@extends('layouts.admin')
@section('title', 'Create Quotation')
@section('content')
    <style>
        .quotation-page {
            display: grid;
            gap: 18px;
        }
        .quotation-head {
            border: 1px solid #dbe3ec;
            border-radius: 18px;
            padding: 22px 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fc 62%, #eef2f7 100%);
            box-shadow: 0 16px 30px rgba(13, 29, 47, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
        .quotation-head h2 {
            margin: 0;
            font-size: 1.75rem;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .quotation-head p {
            margin: 10px 0 0;
            color: #556273;
            font-size: 0.92rem;
            line-height: 1.45;
        }
        .quotation-link {
            color: #0f609b;
            font-weight: 700;
            text-decoration: none;
        }
        .quotation-card {
            border: 1px solid #d7dde4;
            border-radius: 16px;
            padding: 16px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(17, 24, 39, 0.06);
        }
        .quotation-form-layout {
            display: grid;
            gap: 14px;
        }
        .quotation-section {
            border: 1px solid #dbe2ea;
            border-radius: 14px;
            padding: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%);
        }
        .quotation-section h3 {
            margin: 0;
            font-size: 1.02rem;
            color: #1f2937;
        }
        .quotation-sub {
            margin: 6px 0 12px;
            font-size: 0.88rem;
            color: #6b7280;
        }
        .quotation-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .quotation-grid {
            display: grid;
            gap: 12px;
        }
        .quotation-grid-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .quotation-grid-four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .quotation-field {
            display: grid;
            gap: 6px;
        }
        .quotation-field label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .quotation-input {
            width: 100%;
            border: 1px solid #d2dae5;
            border-radius: 12px;
            background: #f8fafc;
            color: #162233;
            font: inherit;
            font-size: 0.94rem;
            padding: 10px 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        textarea.quotation-input {
            resize: vertical;
        }
        .quotation-input:focus {
            outline: none;
            border-color: #b8c2cd;
            box-shadow: 0 0 0 4px rgba(61, 92, 126, 0.12);
            background: #fff;
        }
        .quotation-input.is-invalid {
            border-color: #d25157;
            box-shadow: 0 0 0 3px rgba(210, 81, 87, 0.15);
            background: #fff8f8;
        }
        .quotation-error {
            color: #b4232a;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .quotation-span-all {
            grid-column: 1 / -1;
        }
        .quotation-items-wrapper {
            display: grid;
            gap: 10px;
        }
        .quotation-item {
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }
        .quotation-item-grid {
            display: grid;
            grid-template-columns: 2fr 1.4fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        .quotation-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .quotation-btn {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 1;
        }
        .quotation-btn-primary {
            color: #1a1300;
            background: linear-gradient(135deg, #e2c15a 0%, #d4af37 100%);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.28);
        }
        .quotation-btn-secondary {
            color: #233142;
            background: #fff;
            border-color: #c9d3df;
        }
        .quotation-btn-danger {
            color: #fff;
            background: #dc3545;
        }
        @media (max-width: 1100px) {
            .quotation-grid-four {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .quotation-item-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .quotation-item-grid .quotation-btn-danger {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 760px) {
            .quotation-grid-two,
            .quotation-grid-four,
            .quotation-item-grid {
                grid-template-columns: 1fr;
            }
            .quotation-head h2 {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="quotation-page">
        <section class="quotation-head">
            <div>
                <h2>Create Quotation</h2>
                <p>Build a clear, professional quotation with itemized pricing and delivery scope.</p>
                @if(isset($fromQuote))
                    <p>
                        Pre-filled from Quote Request <strong>#{{ $fromQuote->id }}</strong> — {{ $fromQuote->name }}.
                        <a class="quotation-link" href="{{ route('admin.quotes.show', $fromQuote) }}">Open request</a>
                    </p>
                @endif
            </div>
            <a href="{{ route('admin.quotations.index') }}" class="quotation-btn quotation-btn-secondary">Back to List</a>
        </section>

        <section class="quotation-card">
            <form method="POST" action="{{ route('admin.quotations.store') }}">
                @csrf
                @if(isset($fromQuote))
                    <input type="hidden" name="quote_id" value="{{ $fromQuote->id }}">
                @endif

                @include('admin.quotations._form')

                <div class="quotation-actions">
                    @if(isset($fromQuote))
                        <a href="{{ route('admin.quotes.show', $fromQuote) }}" class="quotation-btn quotation-btn-secondary">Back to Quote Request</a>
                    @endif
                    <button type="submit" class="quotation-btn quotation-btn-primary">Save Quotation</button>
                </div>
            </form>
        </section>
    </div>
@endsection
