<?php

namespace App\Services;

use App\Models\DailyTransaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TransactionService
{
    /**
     * High expense threshold for notifications
     */
    const HIGH_EXPENSE_THRESHOLD = 5000;

    /**
     * Low balance threshold for notifications
     */
    const LOW_BALANCE_THRESHOLD = 10000;

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
            // Calculate balance
            $lastBalance = $this->getLastBalance($data['date']);
            $newBalance = $lastBalance + $data['income'] - $data['expense'];

            // Create transaction
            $transaction = DailyTransaction::create([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'balance' => $newBalance,
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
                'created_by' => Auth::id(),
            ]);

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($data['date']);

            // Check for notifications
            $this->checkAndCreateNotifications($transaction);

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

            // Update transaction
            $transaction->update([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
            ]);

            // Recalculate balance
            $lastBalance = $this->getLastBalance($data['date'], $transaction->id);
            $newBalance = $lastBalance + $data['income'] - $data['expense'];
            $transaction->balance = $newBalance;
            $transaction->save();

            // Update balances for subsequent transactions
            $earliestDate = $oldDate < $data['date'] ? $oldDate : $data['date'];
            $this->updateSubsequentBalances($earliestDate);

            // Check for notifications
            $this->checkAndCreateNotifications($transaction);

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
            $currentBalance = $currentBalance + $transaction->income - $transaction->expense;
            $transaction->balance = $currentBalance;
            $transaction->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Check conditions and create notifications.
     *
     * @param DailyTransaction $transaction
     * @return void
     */
    private function checkAndCreateNotifications(DailyTransaction $transaction): void
    {
        // Check for high expense
        if ($transaction->expense > self::HIGH_EXPENSE_THRESHOLD) {
            Notification::create([
                'message' => "High expense alert: {$transaction->description} - " . number_format($transaction->expense, 2),
                'type' => 'warning',
                'user_id' => null, // Broadcast to all users
            ]);
        }

        // Check for low balance
        if ($transaction->balance < self::LOW_BALANCE_THRESHOLD) {
            Notification::create([
                'message' => "Low balance alert: Current balance is " . number_format($transaction->balance, 2),
                'type' => 'warning',
                'user_id' => null, // Broadcast to all users
            ]);
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

        $totalIncome = $query->sum('income');
        $totalExpense = $query->sum('expense');
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
