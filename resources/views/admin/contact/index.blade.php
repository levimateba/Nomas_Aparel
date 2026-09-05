@extends('layouts.admin')
@section('title', 'Contacts')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Storefront</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Contacts</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Contacts</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Phone, email, and location details shown on the public site.</p>
    </div>
    <a href="{{ route('admin.contact.create') }}" class="ta-btn">
        <span class="text-lg leading-none">+</span> Add contact
    </a>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.3a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.2l-2.2 1.1a12 12 0 005.5 5.5l1.1-2.2a1 1 0 011.2-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.7 21 3 14.3 3 6V5z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Contacts</div>
                <div class="ta-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="ta-kpi-sub">Public contact items</div>
            </div>
        </div>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Value</th>
                        <th>Type</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td><strong class="text-gray-800 dark:text-white/90">{{ $contact->label }}</strong></td>
                        <td class="text-sm text-gray-700 dark:text-gray-300">{{ $contact->value }}</td>
                        <td>
                            <span class="inline-flex rounded-full bg-[#f6f0df] px-2.5 py-1 text-xs font-bold capitalize text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">
                                {{ $contact->type }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.contact.edit', $contact) }}" class="ta-btn-outline ta-btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.contact.destroy', $contact) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this contact?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-sm text-gray-500">No contact details yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($contacts, 'hasPages') && $contacts->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $contacts->links() }}</div>
        @endif
    </div>
</div>
@endsection
