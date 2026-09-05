@extends('layouts.admin')
@section('title', 'Add Employee')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.employees.index') }}" class="hover:text-brand-500">Employees</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Add</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Add employee</h1>
        <p class="mt-1 text-sm text-gray-500">Capture full staff details. Optionally create a system login.</p>
    </div>
    <a href="{{ route('admin.employees.index') }}" class="ta-btn-outline">Back</a>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" class="ta-page">
    @csrf
    @include('admin.employees._form', ['employee' => $employee, 'roles' => $roles])
    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('admin.employees.index') }}" class="ta-btn-outline">Cancel</a>
        <button type="submit" class="ta-btn">Save employee</button>
    </div>
</form>
@endsection
