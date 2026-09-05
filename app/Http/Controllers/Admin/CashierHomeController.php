<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Order;
use App\Services\CashierShiftService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CashierHomeController extends Controller
{
    public function __invoke(CashierShiftService $shifts): View
    {
        abort_unless(auth()->user()?->hasPermission('create_sale'), 403);

        $userId = auth()->id();

        $todayPos = Order::query()
            ->whereDate('created_at', today())
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled');

        $yesterdayPos = Order::query()
            ->whereDate('created_at', today()->subDay())
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled');

        $myTodayCount = (clone $todayPos)->where('user_id', $userId)->count();
        $myTodayRevenue = (float) (clone $todayPos)->where('user_id', $userId)->sum('total_amount');
        $storeTodayCount = (clone $todayPos)->count();
        $storeTodayRevenue = (float) (clone $todayPos)->sum('total_amount');

        $myYesterdayCount = (clone $yesterdayPos)->where('user_id', $userId)->count();
        $myYesterdayRevenue = (float) (clone $yesterdayPos)->where('user_id', $userId)->sum('total_amount');
        $storeYesterdayCount = (clone $yesterdayPos)->count();
        $storeYesterdayRevenue = (float) (clone $yesterdayPos)->sum('total_amount');

        $pct = function (float $today, float $yesterday): ?float {
            if ($yesterday <= 0) {
                return $today > 0 ? 100.0 : 0.0;
            }

            return round((($today - $yesterday) / $yesterday) * 100, 0);
        };

        $openShift = null;
        if (Schema::hasTable('cashier_shifts')) {
            $openShift = CashierShift::query()
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->latest('opened_at')
                ->first();
        }

        $recent = Order::query()
            ->withCount('items')
            ->where('source', 'pos')
            ->where('user_id', $userId)
            ->whereDate('created_at', today())
            ->latest()
            ->limit(8)
            ->get();

        $lastReceipt = Order::query()
            ->where('source', 'pos')
            ->where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->first();

        $paymentBreakdown = (clone $todayPos)
            ->where('user_id', $userId)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy(fn ($row) => strtolower((string) ($row->payment_method ?: 'other')));

        $bucket = function (string $key) use ($paymentBreakdown): array {
            $matchKeys = match ($key) {
                'cash' => ['cash'],
                'card' => ['card', 'credit_card', 'debit_card'],
                'mobile' => ['mobile_money', 'mpesa', 'm_pesa', 'mobile'],
                default => [],
            };

            $count = 0;
            $total = 0.0;
            foreach ($paymentBreakdown as $method => $row) {
                $hit = false;
                foreach ($matchKeys as $needle) {
                    if ($method === $needle || str_contains($method, $needle)) {
                        $hit = true;
                        break;
                    }
                }
                if ($key === 'other') {
                    $known = str_contains($method, 'cash')
                        || str_contains($method, 'card')
                        || str_contains($method, 'mobile')
                        || str_contains($method, 'mpesa');
                    $hit = ! $known;
                }
                if ($hit) {
                    $count += (int) $row->count;
                    $total += (float) $row->total;
                }
            }

            return ['count' => $count, 'total' => $total];
        };

        $cash = $bucket('cash');
        $card = $bucket('card');
        $mobile = $bucket('mobile');
        $other = $bucket('other');
        $payTotal = max($myTodayRevenue, 0.0001);

        $spark = function (callable $metric): array {
            $points = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = today()->subDays($i);
                $points[] = (float) $metric($day);
            }

            return $points;
        };

        $myCountSpark = $spark(fn ($day) => Order::query()
            ->whereDate('created_at', $day)
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled')
            ->where('user_id', $userId)
            ->count());

        $myRevenueSpark = $spark(fn ($day) => Order::query()
            ->whereDate('created_at', $day)
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled')
            ->where('user_id', $userId)
            ->sum('total_amount'));

        $storeCountSpark = $spark(fn ($day) => Order::query()
            ->whereDate('created_at', $day)
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled')
            ->count());

        $storeRevenueSpark = $spark(fn ($day) => Order::query()
            ->whereDate('created_at', $day)
            ->where('source', 'pos')
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount'));

        return view('admin.cashier.home', [
            'todayCount' => $myTodayCount,
            'todayRevenue' => $myTodayRevenue,
            'storeTodayCount' => $storeTodayCount,
            'storeTodayRevenue' => $storeTodayRevenue,
            'trends' => [
                'my_sales' => $pct((float) $myTodayCount, (float) $myYesterdayCount),
                'my_revenue' => $pct($myTodayRevenue, $myYesterdayRevenue),
                'store_sales' => $pct((float) $storeTodayCount, (float) $storeYesterdayCount),
                'store_revenue' => $pct($storeTodayRevenue, $storeYesterdayRevenue),
            ],
            'sparklines' => [
                'my_sales' => $myCountSpark,
                'my_revenue' => $myRevenueSpark,
                'store_sales' => $storeCountSpark,
                'store_revenue' => $storeRevenueSpark,
            ],
            'paymentChart' => [
                'labels' => ['Cash', 'Card', 'Mobile Money', 'Other'],
                'series' => [
                    round($cash['total'], 2),
                    round($card['total'], 2),
                    round($mobile['total'], 2),
                    round($other['total'], 2),
                ],
                'counts' => [$cash['count'], $card['count'], $mobile['count'], $other['count']],
                'percents' => [
                    round(($cash['total'] / $payTotal) * 100, 0),
                    round(($card['total'] / $payTotal) * 100, 0),
                    round(($mobile['total'] / $payTotal) * 100, 0),
                    round(($other['total'] / $payTotal) * 100, 0),
                ],
                'colors' => ['#12B76A', '#465FFF', '#7A5AF8', '#F79009'],
                'total' => $myTodayRevenue,
            ],
            'openShift' => $openShift,
            'recentOrders' => $recent,
            'lastReceipt' => $lastReceipt,
            'shiftSummary' => $openShift ? $shifts->performance($openShift) : null,
        ]);
    }
}
