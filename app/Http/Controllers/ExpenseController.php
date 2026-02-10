<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['paymentType', 'approver', 'payer', 'expensable']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment type
        if ($request->has('payment_type_id') && $request->payment_type_id != '') {
            $query->where('payment_type_id', $request->payment_type_id);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Filter recurring
        if ($request->has('is_recurring') && $request->is_recurring !== '') {
            $query->where('is_recurring', $request->is_recurring);
        }

        // Search by expense number, vendor name, or reference
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                  ->orWhere('paid_to', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenses = $query->latest('expense_date')->paginate(20);

        $paymentTypes = PaymentType::active()->expense()->ordered()->get();

        $stats = $this->getExpenseStats($request);

        return view('expenses.index', compact('expenses', 'paymentTypes', 'stats'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        $paymentTypes = PaymentType::active()->expense()->ordered()->get();

        return view('expenses.create', compact('paymentTypes'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card,credit,other',
            'reference_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'paid_to' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:monthly,quarterly,yearly',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:pending,paid,cleared',
        ]);

        // Set defaults
        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['is_recurring'] = $validated['is_recurring'] ?? false;
        $validated['status'] = $validated['status'] ?? 'paid';
        $validated['paid_by'] = auth()->id();

        if ($validated['status'] === 'paid') {
            $validated['payment_date'] = $validated['expense_date'];
        }

        $expense = Expense::create($validated);

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        $expense->load(['paymentType', 'approver', 'payer', 'expensable']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        $paymentTypes = PaymentType::active()->expense()->ordered()->get();

        return view('expenses.edit', compact('expense', 'paymentTypes'));
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card,credit,other',
            'reference_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'paid_to' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:monthly,quarterly,yearly',
            'remarks' => 'nullable|string',
        ]);

        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['is_recurring'] = $validated['is_recurring'] ?? false;

        $expense->update($validated);

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(Request $request, Expense $expense)
    {
        if ($expense->isPaid() || $expense->isCleared()) {
            return back()->with('error', 'Expense is already paid.');
        }

        $expense->markAsPaid(auth()->id());

        return back()->with('success', 'Expense marked as paid successfully.');
    }

    /**
     * Mark expense as cleared.
     */
    public function clearExpense(Expense $expense)
    {
        if ($expense->isCleared()) {
            return back()->with('error', 'Expense is already cleared.');
        }

        $expense->markAsCleared();

        return back()->with('success', 'Expense marked as cleared successfully.');
    }

    /**
     * Cancel expense.
     */
    public function cancelExpense(Request $request, Expense $expense)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $expense->cancel($request->reason);

        return back()->with('success', 'Expense cancelled successfully.');
    }

    /**
     * Mark expense as refunded.
     */
    public function refundExpense(Request $request, Expense $expense)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $expense->markAsRefunded($request->remarks);

        return back()->with('success', 'Expense marked as refunded.');
    }

    /**
     * Approve expense.
     */
    public function approveExpense(Expense $expense)
    {
        $expense->update([
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Expense approved successfully.');
    }

    /**
     * Create next recurrence for recurring expense.
     */
    public function createRecurrence(Expense $expense)
    {
        if (!$expense->is_recurring) {
            return back()->with('error', 'This expense is not recurring.');
        }

        $newExpense = $expense->createNextRecurrence();

        return redirect()
            ->route('expenses.show', $newExpense)
            ->with('success', 'Next recurring expense created successfully.');
    }

    /**
     * Show upcoming recurring expenses.
     */
    public function upcomingRecurring(Request $request)
    {
        $days = $request->get('days', 30);

        $expenses = Expense::with(['paymentType', 'payer'])
            ->recurring()
            ->where('next_due_date', '<=', now()->addDays($days))
            ->orderBy('next_due_date')
            ->get();

        return view('expenses.upcoming-recurring', compact('expenses', 'days'));
    }

    /**
     * Get expense statistics.
     */
    private function getExpenseStats(Request $request)
    {
        $query = Expense::query();

        // Apply same filters as index
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('expense_date', '>=', $request->start_date);
        } else {
            // Default to current month
            $query->currentMonth();
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        return [
            'total_expenses' => $query->count(),
            'total_amount' => $query->sum('net_amount'),
            'paid_amount' => (clone $query)->paid()->sum('net_amount'),
            'pending_amount' => (clone $query)->pending()->sum('net_amount'),
            'cleared_amount' => (clone $query)->cleared()->sum('net_amount'),
            'recurring_expenses' => Expense::recurring()->count(),
            'upcoming_due' => Expense::upcomingDue(7)->count(),
            'overdue_count' => Expense::overdue()->count(),
        ];
    }

    /**
     * Generate expenses report.
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_type_id' => 'nullable|exists:payment_types,id',
        ]);

        $expenses = Expense::with(['paymentType', 'payer'])
            ->dateRange($request->start_date, $request->end_date);

        if ($request->has('payment_type_id') && $request->payment_type_id != '') {
            $expenses->where('payment_type_id', $request->payment_type_id);
        }

        $expenses = $expenses->orderBy('expense_date')->get();

        // Group by payment type
        $groupedByType = $expenses->groupBy('payment_type_id')->map(function ($items) {
            return [
                'payment_type' => $items->first()->paymentType->name,
                'count' => $items->count(),
                'total_amount' => $items->sum('net_amount'),
                'paid_amount' => $items->whereIn('status', ['paid', 'cleared'])->sum('net_amount'),
            ];
        });

        // Group by vendor
        $groupedByVendor = $expenses->groupBy('paid_to')->map(function ($items, $vendor) {
            return [
                'vendor' => $vendor,
                'count' => $items->count(),
                'total_amount' => $items->sum('net_amount'),
            ];
        })->sortByDesc('total_amount')->take(10);

        $stats = [
            'total_expenses' => $expenses->count(),
            'total_amount' => $expenses->sum('net_amount'),
            'paid_amount' => $expenses->whereIn('status', ['paid', 'cleared'])->sum('net_amount'),
            'pending_amount' => $expenses->where('status', 'pending')->sum('net_amount'),
            'total_tax' => $expenses->sum('tax_amount'),
            'total_discounts' => $expenses->sum('discount_amount'),
        ];

        return view('expenses.report', compact(
            'expenses',
            'groupedByType',
            'groupedByVendor',
            'stats',
            'request'
        ));
    }

    /**
     * Link expense to entity (Property, Deal, etc.).
     */
    public function linkToEntity(Request $request, Expense $expense)
    {
        $request->validate([
            'expensable_type' => 'required|string',
            'expensable_id' => 'required|integer',
        ]);

        $expense->update([
            'expensable_type' => $request->expensable_type,
            'expensable_id' => $request->expensable_id,
        ]);

        return back()->with('success', 'Expense linked to entity successfully.');
    }
}
