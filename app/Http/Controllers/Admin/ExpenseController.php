<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_expenses'), 403);

        if (ExpenseCategory::query()->count() === 0) {
            foreach (['Rent', 'Utilities', 'Transport', 'Supplies', 'Salaries', 'Other'] as $name) {
                ExpenseCategory::query()->firstOrCreate(['name' => $name], ['is_active' => true]);
            }
        }

        return view('admin.expenses.index', [
            'expenses' => Expense::with(['category', 'user'])->latest('expense_date')->latest('id')->paginate(20),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'todayTotal' => (float) Expense::query()->whereDate('expense_date', today())->sum('amount'),
            'monthTotal' => (float) Expense::query()
                ->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                ->sum('amount'),
            'allTotal' => (float) Expense::query()->sum('amount'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_expenses'), 403);

        $data = $request->validate([
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,M-Pesa,Card,Bank,Credit'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        $expense = Expense::create($data + ['user_id' => auth()->id()]);
        Audit::log('expense_recorded', 'Recorded expense of KES '.$expense->amount, $expense, [
            'amount' => $expense->amount,
        ], 'expenses');

        return back()->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_expenses'), 403);
        $amount = $expense->amount;
        $expense->delete();
        Audit::log('expense_deleted', 'Deleted expense of KES '.$amount, null, ['amount' => $amount], 'expenses');

        return back()->with('success', 'Expense deleted.');
    }
}
