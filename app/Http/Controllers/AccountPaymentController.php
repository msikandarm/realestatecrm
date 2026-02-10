<?php

namespace App\Http\Controllers;

use App\Models\AccountPayment;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountPaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = AccountPayment::with(['paymentType', 'receiver', 'payable']);

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
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Search by payment number or received from
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('received_from', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('payment_date')->paginate(20);

        $paymentTypes = PaymentType::active()->income()->ordered()->get();

        $stats = $this->getPaymentStats($request);

        return view('payments.index', compact('payments', 'paymentTypes', 'stats'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $paymentTypes = PaymentType::active()->income()->ordered()->get();

        return view('payments.create', compact('paymentTypes'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card,other',
            'reference_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:pending,received,cleared',
        ]);

        $validated['received_by'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'received';

        $payment = AccountPayment::create($validated);

        return redirect()
            ->route('account-payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(AccountPayment $accountPayment)
    {
        $accountPayment->load(['paymentType', 'receiver', 'payable']);

        return view('payments.show', compact('accountPayment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(AccountPayment $accountPayment)
    {
        $paymentTypes = PaymentType::active()->income()->ordered()->get();

        return view('payments.edit', compact('accountPayment', 'paymentTypes'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, AccountPayment $accountPayment)
    {
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card,other',
            'reference_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $accountPayment->update($validated);

        return redirect()
            ->route('account-payments.show', $accountPayment)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(AccountPayment $accountPayment)
    {
        $accountPayment->delete();

        return redirect()
            ->route('account-payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Mark payment as cleared.
     */
    public function clearPayment(AccountPayment $accountPayment)
    {
        if ($accountPayment->isCleared()) {
            return back()->with('error', 'Payment is already cleared.');
        }

        $accountPayment->markAsCleared();

        return back()->with('success', 'Payment marked as cleared successfully.');
    }

    /**
     * Mark payment as bounced.
     */
    public function bouncePayment(Request $request, AccountPayment $accountPayment)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $accountPayment->markAsBounced($request->remarks);

        return back()->with('success', 'Payment marked as bounced.');
    }

    /**
     * Cancel payment.
     */
    public function cancelPayment(Request $request, AccountPayment $accountPayment)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $accountPayment->cancel($request->reason);

        return back()->with('success', 'Payment cancelled successfully.');
    }

    /**
     * Generate payment receipt.
     */
    public function receipt(AccountPayment $accountPayment)
    {
        $accountPayment->load(['paymentType', 'receiver', 'payable']);

        return view('payments.receipt', compact('accountPayment'));
    }

    /**
     * Get payment statistics.
     */
    private function getPaymentStats(Request $request)
    {
        $query = AccountPayment::query();

        // Apply same filters as index
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('payment_date', '>=', $request->start_date);
        } else {
            // Default to current month
            $query->currentMonth();
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        return [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'cleared_amount' => (clone $query)->cleared()->sum('amount'),
            'pending_amount' => (clone $query)->pending()->sum('amount'),
            'bounced_amount' => (clone $query)->bounced()->sum('amount'),
            'cash_payments' => (clone $query)->byMethod('cash')->sum('amount'),
            'cheque_payments' => (clone $query)->byMethod('cheque')->sum('amount'),
            'bank_transfer_payments' => (clone $query)->byMethod('bank_transfer')->sum('amount'),
        ];
    }

    /**
     * Generate payments report.
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_type_id' => 'nullable|exists:payment_types,id',
        ]);

        $payments = AccountPayment::with(['paymentType', 'receiver'])
            ->dateRange($request->start_date, $request->end_date);

        if ($request->has('payment_type_id') && $request->payment_type_id != '') {
            $payments->where('payment_type_id', $request->payment_type_id);
        }

        $payments = $payments->orderBy('payment_date')->get();

        // Group by payment type
        $groupedByType = $payments->groupBy('payment_type_id')->map(function ($items) {
            return [
                'payment_type' => $items->first()->paymentType->name,
                'count' => $items->count(),
                'total_amount' => $items->sum('amount'),
                'cleared_amount' => $items->where('status', 'cleared')->sum('amount'),
            ];
        });

        // Group by payment method
        $groupedByMethod = $payments->groupBy('payment_method')->map(function ($items, $method) {
            return [
                'payment_method' => ucfirst(str_replace('_', ' ', $method)),
                'count' => $items->count(),
                'total_amount' => $items->sum('amount'),
            ];
        });

        $stats = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'cleared_amount' => $payments->where('status', 'cleared')->sum('amount'),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
            'bounced_amount' => $payments->where('status', 'bounced')->sum('amount'),
        ];

        return view('payments.report', compact(
            'payments',
            'groupedByType',
            'groupedByMethod',
            'stats',
            'request'
        ));
    }

    /**
     * Link payment to entity (Client, Deal, PropertyFile, etc.).
     */
    public function linkToEntity(Request $request, AccountPayment $accountPayment)
    {
        $request->validate([
            'payable_type' => 'required|string',
            'payable_id' => 'required|integer',
        ]);

        $accountPayment->update([
            'payable_type' => $request->payable_type,
            'payable_id' => $request->payable_id,
        ]);

        return back()->with('success', 'Payment linked to entity successfully.');
    }
}
