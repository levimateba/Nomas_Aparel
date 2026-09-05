@extends('layouts.admin')
@section('title', 'Enquiry #'.$enquiry->id)

@section('page_header')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.enquiries.index') }}" class="hover:text-brand-500">Enquiries</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">#{{ $enquiry->id }}</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Enquiry #{{ $enquiry->id }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Received {{ $enquiry->created_at->format('d M Y \a\t H:i') }}</p>
    </div>
    <a href="{{ route('admin.enquiries.index') }}" class="ta-btn-outline">← Back</a>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(260px,0.7fr)]">
        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Sender information</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Full name</div>
                        <div class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $enquiry->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Email</div>
                        <a href="mailto:{{ $enquiry->email }}" class="mt-1 block font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ $enquiry->email }}</a>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Subject</div>
                        <div class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $enquiry->subject }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Submitted</div>
                        <div class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $enquiry->created_at->format('F j, Y \a\t H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Message</h2>
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-700 dark:text-gray-300">{{ $enquiry->message }}</p>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-[#faf8f2] p-5 dark:border-gray-800 dark:bg-brand-500/10">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Quick actions</h3>
                <div class="mt-4 space-y-2">
                    <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Re: '.$enquiry->subject) }}" class="ta-btn w-full justify-center">
                        Reply via email
                    </a>
                    <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Permanently delete this enquiry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ta-btn-danger w-full justify-center">Delete enquiry</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
