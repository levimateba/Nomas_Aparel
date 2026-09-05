@extends('layouts.admin')
@section('title', 'Employees')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>People</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Employees</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Employees</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Staff records, photos, printable forms, and optional system logins.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.employees.create') }}" class="ta-btn">
            <span class="text-lg leading-none">+</span> Add employee
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-8a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Employees</div>
                <div class="ta-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="ta-kpi-sub">All staff records</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Active</div>
                <div class="ta-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="ta-kpi-sub">Currently employed</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Inactive</div>
                <div class="ta-kpi-value">{{ number_format($stats['inactive']) }}</div>
                <div class="ta-kpi-sub">Not active</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">With Login</div>
                <div class="ta-kpi-value">{{ number_format($stats['users']) }}</div>
                <div class="ta-kpi-sub">System access</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="ta-field" style="flex:2; min-width:180px;">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Search name, EMP code, phone, email…">
            </div>
            <div class="ta-field">
                <label>Department</label>
                <select name="department" class="ta-select">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Status</label>
                <select name="status" class="ta-select">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    <option value="users" @selected(request('status') === 'users')>Has login</option>
                    <option value="no_login" @selected(request('status') === 'no_login')>No login</option>
                </select>
            </div>
            <button class="ta-btn" type="submit">Filter</button>
            @if(request()->hasAny(['q','status','department']))
                <a href="{{ route('admin.employees.index') }}" class="ta-btn-outline">Reset</a>
            @endif
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Job</th>
                        <th>Contact</th>
                        <th>Login</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($employee->photoUrl())
                                    <img src="{{ $employee->photoUrl() }}" alt="" class="h-10 w-10 rounded-xl object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-700">
                                        {{ strtoupper(substr($employee->first_name, 0, 1).substr($employee->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="ta-name hover:text-brand-600">{{ $employee->fullName() }}</a>
                                    <div class="ta-muted">{{ $employee->employee_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $employee->job_title ?: '—' }}</div>
                            <div class="ta-muted">{{ $employee->department ?: $employee->employmentTypeLabel() }}</div>
                        </td>
                        <td>
                            <div>{{ $employee->phone ?: '—' }}</div>
                            <div class="ta-muted">{{ $employee->email ?: '—' }}</div>
                        </td>
                        <td>
                            @if($employee->user_id)
                                <span class="ta-pill ta-pill-success">User</span>
                            @else
                                <span class="ta-pill ta-pill-muted">No login</span>
                            @endif
                        </td>
                        <td>
                            <span class="ta-pill {{ $employee->is_active ? 'ta-pill-success' : 'ta-pill-error' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="ta-actions justify-end">
                                <a class="ta-btn ta-btn-sm" href="{{ route('admin.employees.print-form', $employee) }}" target="_blank" rel="noopener" title="Print employee form">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V3"/></svg>
                                    Print
                                </a>
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.employees.show', $employee) }}">View</a>
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.employees.edit', $employee) }}">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ta-empty">No employees yet. <a href="{{ route('admin.employees.create') }}" class="font-semibold text-brand-600">Add the first employee</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">
            <div class="text-sm text-gray-500">
                Showing {{ $employees->firstItem() ?: 0 }} to {{ $employees->lastItem() ?: 0 }} of {{ $employees->total() }} employees
            </div>
            <div>{{ $employees->links() }}</div>
        </div>
    </div>
</div>
@endsection
