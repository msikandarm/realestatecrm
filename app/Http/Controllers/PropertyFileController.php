<?php

namespace App\Http\Controllers;

use App\Models\PropertyFile;
use App\Models\FilePayment;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertyFileController extends Controller
{
    /**
     * Display a listing of the property files.
     */
    public function index(Request $request)
    {
        $query = PropertyFile::with(['client', 'fileable', 'deal']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('file_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => \App\Models\PropertyFile::count(),
            'active' => \App\Models\PropertyFile::where('status', 'active')->count(),
            'pending' => \App\Models\PropertyFile::where('status', 'pending')->count(),
            'completed' => \App\Models\PropertyFile::where('status', 'completed')->count(),
        ];

        return view('files.index', compact('files', 'stats'));
    }

    /**
     * Show the form for creating a new property file.
     */
    public function create()
    {
        $clients = Client::active()->get();
        $deals = Deal::confirmed()->get();
        return view('files.create', compact('clients', 'deals'));
    }

    /**
     * Store a newly created property file in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'deal_id' => 'nullable|exists:deals,id',
            'fileable_type' => 'required|in:App\Models\Plot,App\Models\Property',
            'fileable_id' => 'required|integer',
            'total_amount' => 'required|numeric|min:0',
            'payment_plan' => 'required|in:cash,installment',
            'total_installments' => 'required_if:payment_plan,installment|nullable|integer|min:1',
            'installment_amount' => 'required_if:payment_plan,installment|nullable|numeric|min:0',
            'installment_frequency' => 'required_if:payment_plan,installment|nullable|in:monthly,quarterly,yearly',
            'first_installment_date' => 'required_if:payment_plan,installment|nullable|date',
            'issue_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate file number
            $validated['file_number'] = PropertyFile::generateFileNumber();
            $validated['remaining_amount'] = $validated['total_amount'];
            $validated['paid_amount'] = 0;
            $validated['status'] = 'active';
            $validated['created_by'] = Auth::id();

            // Calculate last installment date
            if ($validated['payment_plan'] === 'installment') {
                $firstDate = Carbon::parse($validated['first_installment_date']);

                switch ($validated['installment_frequency']) {
                    case 'monthly':
                        $validated['last_installment_date'] = $firstDate->copy()
                            ->addMonths($validated['total_installments'] - 1);
                        break;
                    case 'quarterly':
                        $validated['last_installment_date'] = $firstDate->copy()
                            ->addMonths(($validated['total_installments'] - 1) * 3);
                        break;
                    case 'yearly':
                        $validated['last_installment_date'] = $firstDate->copy()
                            ->addYears($validated['total_installments'] - 1);
                        break;
                }
            }

            $file = PropertyFile::create($validated);

            // Create installments if payment plan is installment
            if ($validated['payment_plan'] === 'installment') {
                $this->createInstallments($file);
            }

            DB::commit();

            return redirect()->route('files.show', $file)
                ->with('success', 'Property file created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create file: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified property file.
     */
    public function show(PropertyFile $file)
    {
        $file->load(['client', 'fileable', 'deal', 'filePayments.receiver']);

        $stats = [
            'total_payments' => $file->filePayments()->count(),
            'received_payments' => $file->filePayments()->received()->count(),
            'cleared_payments' => $file->filePayments()->cleared()->count(),
            'pending_payments' => $file->filePayments()->pending()->count(),
            'overdue_payments' => $file->filePayments()->overdue()->count(),
            'payment_progress' => $file->getPaymentProgress(),
            'remaining_balance' => $file->getRemainingBalance(),
            'paid_installments' => $file->getPaidInstallmentsCount(),
            'pending_installments' => $file->getPendingInstallmentsCount(),
        ];

        $installmentSchedule = $file->getInstallmentSchedule();

        return view('files.show', compact('file', 'stats', 'installmentSchedule'));
    }

    /**
     * Show the form for editing the specified property file.
     */
    public function edit(PropertyFile $file)
    {
        $clients = Client::active()->get();
        return view('files.edit', compact('file', 'clients'));
    }

    /**
     * Update the specified property file in storage.
     */
    public function update(Request $request, PropertyFile $file)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,completed,transferred,cancelled,defaulted',
            'remarks' => 'nullable|string',
        ]);

        $file->update($validated);

        return redirect()->route('files.show', $file)
            ->with('success', 'Property file updated successfully.');
    }

    /**
     * Transfer file to another client
     */
    public function transfer(Request $request, PropertyFile $file)
    {
        if (!Auth::user()->can('files.transfer')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'new_client_id' => 'required|exists:clients,id|different:client_id',
            'transfer_charges' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $newClient = Client::find($validated['new_client_id']);

            $file->transferTo(
                $newClient,
                $validated['transfer_charges'] ?? 0,
                $validated['remarks'] ?? null
            );

            // Create transfer charges payment if applicable
            if (!empty($validated['transfer_charges'])) {
                FilePayment::create([
                    'property_file_id' => $file->id,
                    'amount' => $validated['transfer_charges'],
                    'payment_date' => now(),
                    'payment_type' => FilePayment::TYPE_TRANSFER_CHARGES,
                    'payment_method' => 'cash',
                    'status' => FilePayment::STATUS_RECEIVED,
                    'remarks' => 'Transfer charges for file transfer',
                    'received_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('files.show', $file)
                ->with('success', 'File transferred successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to transfer file: ' . $e->getMessage());
        }
    }

    /**
     * Add payment to property file
     */
    public function addPayment(Request $request, PropertyFile $file)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:down_payment,installment,partial,full_payment,penalty,adjustment',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'installment_number' => 'nullable|integer',
            'penalty_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['property_file_id'] = $file->id;
            $validated['status'] = FilePayment::STATUS_RECEIVED;
            $validated['received_by'] = Auth::id();

            $payment = FilePayment::create($validated);

            // Auto-clear if cash
            if ($validated['payment_method'] === FilePayment::METHOD_CASH) {
                $payment->markAsCleared();
            }

            DB::commit();

            return redirect()->route('files.show', $file)
                ->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add payment: ' . $e->getMessage());
        }
    }

    /**
     * Clear a payment (for cheques/bank transfers)
     */
    public function clearPayment(FilePayment $payment)
    {
        if ($payment->isCleared()) {
            return back()->with('info', 'Payment is already cleared.');
        }

        if ($payment->markAsCleared()) {
            return back()->with('success', 'Payment marked as cleared.');
        }

        return back()->with('error', 'Failed to clear payment.');
    }

    /**
     * Mark payment as bounced
     */
    public function bouncePayment(Request $request, FilePayment $payment)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        if ($payment->markAsBounced($validated['reason'])) {
            return back()->with('success', 'Payment marked as bounced.');
        }

        return back()->with('error', 'Failed to mark payment as bounced.');
    }

    /**
     * Get payment receipt
     */
    public function paymentReceipt(FilePayment $payment)
    {
        $payment->load(['propertyFile.client', 'propertyFile.fileable', 'receiver']);

        return view('files.payment-receipt', compact('payment'));
    }

    /**
     * Get file statement
     */
    public function statement(PropertyFile $file)
    {
        $file->load(['client', 'fileable', 'filePayments' => function($query) {
            $query->orderBy('payment_date', 'asc');
        }]);

        $stats = [
            'total_amount' => $file->total_amount,
            'paid_amount' => $file->paid_amount,
            'remaining_balance' => $file->getRemainingBalance(),
            'payment_progress' => $file->getPaymentProgress(),
            'total_payments' => $file->filePayments->count(),
        ];

        return view('files.statement', compact('file', 'stats'));
    }

    /**
     * Sync file paid amount from payments
     */
    public function syncPaidAmount(PropertyFile $file)
    {
        $file->syncPaidAmount();

        return back()->with('success', 'Paid amount synchronized successfully.');
    }

    /**
     * Mark file as defaulted
     */
    public function markAsDefaulted(Request $request, PropertyFile $file)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        if ($file->markAsDefaulted($validated['reason'])) {
            return back()->with('success', 'File marked as defaulted.');
        }

        return back()->with('error', 'Failed to mark file as defaulted.');
    }

    /**
     * Cancel file
     */
    public function cancelFile(Request $request, PropertyFile $file)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        if ($file->cancel($validated['reason'])) {
            return back()->with('success', 'File cancelled successfully.');
        }

        return back()->with('error', 'Failed to cancel file.');
    }

    /**
     * Create installments for a property file
     */
    private function createInstallments(PropertyFile $file)
    {
        $installmentDate = Carbon::parse($file->first_installment_date);

        for ($i = 1; $i <= $file->total_installments; $i++) {
            Installment::create([
                'property_file_id' => $file->id,
                'installment_number' => $i,
                'amount' => $file->installment_amount,
                'due_date' => $installmentDate->copy(),
                'status' => 'pending',
            ]);

            // Increment date based on frequency
            switch ($file->installment_frequency) {
                case 'monthly':
                    $installmentDate->addMonth();
                    break;
                case 'quarterly':
                    $installmentDate->addMonths(3);
                    break;
                case 'yearly':
                    $installmentDate->addYear();
                    break;
            }
        }
    }
}
