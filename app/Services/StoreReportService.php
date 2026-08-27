<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StoreReportService
{
    public function summary(string $from, string $to, array $filters = []): array
    {
        $sales = $this->salesQuery($from, $to, $filters);
        $gross = (float) (clone $sales)->sum('total_amount');
        $discounts = Schema::hasColumn('orders', 'discount_amount')
            ? (float) (clone $sales)->sum('discount_amount')
            : 0.0;
        $returns = $this->returnsTotal($from, $to, $filters);
        $cancelled = (float) $this->ordersInRange($from, $to, $filters)
            ->where('status', 'cancelled')
            ->sum('total_amount');
        $count = (clone $sales)->count();
        $posCount = (clone $sales)->where(function ($query) {
            $this->constrainPos($query);
        })->count();
        $posRevenue = (float) (clone $sales)->where(function ($query) {
            $this->constrainPos($query);
        })->sum('total_amount');
        $net = max($gross - $returns, 0);
        $average = $count > 0 ? $net / $count : 0;

        return [
            'count' => $count,
            'gross' => $gross,
            'discount' => $discounts,
            'returns' => $returns,
            'cancelled' => $cancelled,
            'net' => $net,
            'average' => $average,
            'pos_count' => $posCount,
            'pos_revenue' => $posRevenue,
            'online_count' => max($count - $posCount, 0),
            'online_revenue' => max($gross - $posRevenue, 0),
        ];
    }

    public function daily(string $from, string $to): Collection
    {
        $rows = collect();
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        while ($cursor->lte($end) && $rows->count() < 62) {
            $day = $cursor->toDateString();
            $summary = $this->summary($day, $day);
            $rows->push([
                'date' => $day,
                'label' => $cursor->format('d M'),
                'sales' => $summary['net'],
                'count' => $summary['count'],
                'pos' => $summary['pos_revenue'],
                'online' => $summary['online_revenue'],
                'returns' => $summary['returns'],
            ]);
            $cursor->addDay();
        }

        return $rows;
    }

    public function paymentBreakdown(string $from, string $to): Collection
    {
        return $this->salesQuery($from, $to)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }

    public function statusBreakdown(string $from, string $to): Collection
    {
        return $this->ordersInRange($from, $to)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function topProducts(string $from, string $to, int $limit = 10): Collection
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('quantity')
            ->limit($limit);

        return $query->get();
    }

    public function categorySales(string $from, string $to, int $limit = 12): Collection
    {
        if (! Schema::hasTable('categories')) {
            return collect();
        }

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->select(
                DB::raw("COALESCE(categories.name, 'Uncategorised') as category_name"),
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy(DB::raw("COALESCE(categories.name, 'Uncategorised')"))
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function byCashier(string $from, string $to): Collection
    {
        $rows = $this->salesQuery($from, $to)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw(Schema::hasColumn('orders', 'discount_amount') ? 'SUM(discount_amount) as discounts' : '0 as discounts')
            )
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get();

        $users = User::query()->whereIn('id', $rows->pluck('user_id')->filter())->get()->keyBy('id');

        return $rows->map(function ($row) use ($from, $to, $users) {
            $payments = $this->salesQuery($from, $to, ['user_id' => $row->user_id])
                ->select('payment_method', DB::raw('SUM(total_amount) as total'))
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method');

            $returns = $this->returnsTotal($from, $to, ['user_id' => $row->user_id]);
            $variance = 0.0;
            if (Schema::hasTable('cashier_shifts')) {
                $variance = (float) CashierShift::query()
                    ->where('user_id', $row->user_id)
                    ->where('status', 'closed')
                    ->whereDate('closed_at', '>=', $from)
                    ->whereDate('closed_at', '<=', $to)
                    ->sum('difference');
            }

            $pay = fn (array $keys) => collect($keys)->sum(fn ($key) => (float) ($payments[$key] ?? 0));

            return (object) [
                'user_id' => $row->user_id,
                'cashier' => $users[$row->user_id]->name ?? 'Unknown',
                'transactions' => (int) $row->transactions,
                'sales' => (float) $row->sales,
                'discounts' => (float) $row->discounts,
                'returns' => $returns,
                'net' => max((float) $row->sales - $returns, 0),
                'cash' => $pay(['cash']),
                'mobile_money' => $pay(['mobile_money', 'mpesa', 'm-pesa']),
                'card' => $pay(['card']),
                'bank' => $pay(['bank_transfer', 'bank']),
                'variance' => $variance,
            ];
        })->sortByDesc('net')->values();
    }

    public function lowStock(int $limit = 15): Collection
    {
        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit($limit)
            ->get();
    }

    public function cashiers(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->where('is_admin', true)
                    ->orWhereHas('roles', function ($roles) {
                        $roles->whereIn('slug', ['admin', 'super-admin', 'manager', 'staff', 'cashier']);
                    })
                    ->orWhereHas('orders', function ($orders) {
                        $this->constrainPos($orders);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function salesQuery(string $from, string $to, array $filters = [])
    {
        return $this->ordersInRange($from, $to, $filters)
            ->where('status', '!=', 'cancelled');
    }

    private function ordersInRange(string $from, string $to, array $filters = [])
    {
        return Order::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when(($filters['source'] ?? null) === 'pos', fn ($query) => $query->where(function ($inner) {
                $this->constrainPos($inner);
            }))
            ->when(($filters['source'] ?? null) === 'online', fn ($query) => $query->where(function ($inner) {
                $inner->where(function ($online) {
                    if (Schema::hasColumn('orders', 'source')) {
                        $online->where('source', '!=', 'pos')->orWhereNull('source');
                    } else {
                        $online->where('order_number', 'not like', 'POS-%');
                    }
                });
            }));
    }

    private function constrainPos($query): void
    {
        $query->where(function ($inner) {
            if (Schema::hasColumn('orders', 'source')) {
                $inner->where('source', 'pos')->orWhere('order_number', 'like', 'POS-%');
            } else {
                $inner->where('order_number', 'like', 'POS-%');
            }
        });
    }

    private function returnsTotal(string $from, string $to, array $filters = []): float
    {
        if (! Schema::hasTable('order_returns')) {
            return 0.0;
        }

        return (float) OrderReturn::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->sum('total');
    }
}
