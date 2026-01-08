<?php

namespace App\Services;

use App\Models\DailyTransaction;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction with balance calculation.
     *
     * @param array $data
     * @return DailyTransaction
     * @throws Exception
     */
    public function createTransaction(array $data): DailyTransaction
    {
        // Validate that income and expense are not both greater than 0
        if ($data['income'] > 0 && $data['expense'] > 0) {
            throw new Exception('Income and expense cannot both be greater than 0 in one transaction.');
        }

        // Validate that at least one of income or expense is greater than 0
        if ($data['income'] <= 0 && $data['expense'] <= 0) {
            throw new Exception('Either income or expense must be greater than 0.');
        }

        DB::beginTransaction();

        try {
            // Get currency (default to KRW if not specified)
            $currencyId = $data['currency_id'] ?? Currency::getDefault()?->id;
            $currency = Currency::findOrFail($currencyId);
            
            // Determine original amount (the amount in selected currency)
            $amountOriginal = $data['income'] > 0 ? $data['income'] : $data['expense'];
            
            // Convert to base currency (KRW)
            $amountBase = $currency->convertToBase($amountOriginal);
            
            // Store exchange rate snapshot for historical accuracy
            $exchangeRateSnapshot = $currency->exchange_rate;
            
            // Calculate income and expense in base currency
            $incomeBase = $data['income'] > 0 ? $amountBase : 0;
            $expenseBase = $data['expense'] > 0 ? $amountBase : 0;
            
            // Calculate balance
            $lastBalance = $this->getLastBalance($data['date']);
            $newBalance = $lastBalance + $incomeBase - $expenseBase;

            // Create transaction
            $transaction = DailyTransaction::create([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'balance' => $newBalance,
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $currencyId,
                'amount_original' => $amountOriginal,
                'amount_base' => $amountBase,
                'exchange_rate_snapshot' => $exchangeRateSnapshot,
                'created_by' => Auth::id(),
            ]);

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($data['date']);

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing transaction.
     *
     * @param DailyTransaction $transaction
     * @param array $data
     * @return DailyTransaction
     * @throws Exception
     */
    public function updateTransaction(DailyTransaction $transaction, array $data): DailyTransaction
    {
        // Validate that income and expense are not both greater than 0
        if ($data['income'] > 0 && $data['expense'] > 0) {
            throw new Exception('Income and expense cannot both be greater than 0 in one transaction.');
        }

        // Validate that at least one of income or expense is greater than 0
        if ($data['income'] <= 0 && $data['expense'] <= 0) {
            throw new Exception('Either income or expense must be greater than 0.');
        }

        DB::beginTransaction();

        try {
            $oldDate = $transaction->date;
            
            // Get currency (use existing if not changed)
            $currencyId = $data['currency_id'] ?? $transaction->currency_id;
            $currency = Currency::findOrFail($currencyId);
            
            // Determine original amount
            $amountOriginal = $data['income'] > 0 ? $data['income'] : $data['expense'];
            
            // Convert to base currency
            $amountBase = $currency->convertToBase($amountOriginal);
            
            // Store exchange rate snapshot
            $exchangeRateSnapshot = $currency->exchange_rate;
            
            // Calculate income and expense in base currency
            $incomeBase = $data['income'] > 0 ? $amountBase : 0;
            $expenseBase = $data['expense'] > 0 ? $amountBase : 0;

            // Update transaction
            $transaction->update([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $currencyId,
                'amount_original' => $amountOriginal,
                'amount_base' => $amountBase,
                'exchange_rate_snapshot' => $exchangeRateSnapshot,
            ]);

            // Recalculate balance
            $lastBalance = $this->getLastBalance($data['date'], $transaction->id);
            $newBalance = $lastBalance + $incomeBase - $expenseBase;
            $transaction->balance = $newBalance;
            $transaction->save();

            // Update balances for subsequent transactions
            $earliestDate = $oldDate < $data['date'] ? $oldDate : $data['date'];
            $this->updateSubsequentBalances($earliestDate);

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a transaction.
     *
     * @param DailyTransaction $transaction
     * @return bool
     * @throws Exception
     */
    public function deleteTransaction(DailyTransaction $transaction): bool
    {
        DB::beginTransaction();

        try {
            $date = $transaction->date;
            $transaction->delete();

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($date);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get the last balance before a given date.
     *
     * @param string $date
     * @param int|null $excludeId
     * @return float
     */
    private function getLastBalance($date, $excludeId = null): float
    {
        $query = DailyTransaction::where('date', '<', $date)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $lastTransaction = $query->first();

        return $lastTransaction ? $lastTransaction->balance : 0;
    }

    /**
     * Update balances for all transactions after a given date.
     *
     * @param string $date
     * @return void
     */
    private function updateSubsequentBalances($date): void
    {
        $transactions = DailyTransaction::where('date', '>=', $date)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentBalance = $this->getLastBalance($date);

        foreach ($transactions as $transaction) {
            // Use amount_base for balance calculation (always in KRW)
            $incomeBase = $transaction->income > 0 ? $transaction->amount_base : 0;
            $expenseBase = $transaction->expense > 0 ? $transaction->amount_base : 0;
            
            $currentBalance = $currentBalance + $incomeBase - $expenseBase;
            $transaction->balance = $currentBalance;
            $transaction->saveQuietly(); // Save without triggering events
        }
    }



    /**
     * Get summary statistics.
     *
     * @param string $period 'today', 'week', 'month', 'year'
     * @return array
     */
    public function getSummary($period = 'today'): array
    {
        $query = DailyTransaction::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->thisWeek();
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        // Use amount_base for all calculations (always in KRW)
        $totalIncome = DailyTransaction::where('income', '>', 0)
            ->when($period !== 'all', function($q) use ($query) {
                return $q->whereIn('id', $query->pluck('id'));
            })
            ->sum('amount_base');
            
        $totalExpense = DailyTransaction::where('expense', '>', 0)
            ->when($period !== 'all', function($q) use ($query) {
                return $q->whereIn('id', $query->pluck('id'));
            })
            ->sum('amount_base');
            
        $netAmount = $totalIncome - $totalExpense;
        
        $currentBalance = DailyTransaction::orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first()
            ?->balance ?? 0;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
            'current_balance' => $currentBalance,
        ];
    }
}
