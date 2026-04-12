@extends('layouts.admin')
@section('title', 'Quotations')
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="margin:0;">Quotations</h2>
        <a href="{{ route('admin.quotations.create') }}" class="btn" style="text-decoration:none;">+ Create Quotation</a>
    </div>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    <div class="card" style="padding:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;">
        <table>
            <thead>
                <tr>
                    <th style="padding:12px 10px;">Quotation #</th>
                    <th>Client</th>
                    <th>Project</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="width:280px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quotation)
                    <tr>
                        <td style="padding:12px 10px;">
                            <strong>{{ $quotation->quotation_number }}</strong>
                            @if($quotation->quote_id)
                                <br><a href="{{ route('admin.quotes.show', $quotation->quote_id) }}" style="font-size:0.72rem;color:#2196f3;text-decoration:none;" title="View source quote request">
                                    <i class="fa fa-link"></i> Request #{{ $quotation->quote_id }}
                                </a>
                            @endif
                        </td>
                        <td>
                            <div>{{ $quotation->client_name }}</div>
                            <small style="color:#7a8590;">{{ $quotation->client_email }}</small>
                        </td>
                        <td>{{ $quotation->project_title }}</td>
                        <td>{{ $quotation->items_count }}</td>
                        <td>${{ number_format((float) $quotation->total_amount, 2) }}</td>
                        <td>
                            <span style="display:inline-block;padding:4px 12px;border-radius:999px;color:#fff;background:{{ $quotation->status_badge_color }};text-transform:uppercase;font-size:0.72rem;font-weight:700;">
                                {{ $quotation->status }}
                            </span>
                        </td>
                        <td>{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-secondary" style="text-decoration:none;padding:6px 12px;">Edit</a>
                            <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-secondary" style="text-decoration:none;padding:6px 12px;">Generate PDF</a>
                            <form method="POST" action="{{ route('admin.quotations.send-email', $quotation) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary" style="padding:6px 12px;">Email PDF</button>
                            </form>
                            <form method="POST" action="{{ route('admin.quotations.destroy', $quotation) }}" style="display:inline;" onsubmit="return confirm('Delete this quotation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding:6px 12px;background:#c62828;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:28px;text-align:center;color:#98a2ad;">No quotations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:14px;">{{ $quotations->links() }}</div>
    </div>
@endsection
