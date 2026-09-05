@extends('layouts.admin')
@section('title', 'Q&A')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Storefront</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Q&amp;A</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Product Q&amp;A</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Answer product questions and approve them for the storefront.</p>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M7 4h10a2 2 0 012 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 012-2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Questions</div>
                <div class="ta-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="ta-kpi-sub">All customer questions</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Unanswered</div>
                <div class="ta-kpi-value">{{ number_format($stats['unanswered']) }}</div>
                <div class="ta-kpi-sub">Needs a reply</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Answered</div>
                <div class="ta-kpi-value">{{ number_format($stats['answered']) }}</div>
                <div class="ta-kpi-sub">Replies saved</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <form method="GET" action="{{ route('admin.questions.index') }}" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="ta-field">
                <label>Status</label>
                <select name="status" class="ta-select">
                    <option value="">All</option>
                    <option value="answered" @selected(request('status') === 'answered')>Answered</option>
                    <option value="unanswered" @selected(request('status') === 'unanswered')>Unanswered</option>
                </select>
            </div>
            <button class="ta-btn" type="submit">Filter</button>
            @if(request('status'))
                <a href="{{ route('admin.questions.index') }}" class="ta-btn-outline">Reset</a>
            @endif
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Approved</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td>
                            <strong class="text-gray-800 dark:text-white/90">{{ $question->product?->name ?: 'N/A' }}</strong>
                        </td>
                        <td>
                            <div class="font-semibold text-gray-800 dark:text-white/90">{{ $question->name }}</div>
                            <div class="text-xs text-gray-500">{{ $question->email }}</div>
                        </td>
                        <td class="max-w-xs text-sm text-gray-700 dark:text-gray-300">{{ $question->question }}</td>
                        <td class="min-w-[220px]">
                            <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="answer" rows="2" class="ta-input" placeholder="Write an answer…">{{ $question->answer }}</textarea>
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" name="approved" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" @checked($question->approved)>
                                    Approved
                                </label>
                                <div>
                                    <button class="ta-btn ta-btn-sm" type="submit">Save</button>
                                </div>
                            </form>
                        </td>
                        <td>
                            @if($question->approved)
                                <span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-xs font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">Yes</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">No</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete question?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-gray-500">No questions yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($questions->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $questions->links() }}</div>
        @endif
    </div>
</div>
@endsection
