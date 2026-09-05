@extends('layouts.admin')
@section('title', 'Hold Sales')
@section('heading', 'Hold Sales')
@section('subheading', 'Park a cart and resume later')

@section('content')
<div class="ta-page">
    <x-admin.list-card title="Held sales" desc="From POS, use Hold current sale to park the cart. Resume from this list.">
        <x-slot:actions>
            <a href="{{ route('admin.pos.index') }}" class="ta-btn">Go to POS</a>
        </x-slot:actions>
    </x-admin.list-card>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Items</th>
                        <th>Held at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holds as $hold)
                        @php $count = collect($hold->payload['cart'] ?? [])->sum('qty'); @endphp
                        <tr>
                            <td><div class="ta-name">{{ $hold->label }}</div></td>
                            <td>{{ $count }}</td>
                            <td>{{ $hold->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="ta-actions">
                                    <form method="POST" action="{{ route('admin.holds.resume', $hold) }}">@csrf<button class="ta-btn ta-btn-sm" type="submit">Resume</button></form>
                                    <form method="POST" action="{{ route('admin.holds.destroy', $hold) }}" onsubmit="return confirm('Discard held sale?')">@csrf @method('DELETE')<button class="ta-btn-danger ta-btn-sm" type="submit">Discard</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ta-empty">No held sales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $holds->links() }}</div>
    </div>
</div>
@endsection
