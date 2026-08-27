@extends('layouts.admin')
@section('title', 'Cashier Training')
@section('heading', 'Cashier Training')
@section('subheading', 'Daily POS operations for cashiers and managers.')

@section('content')
    <div class="admin-toolbar">
        <div></div>
        <button class="btn btn-secondary" type="button" onclick="window.print()">Print guide</button>
    </div>
    <article class="card training-doc">
        {!! $html !!}
    </article>
    <style>
        .training-doc h1 { font-size: 1.4rem; margin: 0 0 12px; }
        .training-doc h2 { font-size: 1rem; color: #b8942d; text-transform: uppercase; letter-spacing: .04em; margin: 22px 0 8px; }
        .training-doc p, .training-doc li { color: #374151; line-height: 1.55; }
        .training-doc ul { margin: 8px 0 12px 18px; }
        @media print {
            .sidebar, .topbar, .admin-toolbar, .admin-footer, .visit-store { display: none !important; }
            .shell { margin: 0; }
            .training-doc { box-shadow: none; border: 0; }
        }
    </style>
@endsection
