<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PropertyFile;
use App\Models\Client;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['client', 'propertyFile', 'installment', 'receivedBy']);

        // Check if user can view all payments
        if (!Auth::user()->can('payments.view_all')) {
            $query->where('received_by', Auth::id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        $stats = [
            'total_today' => Payment::whereDate('payment_date', today())->sum('amount'),
            'total_this_month' => Payment::whereMonth('payment_date', now()->month)
                                        ->whereYear('payment_date', now()->year)
                                        ->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $propertyFiles = PropertyFile::active()->get();
        $clients = Client::active()->get();

        // If coming from a specific file or installment
        $selectedFile = null;
        $selectedInstallment = null;

        if ($request->filled('file_id')) {
            $selectedFile = PropertyFile::findOrFail($request->file_id);
        }

        if ($request->filled('installment_id')) {
            $selectedInstallment = Installment::findOrFail($request->installment_id);
            $selectedFile = $selectedInstallment->propertyFile;
        }

        return view('payments.create', compact('propertyFiles', 'clients', 'selectedFile', 'selectedInstallment'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_file_id' => 'required|exists:property_files,id',
            'client_id' => 'required|exists:clients,id',
            'installment_id' => 'nullable|exists:installments,id',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:installment,down_payment,token,full_payment,late_fee,transfer_fee',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online,card',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate receipt number
            $validated['receipt_number'] = Payment::generateReceiptNumber();
            $validated['status'] = 'completed';
            $validated['received_by'] = Auth::id();
            $validated['created_by'] = Auth::id();

            $payment = Payment::create($validated);

            // Update property file
            $file = PropertyFile::findOrFail($validated['property_file_id']);
            $file->paid_amount += $validated['amount'];
            $file->updatePaymentStatus();

            // Update installment if specified
            if ($validated['installment_id']) {
                $installment = Installment::findOrFail($validated['installment_id']);
                $installment->markAsPaid(
                    $validated['payment_method'],
                    $validated['reference_number'],
                    Auth::id()
                );
            }

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['client', 'propertyFile', 'installment', 'receivedBy', 'creator']);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'status' => 'required|in:completed,pending,bounced,reversed',
            'remarks' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Generate payment receipt (PDF)
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['client', 'propertyFile', 'installment', 'receivedBy']);

        // Here you would generate a PDF receipt
        // For now, return a view that can be printed
        return view('payments.receipt', compact('payment'));
    }
}
