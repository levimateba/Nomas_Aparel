<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Services\CashierShiftService;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    public function index(CashierShiftService $shifts)
    {
        $query = CashierShift::with('user')->latest('opened_at');
        if ($this->isFrontlineCashier()) {
            $query->where('user_id', auth()->id());
        }

        return view('admin.shifts.index', [
            'current' => $shifts->currentOpen(auth()->id()),
            'shifts' => $query->paginate(20),
        ]);
    }

    public function store(Request $request, CashierShiftService $shifts)
    {
        $data = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shifts->open((float) $data['opening_cash'], $data['notes'] ?? null);

        return back()->with('success', 'Shift opened.');
    }

    public function close(Request $request, CashierShift $shift, CashierShiftService $shifts)
    {
        $this->assertCanAccessShift($shift);

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shifts->close($shift, (float) $data['actual_cash'], $data['notes'] ?? null);

        return back()->with('success', 'Shift closed and cash reconciled.');
    }

    public function show(CashierShift $shift, CashierShiftService $shifts)
    {
        $this->assertCanAccessShift($shift);

        $shift->load(['user', 'orders']);

        return view('admin.shifts.show', [
            'shift' => $shift,
            'performance' => $shifts->performance($shift),
        ]);
    }

    private function isFrontlineCashier(): bool
    {
        $user = auth()->user();

        return (bool) ($user && method_exists($user, 'isFrontlineCashier') && $user->isFrontlineCashier());
    }

    private function assertCanAccessShift(CashierShift $shift): void
    {
        if ((int) auth()->id() === (int) $shift->user_id) {
            return;
        }

        // Managers/admins with manage_shifts can view/close any shift; cashiers only their own.
        abort_unless(
            auth()->user()?->hasPermission('manage_shifts') && ! $this->isFrontlineCashier(),
            403
        );
    }
}
