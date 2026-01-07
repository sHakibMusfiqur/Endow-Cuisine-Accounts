<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('name')->paginate(20);

        return view('payment_methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new payment method.
     */
    public function create()
    {
        return view('payment_methods.create');
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'status' => 'required|in:active,inactive',
        ]);

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method created successfully.');
    }

    /**
     * Show the form for editing the specified payment method.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('payment_methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $paymentMethod->id,
            'status' => 'required|in:active,inactive',
        ]);

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Check if payment method has transactions
        if ($paymentMethod->transactions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete payment method with existing transactions.');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method deleted successfully.');
    }
}
