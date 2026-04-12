<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VendorPayoutController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::query()->orderBy('name')->get(['id', 'name', 'commission_rate']);

        $filters = [
            'vendor_id' => $request->input('vendor_id'),
            'status' => $request->input('status'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $payoutsQuery = VendorPayout::query()->with('vendor')->latest();

        if (!empty($filters['vendor_id'])) {
            $payoutsQuery->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['status'])) {
            $payoutsQuery->where('status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $payoutsQuery->whereDate('period_start', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $payoutsQuery->whereDate('period_end', '<=', $filters['to_date']);
        }

        $payouts = $payoutsQuery->paginate(20)->withQueryString();

        return view('admin.vendor-payouts.index', [
            'vendors' => $vendors,
            'payouts' => $payouts,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            // Optional: when you want to “pre-create” a payout and mark it paid later
            'status' => ['nullable', 'in:pending,paid'],
            'override_if_exists' => ['nullable', 'boolean'],
        ]);

        $vendor = Vendor::query()->findOrFail($validated['vendor_id']);

        $periodStart = $validated['from_date'];
        $periodEnd = $validated['to_date'];
        $status = $validated['status'] ?? 'pending';
        $overrideIfExists = (bool) ($validated['override_if_exists'] ?? false);

        // This guards against running payouts before necessary migrations.
        if (!Schema::hasColumn('products', 'vendor_id')) {
            return back()->withErrors(['payout' => 'Missing `products.vendor_id`. Run migrations.'])->withInput();
        }

        $grossSales = $this->computeGrossSalesForVendor($vendor->id, $periodStart, $periodEnd);

        $commissionAmount = round($grossSales * ($vendor->commission_rate / 100), 2);
        $netAmount = max($grossSales - $commissionAmount, 0);

        $existing = VendorPayout::query()
            ->where('vendor_id', $vendor->id)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if ($existing) {
            if ($existing->status === 'paid' && !$overrideIfExists) {
                return redirect()
                    ->route('admin.vendor-payouts.index')
                    ->with('success', 'A paid payout for this period already exists.');
            }

            $existing->update([
                'gross_sales' => $grossSales,
                'commission_amount' => $commissionAmount,
                'net_amount' => $netAmount,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
                'reference' => $status === 'paid' ? ($existing->reference ?? null) : null,
            ]);

            return redirect()
                ->route('admin.vendor-payouts.index')
                ->with('success', 'Vendor payout updated.');
        }

        VendorPayout::query()->create([
            'vendor_id' => $vendor->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'gross_sales' => $grossSales,
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'status' => $status,
        ]);

        return redirect()
            ->route('admin.vendor-payouts.index', ['vendor_id' => $vendor->id])
            ->with('success', 'Vendor payout created.');
    }

    public function show(VendorPayout $vendorPayout)
    {
        $vendorPayout->load('vendor');
        return view('admin.vendor-payouts.show', [
            'payout' => $vendorPayout,
        ]);
    }

    public function markPaid(Request $request, VendorPayout $payout)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($payout->status === 'paid') {
            return redirect()
                ->route('admin.vendor-payouts.show', $payout)
                ->with('success', 'This payout is already marked as paid.');
        }

        $payout->update([
            'status' => 'paid',
            'reference' => $validated['reference'],
            'paid_at' => !empty($validated['paid_at']) ? $validated['paid_at'] : now(),
            'notes' => $validated['notes'] ?? $payout->notes,
        ]);

        return redirect()
            ->route('admin.vendor-payouts.show', $payout)
            ->with('success', 'Payout marked as paid.');
    }

    public function exportCsv(Request $request)
    {
        $filters = [
            'vendor_id' => $request->input('vendor_id'),
            'status' => $request->input('status'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $query = VendorPayout::query()->with('vendor');

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('period_start', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('period_end', '<=', $filters['to_date']);
        }

        $rows = $query->orderByDesc('period_end')->get();

        $filename = 'vendor-payouts-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Vendor',
                'Period Start',
                'Period End',
                'Gross Sales',
                'Commission Amount',
                'Net Amount',
                'Status',
                'Reference',
                'Paid At',
                'Notes',
            ]);
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->vendor?->name ?? '',
                    $row->period_start?->format('Y-m-d') ?? '',
                    $row->period_end?->format('Y-m-d') ?? '',
                    number_format((float) $row->gross_sales, 2, '.', ''),
                    number_format((float) $row->commission_amount, 2, '.', ''),
                    number_format((float) $row->net_amount, 2, '.', ''),
                    $row->status,
                    $row->reference ?? '',
                    $row->paid_at?->format('Y-m-d H:i:s') ?? '',
                    $row->notes ?? '',
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Gross sales = sum of order_items.line_total for all products belonging to the vendor
     * within the given period.
     */
    private function computeGrossSalesForVendor(int $vendorId, string $periodStart, string $periodEnd): float
    {
        // We attempt to use payment_status if it exists; otherwise we fall back to excluding cancelled orders.
        $ordersPaymentStatusColumnExists = Schema::hasColumn('orders', 'payment_status');

        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('products.vendor_id', $vendorId)
            ->whereDate('orders.created_at', '>=', $periodStart)
            ->whereDate('orders.created_at', '<=', $periodEnd)
            ->selectRaw('COALESCE(SUM(order_items.line_total), 0) as gross_sales');

        $query->where('orders.status', '!=', 'cancelled');

        if ($ordersPaymentStatusColumnExists) {
            // Only payout paid orders.
            $query->where('orders.payment_status', '=', 'paid');
        }

        return (float) $query->value('gross_sales');
    }
}

