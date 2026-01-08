<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Currency;
use App\Models\DailyTransaction;
use App\Models\PaymentMethod;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Exception;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = DailyTransaction::with(['category', 'paymentMethod', 'currency', 'creator']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('type')) {
            if ($request->type === 'income') {
                $query->income();
            } elseif ($request->type === 'expense') {
                $query->expense();
            }
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Order by date and id
        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // Get filter options
        $categories = Category::all();
        $paymentMethods = PaymentMethod::active()->get();

        return view('transactions.index', compact('transactions', 'categories', 'paymentMethods'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $incomeCategories = Category::income()->get();
        $expenseCategories = Category::expense()->get();
        $paymentMethods = PaymentMethod::active()->get();
        $currencies = Currency::getActive();
        $defaultCurrency = Currency::getDefault();

        return view('transactions.create', compact(
            'incomeCategories', 
            'expenseCategories', 
            'paymentMethods',
            'currencies',
            'defaultCurrency'
        ));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:5000',
            'income' => 'nullable|numeric|min:0',
            'expense' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        // Set defaults
        $validated['income'] = $validated['income'] ?? 0;
        $validated['expense'] = $validated['expense'] ?? 0;
        
        // Sanitize HTML description to prevent XSS
        $validated['description'] = $this->sanitizeHtml($validated['description']);

        try {
            $this->transactionService->createTransaction($validated);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(DailyTransaction $transaction)
    {
        $incomeCategories = Category::income()->get();
        $expenseCategories = Category::expense()->get();
        $paymentMethods = PaymentMethod::active()->get();
        $currencies = Currency::getActive();

        return view('transactions.edit', compact(
            'transaction', 
            'incomeCategories', 
            'expenseCategories', 
            'paymentMethods',
            'currencies'
        ));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, DailyTransaction $transaction)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:5000',
            'income' => 'nullable|numeric|min:0',
            'expense' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        // Set defaults
        $validated['income'] = $validated['income'] ?? 0;
        $validated['expense'] = $validated['expense'] ?? 0;
        
        // Sanitize HTML description to prevent XSS
        $validated['description'] = $this->sanitizeHtml($validated['description']);

        try {
            $this->transactionService->updateTransaction($transaction, $validated);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(DailyTransaction $transaction)
    {
        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Sanitize HTML content to prevent XSS attacks.
     * Allows only safe HTML tags generated by Quill editor.
     */
    private function sanitizeHtml($html)
    {
        // Allow only safe tags from Quill editor
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><a>';
        
        // Strip dangerous tags and attributes
        $cleaned = strip_tags($html, $allowedTags);
        
        // Remove any javascript: or data: protocols from links
        $cleaned = preg_replace('/(<a[^>]+href=[\"\'])(javascript:|data:)/i', '$1#', $cleaned);
        
        // Remove on* event attributes
        $cleaned = preg_replace('/<([^>]+)\s+on\w+=[\""][^\"]*[\""]([^>]*)>/i', '<$1$2>', $cleaned);
        
        return $cleaned;
    }}