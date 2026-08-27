@extends('layouts.admin')
@section('title', 'Q&A')
@section('heading', 'Q&A')
@section('subheading', 'Answer product questions and approve them for the storefront.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Questions</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-kpi"><span>Unanswered</span><strong>{{ $stats['unanswered'] }}</strong></div>
        <div class="admin-kpi"><span>Answered</span><strong>{{ $stats['answered'] }}</strong></div>
    </div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.questions.index') }}">
        <div class="admin-filters">
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="answered" @selected(request('status') === 'answered')>Answered</option>
                    <option value="unanswered" @selected(request('status') === 'unanswered')>Unanswered</option>
                </select>
            </div>
            <button class="btn" type="submit">Apply</button>
        </div>
    </form>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Customer</th><th>Question</th><th>Answer</th><th>Approved</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td><strong>{{ $question->product?->name ?: 'N/A' }}</strong></td>
                        <td>{{ $question->name }}<br><span class="muted">{{ $question->email }}</span></td>
                        <td>{{ $question->question }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.questions.update', $question) }}">
                                @csrf @method('PATCH')
                                <textarea name="answer" rows="2" style="margin-bottom:8px;">{{ $question->answer }}</textarea>
                                <label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:0 0 8px;">
                                    <input type="checkbox" name="approved" value="1" {{ $question->approved ? 'checked' : '' }} style="width:auto;margin:0;">
                                    Approved
                                </label>
                                <button class="btn btn-secondary" type="submit">Save</button>
                            </form>
                        </td>
                        <td><span class="status-pill {{ $question->approved ? 'on' : 'off' }}">{{ $question->approved ? 'Yes' : 'No' }}</span></td>
                        <td class="row-actions">
                            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete question?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-cell">No questions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $questions->links() }}</div>
    </div>
@endsection
