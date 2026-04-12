@extends('layouts.admin')
@section('title', 'Product Questions')
@section('content')
    <h2>Product Questions</h2>
    <form method="GET" action="{{ route('admin.questions.index') }}" style="margin: 10px 0; display:flex; gap:8px; align-items:center;">
        <select name="status" style="max-width:180px;">
            <option value="">All</option>
            <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
            <option value="unanswered" {{ request('status') === 'unanswered' ? 'selected' : '' }}>Unanswered</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
    </form>
    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Product</th>
                <th>Customer</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Approved</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($questions as $question)
                <tr>
                    <td>{{ $question->product?->name ?: 'N/A' }}</td>
                    <td>{{ $question->name }}<br><small>{{ $question->email }}</small></td>
                    <td>{{ $question->question }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.questions.update', $question) }}">
                            @csrf @method('PATCH')
                            <textarea name="answer" rows="2" style="width:100%;min-width:240px;">{{ $question->answer }}</textarea>
                            <label style="display:block;margin-top:6px;">
                                <input type="checkbox" name="approved" value="1" {{ $question->approved ? 'checked' : '' }}>
                                Approved
                            </label>
                            <button class="btn btn-secondary" type="submit" style="margin-top:6px;">Save</button>
                        </form>
                    </td>
                    <td>{{ $question->approved ? 'Yes' : 'No' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                            @csrf @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete question?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No questions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $questions->links() }}
    </div>
@endsection
