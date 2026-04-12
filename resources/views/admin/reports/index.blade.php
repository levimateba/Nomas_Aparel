@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
    <style>
        .reports-page {
            display: grid;
            gap: 18px;
        }
        .reports-head {
            border: 1px solid #dbe3ec;
            border-radius: 18px;
            padding: 22px 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fc 62%, #eef2f7 100%);
            box-shadow: 0 16px 30px rgba(13, 29, 47, 0.08);
        }
        .reports-head h2 {
            margin: 0;
            font-size: 1.85rem;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .reports-head p {
            margin: 10px 0 0;
            color: #556273;
            font-size: 0.95rem;
            line-height: 1.45;
        }
        .reports-filters {
            border: 1px solid #d7dde4;
            border-radius: 16px;
            padding: 14px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(17, 24, 39, 0.06);
            display: grid;
            gap: 12px;
        }
        .reports-filter-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .reports-field {
            display: grid;
            gap: 6px;
        }
        .reports-field label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .reports-input {
            width: 100%;
            border: 1px solid #d2dae5;
            border-radius: 12px;
            background: #f8fafc;
            color: #162233;
            font: inherit;
            font-size: 0.94rem;
            padding: 10px 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .reports-input:focus {
            outline: none;
            border-color: #b8c2cd;
            box-shadow: 0 0 0 4px rgba(61, 92, 126, 0.12);
            background: #fff;
        }
        .reports-filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .reports-btn {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .reports-btn-primary {
            color: #1a1300;
            background: linear-gradient(135deg, #e2c15a 0%, #d4af37 100%);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.28);
        }
        .reports-btn-secondary {
            color: #233142;
            background: #fff;
            border-color: #c9d3df;
        }
        .reports-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }
        .reports-kpi {
            border: 1px solid #d7dde4;
            border-radius: 14px;
            padding: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 18px rgba(17, 24, 39, 0.05);
        }
        .reports-kpi-label {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
        }
        .reports-kpi-value {
            margin: 8px 0 0;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .reports-section {
            border: 1px solid #d7dde4;
            border-radius: 16px;
            padding: 16px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(17, 24, 39, 0.06);
        }
        .reports-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .reports-section h3 {
            margin: 0;
            font-size: 1.05rem;
            color: #1f2937;
        }
        .reports-table-wrap {
            overflow-x: auto;
        }
        .reports-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 620px;
        }
        .reports-table th {
            text-align: left;
            padding: 10px;
            font-size: 0.77rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .reports-table td {
            padding: 10px;
            border-bottom: 1px solid #edf2f7;
            color: #1f2937;
            font-size: 0.92rem;
            white-space: nowrap;
        }
        .reports-empty {
            color: #64748b;
            font-style: italic;
        }
        .reports-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .reports-badge-success {
            color: #14532d;
            background: rgba(34, 197, 94, 0.14);
            border-color: rgba(34, 197, 94, 0.28);
        }
        .reports-badge-muted {
            color: #4b5563;
            background: rgba(107, 114, 128, 0.14);
            border-color: rgba(107, 114, 128, 0.28);
        }
        .reports-payout-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .reports-mini {
            border: 1px solid #d7dde4;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            padding: 14px;
        }
        .reports-mini strong {
            color: #1e293b;
            font-size: 0.95rem;
        }
        .reports-mini-value {
            margin-top: 6px;
            font-size: 1.05rem;
            font-weight: 800;
            color: #111827;
        }
        .reports-mini small {
            color: #64748b;
        }
        @media (max-width: 860px) {
            .reports-filter-grid {
                grid-template-columns: 1fr;
            }
            .reports-table {
                min-width: 540px;
            }
            .reports-head h2 {
                font-size: 1.55rem;
            }
        }
    </style>

    <div class="reports-page">
        <section class="reports-head">
            <h2>Sales Reports</h2>
            <p>Track order trends, product performance, coupon effectiveness, and vendor payout exposure in one place.</p>
        </section>

        <section class="reports-filters">
            <form method="GET" action="{{ route('admin.reports.index') }}">
                <div class="reports-filter-grid">
                    <div class="reports-field">
                        <label for="report-from-date">From</label>
                        <input id="report-from-date" class="reports-input" type="date" name="from_date" value="{{ $fromDate }}">
                    </div>
                    <div class="reports-field">
                        <label for="report-to-date">To</label>
                        <input id="report-to-date" class="reports-input" type="date" name="to_date" value="{{ $toDate }}">
                    </div>
                </div>
                <div class="reports-filter-actions" style="margin-top:10px;">
                    <button class="reports-btn reports-btn-primary" type="submit">Apply Filters</button>
                    @if($fromDate || $toDate)
                        <a class="reports-btn reports-btn-secondary" href="{{ route('admin.reports.index') }}">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="reports-kpi-grid">
            <article class="reports-kpi">
                <p class="reports-kpi-label">Total Orders</p>
                <p class="reports-kpi-value">{{ $totalOrders }}</p>
            </article>
            <article class="reports-kpi">
                <p class="reports-kpi-label">Total Revenue</p>
                <p class="reports-kpi-value">KES {{ number_format($totalRevenue, 2) }}</p>
            </article>
            <article class="reports-kpi">
                <p class="reports-kpi-label">Average Order Value</p>
                <p class="reports-kpi-value">KES {{ number_format($averageOrderValue, 2) }}</p>
            </article>
            <article class="reports-kpi">
                <p class="reports-kpi-label">Pending Reviews</p>
                <p class="reports-kpi-value">{{ $reviewPendingCount }}</p>
            </article>
            <article class="reports-kpi">
                <p class="reports-kpi-label">Active Coupons</p>
                <p class="reports-kpi-value">{{ $couponActiveCount }}</p>
            </article>
            <article class="reports-kpi">
                <p class="reports-kpi-label">Coupon Uses</p>
                <p class="reports-kpi-value">{{ $couponUsedTotal }}</p>
            </article>
        </section>

        <section class="reports-section">
            <div class="reports-section-head">
                <h3>Status Breakdown</h3>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Status</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($statusBreakdown as $status => $count)
                            <tr>
                                <td>
                                    @php
                                        $normalizedStatus = strtolower((string) $status);
                                        $isPositive = in_array($normalizedStatus, ['paid', 'completed', 'delivered', 'processing'], true);
                                    @endphp
                                    <span class="reports-badge {{ $isPositive ? 'reports-badge-success' : 'reports-badge-muted' }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </td>
                                <td>{{ $count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="reports-empty">No orders in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="reports-section">
            <div class="reports-section-head">
                <h3>Top Selling Products</h3>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Product</th><th>Units Sold</th><th>Sales</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ (int) $item->total_qty }}</td>
                                <td>KES {{ number_format((float) $item->total_sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="reports-empty">No sales data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="reports-section">
            <div class="reports-section-head">
                <h3>Top Coupon Performance</h3>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($couponPerformance as $coupon)
                            <tr>
                                <td><strong>{{ $coupon->code }}</strong></td>
                                <td>{{ ucfirst($coupon->type) }}</td>
                                <td>{{ $coupon->type === 'percent' ? $coupon->value.'%' : 'KES '.number_format((float) $coupon->value, 2) }}</td>
                                <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                                <td>
                                    <span class="reports-badge {{ $coupon->is_active ? 'reports-badge-success' : 'reports-badge-muted' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="reports-empty">No coupons found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="reports-section">
            <div class="reports-section-head">
                <h3>Vendor Payout Totals</h3>
            </div>
            <div class="reports-payout-grid">
                <article class="reports-mini">
                    <strong>Pending</strong>
                    <div class="reports-mini-value">KES {{ number_format((float) ($payoutTotals['pending']['net_amount'] ?? 0), 2) }}</div>
                    <small>Count: {{ $payoutTotals['pending']['count'] ?? 0 }}</small>
                </article>
                <article class="reports-mini">
                    <strong>Paid</strong>
                    <div class="reports-mini-value">KES {{ number_format((float) ($payoutTotals['paid']['net_amount'] ?? 0), 2) }}</div>
                    <small>Count: {{ $payoutTotals['paid']['count'] ?? 0 }}</small>
                </article>
            </div>
        </section>

        <section class="reports-section">
            <div class="reports-section-head">
                <h3>Vendor Commissions</h3>
                <a class="reports-btn reports-btn-secondary" href="{{ route('admin.reports.vendor-commissions.csv', request()->query()) }}">Export Vendor CSV</a>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Vendor</th><th>Commission Rate</th><th>Gross Sales</th><th>Commission</th><th>Net to Vendor</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($vendorCommissions as $vendor)
                            <tr>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ number_format((float) $vendor->commission_rate, 2) }}%</td>
                                <td>KES {{ number_format((float) $vendor->gross_sales, 2) }}</td>
                                <td>KES {{ number_format((float) $vendor->commission_amount, 2) }}</td>
                                <td>KES {{ number_format((float) $vendor->net_vendor_amount, 2) }}</td>
                                <td>
                                    <a class="reports-btn reports-btn-secondary" href="{{ route('admin.vendor-payouts.index', array_merge(request()->only(['from_date', 'to_date']), ['vendor_id' => $vendor->id])) }}">
                                        Create Payout
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="reports-empty">No vendor sales data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
