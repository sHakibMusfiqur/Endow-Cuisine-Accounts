<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     */
    public function index()
    {
        $currencies = Currency::orderBy('is_default', 'desc')
            ->orderBy('code', 'asc')
            ->get();
            
        return view('currencies.index', compact('currencies'));
    }

    /**
     * Show the form for editing a currency.
     */
    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency.
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        try {
            // Prevent changing exchange rate of default currency (KRW)
            if ($currency->is_default && $validated['exchange_rate'] != 1.0) {
                return back()->withErrors([
                    'exchange_rate' => 'Cannot change exchange rate of the default currency. It must always be 1.0.'
                ]);
            }

            $currency->update([
                'exchange_rate' => $validated['exchange_rate'],
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('currencies.index')
                ->with('success', 'Currency updated successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Set a currency as default.
     */
    public function setDefault(Currency $currency)
    {
        DB::beginTransaction();

        try {
            // Remove default status from all currencies
            Currency::where('is_default', true)->update(['is_default' => false]);

            // Set the new default currency
            $currency->update([
                'is_default' => true,
                'exchange_rate' => 1.000000, // Default currency must have rate of 1
            ]);

            DB::commit();

            return redirect()->route('currencies.index')
                ->with('success', "{$currency->name} is now the default currency.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle currency active status.
     */
    public function toggleActive(Currency $currency)
    {
        try {
            // Prevent deactivating default currency
            if ($currency->is_default && $currency->is_active) {
                return back()->withErrors([
                    'error' => 'Cannot deactivate the default currency.'
                ]);
            }

            $currency->update([
                'is_active' => !$currency->is_active
            ]);

            $status = $currency->is_active ? 'activated' : 'deactivated';
            
            return redirect()->route('currencies.index')
                ->with('success', "Currency {$status} successfully.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show currency conversion calculator.
     */
    public function calculator()
    {
        $currencies = Currency::where('is_active', true)->get();
        return view('currencies.calculator', compact('currencies'));
    }

    /**
     * Calculate currency conversion (API endpoint).
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id',
        ]);

        $fromCurrency = Currency::find($validated['from_currency_id']);
        $toCurrency = Currency::find($validated['to_currency_id']);
        
        // Convert to base (KRW) first
        $amountBase = $fromCurrency->convertToBase($validated['amount']);
        
        // Then convert to target currency
        $convertedAmount = $toCurrency->convertFromBase($amountBase);

        return response()->json([
            'amount' => $validated['amount'],
            'from_currency' => $fromCurrency->code,
            'to_currency' => $toCurrency->code,
            'converted_amount' => round($convertedAmount, 2),
            'amount_base' => round($amountBase, 2),
        ]);
    }

    /**
     * Get live exchange rates (placeholder for future API integration).
     */
    public function updateRatesFromApi()
    {
        // This is a placeholder for future implementation
        // where you can integrate with external APIs like:
        // - Open Exchange Rates API
        // - Fixer.io
        // - Currency Layer API
        
        return back()->with('info', 'Live rate updates will be available in a future version.');
    }

    /**
     * Switch the active display currency for the current session.
     */
    public function switchCurrency(Request $request)
    {
        $validated = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
        ]);

        if (setActiveCurrency($validated['currency_id'])) {
            return back()->with('success', 'Display currency changed successfully.');
        }

        return back()->withErrors(['error' => 'Failed to change currency. Please try again.']);
    }
}
