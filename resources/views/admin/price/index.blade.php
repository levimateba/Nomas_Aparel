@extends('layouts.admin')
@section('title','Pricing')
@section('content')
    <h2>Pricing</h2>
    <a class="btn" href="{{ route('admin.price.create') }}">New plan</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Amount</th><th>Period</th><th>Featured</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($prices as $price)
                <tr>
                    <td>{{ $price->title }}</td>
                    <td>{{ number_format($price->amount,2) }}</td>
                    <td>{{ $price->billing_period }}</td>
                    <td>{{ $price->featured ? 'Yes' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#pricing" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.price.edit', $price) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.price.destroy', $price) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this pricing item?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No pricing plans yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $prices->links() }}
    </div>
@endsection
