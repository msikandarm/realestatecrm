<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Statement - File #{{ $file->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #333; line-height: 1.6; background: #fff; }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #667eea; }
        .logo { font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .statement-title { font-size: 20px; color: #666; margin-top: 10px; }
        .info-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-box { padding: 20px; background: #f9fafb; border-radius: 8px; }
        .info-box h3 { font-size: 14px; color: #667eea; text-transform: uppercase; margin-bottom: 15px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: 600; color: #6b7280; }
        .info-value { color: #111827; }
        .summary-section { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 40px 0; }
        .summary-card { padding: 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border-radius: 8px; }
        .summary-card .value { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .summary-card .label { font-size: 12px; opacity: 0.9; }
        .table-section { margin: 40px 0; }
        .table-section h3 { font-size: 18px; margin-bottom: 20px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead { background: #667eea; color: #fff; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { font-weight: 600; font-size: 14px; }
        td { font-size: 13px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .totals-row { background: #f9fafb; font-weight: bold; }
        .footer { text-align: center; margin-top: 60px; padding-top: 20px; border-top: 2px solid #e5e7eb; color: #6b7280; font-size: 12px; }
        .notes-section { margin: 30px 0; padding: 20px; background: #fffbeb; border-left: 4px solid #fbbf24; border-radius: 4px; }
        .notes-section h4 { color: #92400e; margin-bottom: 10px; font-size: 14px; }
        .notes-section p { color: #78350f; font-size: 13px; }
        @media print {
            body { background: #fff; }
            .container { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Real Estate CRM</div>
            <div class="statement-title">Payment Statement</div>
            <p style="margin-top: 10px; color: #6b7280; font-size: 13px;">Generated on {{ date('F d, Y') }}</p>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>File Information</h3>
                <div class="info-row">
                    <span class="info-label">File Number:</span>
                    <span class="info-value">#{{ $file->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Property:</span>
                    <span class="info-value">
                        @if($file->property){{ $file->property->title }}
                        @elseif($file->plot)Plot #{{ $file->plot->plot_number }}@endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ ucfirst($file->status) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">File Date:</span>
                    <span class="info-value">{{ $file->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>Client Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $file->client->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $file->client->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $file->client->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $file->client->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-card">
                <div class="value">PKR {{ number_format($file->total_amount) }}</div>
                <div class="label">Total Amount</div>
            </div>
            <div class="summary-card">
                <div class="value">PKR {{ number_format($file->down_payment) }}</div>
                <div class="label">Down Payment</div>
            </div>
            <div class="summary-card">
                <div class="value">PKR {{ number_format($file->paid_amount ?? 0) }}</div>
                <div class="label">Amount Paid</div>
            </div>
            <div class="summary-card">
                <div class="value">PKR {{ number_format($file->total_amount - ($file->paid_amount ?? 0)) }}</div>
                <div class="label">Balance Due</div>
            </div>
        </div>

        <div class="table-section">
            <h3>Installment Schedule</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPaid = 0; @endphp
                    @if($file->installments && $file->installments->isNotEmpty())
                        @foreach($file->installments as $index => $installment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ date('M d, Y', strtotime($installment->due_date)) }}</td>
                            <td>PKR {{ number_format($installment->amount) }}</td>
                            <td><span class="status-badge status-{{ $installment->status }}">{{ ucfirst($installment->status) }}</span></td>
                            <td>{{ $installment->paid_date ? date('M d, Y', strtotime($installment->paid_date)) : '-' }}</td>
                        </tr>
                        @if($installment->status == 'paid')
                            @php $totalPaid += $installment->amount; @endphp
                        @endif
                        @endforeach
                        <tr class="totals-row">
                            <td colspan="2">TOTAL INSTALLMENTS</td>
                            <td>PKR {{ number_format($file->total_installments * $file->installment_amount) }}</td>
                            <td colspan="2">Paid: PKR {{ number_format($totalPaid) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6b7280;">No installments scheduled</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($file->notes)
        <div class="notes-section">
            <h4>Notes</h4>
            <p>{{ $file->notes }}</p>
        </div>
        @endif

        <div class="footer">
            <p><strong>Real Estate CRM</strong></p>
            <p>This is a computer-generated statement and requires no signature</p>
            <p style="margin-top: 10px;">For queries, contact: info@realestatecrm.com</p>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 40px;">
            <button onclick="window.print()" style="padding: 12px 30px; background: #667eea; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                <i class="fas fa-print"></i> Print Statement
            </button>
            <button onclick="window.close()" style="padding: 12px 30px; background: #6b7280; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; margin-left: 10px;">
                Close
            </button>
        </div>
    </div>
</body>
</html>
