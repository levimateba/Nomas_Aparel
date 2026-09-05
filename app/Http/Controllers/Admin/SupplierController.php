<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);

        $suppliers = Supplier::query()
            ->withCount(['products', 'purchases', 'purchaseOrders'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);

        return view('admin.suppliers.form', ['supplier' => new Supplier(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);
        $supplier = Supplier::create($this->validated($request) + ['is_active' => $request->boolean('is_active', true)]);
        Audit::log('supplier_created', 'Created supplier '.$supplier->name, $supplier, [], 'suppliers');

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier): View
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);

        return view('admin.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);
        $supplier->update($this->validated($request) + ['is_active' => $request->boolean('is_active', $supplier->is_active)]);
        Audit::log('supplier_updated', 'Updated supplier '.$supplier->name, $supplier, [], 'suppliers');

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('manage_suppliers'), 403);

        if ($supplier->purchases()->exists() || $supplier->purchaseOrders()->exists()) {
            $supplier->update(['is_active' => false]);
            Audit::log('supplier_deactivated', 'Deactivated supplier '.$supplier->name, $supplier, [], 'suppliers');

            return back()->with('success', 'Supplier has purchase history — marked inactive instead of deleted.');
        }

        $name = $supplier->name;
        $supplier->delete();
        Audit::log('supplier_deleted', 'Deleted supplier '.$name, null, [], 'suppliers');

        return back()->with('success', 'Supplier deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_pin' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
