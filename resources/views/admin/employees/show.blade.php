@extends('layouts.admin')
@section('title', $employee->fullName())

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.employees.index') }}" class="hover:text-brand-500">Employees</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-white/90">{{ $employee->fullName() }}</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $employee->fullName() }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $employee->employee_number }} · {{ $employee->job_title ?: 'No job title' }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.employees.print-form', $employee) }}" class="ta-btn" target="_blank" rel="noopener">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V3"/></svg>
            Print Employee Form
        </a>
        <a href="{{ route('admin.employees.edit', $employee) }}" class="ta-btn-outline">Edit</a>
        <a href="{{ route('admin.employees.index') }}" class="ta-btn-outline">Back</a>
    </div>
</div>
@endsection

@section('content')
@php $creds = session('generated_credentials'); @endphp

<div class="ta-page">
    @if($creds)
        <div class="rounded-2xl border border-warning-200 bg-warning-50 p-5 dark:border-warning-500/30 dark:bg-warning-500/10">
            <h2 class="text-base font-semibold text-warning-900">Login credentials (shown once)</h2>
            <p class="mt-1 text-sm text-warning-800">Copy these now and share securely with the employee. The password will not be shown again.</p>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-warning-200 bg-white px-4 py-3">
                    <div class="text-xs font-semibold uppercase text-gray-500">Email / username</div>
                    <div class="mt-1 font-mono text-sm font-bold text-gray-900">{{ $creds['email'] }}</div>
                </div>
                <div class="rounded-xl border border-warning-200 bg-white px-4 py-3">
                    <div class="text-xs font-semibold uppercase text-gray-500">Temporary password</div>
                    <div class="mt-1 font-mono text-sm font-bold text-gray-900">{{ $creds['password'] }}</div>
                </div>
            </div>
            <p class="mt-3 text-xs text-warning-800">Login URL: <code class="rounded bg-white px-1.5 py-0.5">{{ url('/admin/login') }}</code></p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                @if($employee->photoUrl())
                    <img src="{{ $employee->photoUrl() }}" alt="" class="mx-auto h-40 w-40 rounded-2xl object-cover">
                @else
                    <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-2xl bg-brand-50 text-3xl font-bold text-brand-700">
                        {{ strtoupper(substr($employee->first_name, 0, 1).substr($employee->last_name, 0, 1)) }}
                    </div>
                @endif
                <h2 class="mt-4 text-lg font-bold text-gray-800 dark:text-white/90">{{ $employee->fullName() }}</h2>
                <p class="text-sm text-gray-500">{{ $employee->job_title ?: '—' }}</p>
                <div class="mt-3 flex flex-wrap justify-center gap-2">
                    <span class="ta-pill {{ $employee->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($employee->user_id)
                        <span class="ta-pill-success">System user</span>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">System login</h3>
                @if($employee->user)
                    <p class="mt-2 text-sm text-gray-600">{{ $employee->user->email }}</p>
                    <p class="text-xs text-gray-500">Role: {{ $employee->user->role?->name ?: ($employee->user->roles->pluck('name')->join(', ') ?: '—') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.users.edit', $employee->user) }}" class="ta-btn-outline ta-btn-sm">Edit user</a>
                        <form method="POST" action="{{ route('admin.employees.reset-login', $employee) }}" onsubmit="return confirm('Generate a new temporary password?')">
                            @csrf
                            <button type="submit" class="ta-btn-outline ta-btn-sm">Regenerate password</button>
                        </form>
                    </div>
                @else
                    <p class="mt-2 text-sm text-gray-500">No login yet. Create one to let this employee use POS / admin.</p>
                    <form method="POST" action="{{ route('admin.employees.create-login', $employee) }}" class="mt-4 space-y-3">
                        @csrf
                        @if(! $employee->email)
                            <div class="ta-field">
                                <label>Email *</label>
                                <input type="email" name="email" class="ta-input" required value="{{ old('email') }}">
                            </div>
                        @endif
                        <div class="ta-field">
                            <label>Role *</label>
                            <select name="role_id" class="ta-select" required>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($role->slug === 'cashier')>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="ta-btn w-full">Generate login credentials</button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee record? The linked user account (if any) will be kept.')">
                @csrf @method('DELETE')
                <button type="submit" class="ta-btn-danger w-full">Delete employee</button>
            </form>
        </div>

        <div class="space-y-5 xl:col-span-2">
            @php
                $sections = [
                    'Identity' => [
                        'Employee number' => $employee->employee_number,
                        'Gender' => \App\Models\Employee::GENDERS[$employee->gender] ?? '—',
                        'Date of birth' => optional($employee->date_of_birth)->format('d M Y') ?: '—',
                        'National ID' => $employee->national_id ?: '—',
                        'KRA PIN' => $employee->kra_pin ?: '—',
                        'NHIF' => $employee->nhif_number ?: '—',
                        'NSSF' => $employee->nssf_number ?: '—',
                    ],
                    'Contact' => [
                        'Phone' => $employee->phone ?: '—',
                        'Alt phone' => $employee->alt_phone ?: '—',
                        'Email' => $employee->email ?: '—',
                        'Address' => collect([$employee->address, $employee->city, $employee->county, $employee->postal_code])->filter()->implode(', ') ?: '—',
                    ],
                    'Employment' => [
                        'Department' => $employee->department ?: '—',
                        'Job title' => $employee->job_title ?: '—',
                        'Type' => $employee->employmentTypeLabel(),
                        'Hire date' => optional($employee->hire_date)->format('d M Y') ?: '—',
                        'Termination' => optional($employee->termination_date)->format('d M Y') ?: '—',
                        'Basic salary' => $employee->basic_salary !== null ? 'KES '.number_format((float) $employee->basic_salary, 2) : '—',
                    ],
                    'Bank & emergency' => [
                        'Bank' => collect([$employee->bank_name, $employee->bank_branch])->filter()->implode(' · ') ?: '—',
                        'Account' => $employee->bank_account ?: '—',
                        'Emergency contact' => collect([$employee->emergency_contact_name, $employee->emergency_contact_relation])->filter()->implode(' · ') ?: '—',
                        'Emergency phone' => $employee->emergency_contact_phone ?: '—',
                    ],
                ];
            @endphp

            @foreach($sections as $title => $rows)
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
                    </div>
                    <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                        @foreach($rows as $label => $value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach

            @if($employee->notes)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Notes</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $employee->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
