@extends('layouts.pos')
@section('title', !empty($scanMode) ? 'Scan & Sell' : 'Point of Sale')
@section('pos-stats')
    <div class="lbl">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a227" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 7-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 8h7v7"/></svg>
        {{ $todayCount }} {{ $todayCount === 1 ? 'Sale' : 'Sales' }}
        @if(!empty($scanMode)) · Scan @endif
    </div>
    <div class="amt">KES {{ number_format($todayRevenue, 0) }}</div>
@endsection

@push('styles')
<style>
    html, body { overflow-x: hidden; max-width: 100%; }
    .pos-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding-bottom: 88px;
    }
    .pos-catalog {
        padding: 16px 14px 20px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    /* Hero: greeting + scan */
    .pos-hero {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    .pos-greet h1 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #111827;
        line-height: 1.25;
    }
    .pos-greet p {
        margin: 4px 0 0;
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }
    .pos-scan-cta {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        color: #fff;
        border: 1px solid rgba(165,129,18,.35);
        box-shadow: 0 10px 24px rgba(15,23,42,.18);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .pos-scan-cta:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(15,23,42,.22); }
    .pos-scan-cta.is-on {
        background: linear-gradient(135deg, #a58112 0%, #c9a227 100%);
        color: #111827;
        border-color: #a58112;
    }
    .pos-scan-cta__ico {
        width: 44px; height: 44px; border-radius: 12px;
        display: grid; place-items: center; flex: 0 0 44px;
        background: rgba(255,255,255,.08);
        color: #c9a227;
    }
    .pos-scan-cta.is-on .pos-scan-cta__ico { background: rgba(0,0,0,.12); color: #111; }
    .pos-scan-cta__body { flex: 1; min-width: 0; }
    .pos-scan-cta__body strong { display: block; font-size: 15px; font-weight: 800; }
    .pos-scan-cta__body span { display: block; margin-top: 2px; font-size: 12px; opacity: .78; font-weight: 550; }
    .pos-scan-cta__key {
        font-size: 11px; font-weight: 800;
        padding: 5px 9px; border-radius: 8px;
        background: rgba(255,255,255,.1);
        letter-spacing: .04em;
    }
    .pos-scan-cta.is-on .pos-scan-cta__key { background: rgba(0,0,0,.12); }

    /* Search toolbar */
    .search-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        margin-bottom: 12px;
        align-items: stretch;
    }
    .search-field {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        padding: 0 12px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(16,24,40,.04);
    }
    .search-field svg { color: #9ca3af; flex: 0 0 auto; }
    .search-field input {
        width: 100%;
        min-width: 0;
        border: 0;
        background: transparent;
        padding: 12px 0;
        font: inherit;
        font-size: 16px;
        outline: none;
    }
    .search-field .kbd {
        display: none;
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 2px 6px;
        white-space: nowrap;
    }
    .search-row button[type=submit] {
        background: #a58112;
        border: 0;
        border-radius: 14px;
        padding: 0 18px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        min-height: 46px;
        color: #111;
        font-family: inherit;
    }
    .toolbar-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
    }
    .view-toggle {
        display: inline-flex;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        margin-left: auto;
    }
    .view-toggle button {
        border: 0;
        background: transparent;
        padding: 8px 10px;
        cursor: pointer;
        color: #6b7280;
        display: grid;
        place-items: center;
    }
    .view-toggle button.on { background: #111827; color: #c9a227; }

    .cats {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
        margin: 0 0 14px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .cats::-webkit-scrollbar { display: none; }
    .cats a {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        flex: 0 0 auto;
        color: #374151;
        transition: background .12s ease, color .12s ease, border-color .12s ease;
    }
    .cats a.on { background: #a58112; color: #111; border-color: #a58112; }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        width: 100%;
    }
    .grid.is-list {
        grid-template-columns: 1fr;
    }
    .grid.is-list .tile {
        flex-direction: row;
        align-items: stretch;
    }
    .grid.is-list .tile-media {
        width: 110px;
        flex: 0 0 110px;
    }
    .grid.is-list .tile img,
    .grid.is-list .tile .ph { height: 100%; min-height: 110px; }
    .grid.is-list .tile-body { padding: 12px 14px; }

    .tile {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(16,24,40,.04);
        min-width: 0;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .tile:hover {
        border-color: rgba(165,129,18,.45);
        box-shadow: 0 10px 24px rgba(16,24,40,.08);
        transform: translateY(-2px);
    }
    .tile-media { position: relative; width: 100%; }
    .tile img, .tile .ph {
        height: 130px;
        width: 100%;
        object-fit: cover;
        background: linear-gradient(145deg, #f3f4f6, #e5e7eb);
        display: block;
    }
    .tile-stock {
        position: absolute; left: 8px; top: 8px;
        background: #ecfdf5; color: #047857; font-size: 10px; font-weight: 800;
        border-radius: 999px; padding: 4px 8px;
        border: 1px solid #a7f3d0;
    }
    .tile-stock.low {
        background: #fef2f2; color: #b91c1c; border-color: #fecaca;
    }
    .tile-body {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
        min-width: 0;
    }
    .tile-body strong {
        font-size: 13px;
        line-height: 1.3;
        min-height: 34px;
        color: #111827;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .tile-body .sku { color: #6b7280; font-size: 11px; overflow-wrap: anywhere; }
    .tile-body b { font-size: 15px; margin-top: 4px; color: #111827; letter-spacing: -.01em; }
    .tile form { margin-top: auto; padding-top: 10px; width: 100%; }
    .tile select {
        width: 100%;
        margin-bottom: 6px;
        padding: 8px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        font-size: 12px;
        font-family: inherit;
    }
    .tile button, .cart-side .gold {
        width: 100%;
        background: #a58112;
        border: 0;
        border-radius: 11px;
        padding: 10px 8px;
        font-weight: 800;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 40px;
        color: #111;
        font-family: inherit;
    }
    .tile button:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }

    .pos-pager-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 16px;
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    /* Cart */
    .cart-side {
        background: #fff;
        padding: 16px 14px 20px;
        display: flex;
        flex-direction: column;
        margin: 8px 12px 16px;
        border-radius: 20px;
        border: 1px solid #ececec;
        scroll-margin-top: 90px;
        box-shadow: 0 8px 28px rgba(16,24,40,.06);
        gap: 0;
        min-height: 0;
    }
    .cs-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .cs-title {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .cs-title-ico {
        width: 40px; height: 40px; border-radius: 12px;
        background: #f6f0df; color: #a58112;
        display: grid; place-items: center; flex: 0 0 40px;
    }
    .cs-title h2 {
        margin: 0; font-size: 17px; font-weight: 800; color: #111827;
    }
    .cs-clear {
        display: inline-flex; align-items: center; gap: 6px;
        border: 0; border-radius: 999px; padding: 8px 12px;
        background: #fef2f2; color: #dc2626; font-weight: 700; font-size: 12px;
        cursor: pointer; font-family: inherit;
    }
    .cs-hold {
        display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid #a58112; border-radius: 999px; padding: 8px 12px;
        background: #111827; color: #c9a227; font-weight: 700; font-size: 12px;
        cursor: pointer; font-family: inherit;
    }
    .cs-empty {
        text-align: center; color: #6b7280; font-size: 13px;
        padding: 32px 16px; background: #fafafa; border-radius: 16px; border: 1px dashed #e5e7eb;
    }
    .cs-empty svg { margin: 0 auto 10px; display: block; color: #d1d5db; }
    .cs-items {
        flex: 1 1 auto;
        min-height: 140px;
        max-height: min(42vh, 360px);
        overflow-x: hidden;
        overflow-y: auto;
        margin-bottom: 8px;
        padding-right: 2px;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
    .cs-items-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .cs-item {
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr) auto;
        gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f1f1;
        align-items: start;
        flex-shrink: 0;
    }
    .cs-item:last-child { border-bottom: 0; }
    .cs-thumb {
        width: 56px; height: 56px; border-radius: 12px; object-fit: cover;
        background: #f3f4f6; display: block;
    }
    .cs-thumb.ph { background: #eee; }
    .cs-info { min-width: 0; }
    .cs-info strong {
        display: block; font-size: 13px; line-height: 1.3; color: #111827;
        overflow-wrap: anywhere;
    }
    .cs-info .each { display: block; margin-top: 2px; color: #6b7280; font-size: 11px; }
    .cs-qty {
        display: inline-flex; align-items: center; margin-top: 8px;
        border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff;
    }
    .cs-qty form { margin: 0; }
    .cs-qty button, .cs-qty .qty-val {
        border: 0; background: #fff; min-width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 14px; color: #111827; cursor: pointer; font-family: inherit;
    }
    .cs-qty input[type=number] {
        width: 38px; height: 32px; border: 0; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;
        text-align: center; font-weight: 800; font-size: 13px; background: #fff; -moz-appearance: textfield;
    }
    .cs-qty input[type=number]::-webkit-outer-spin-button,
    .cs-qty input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .cs-right {
        display: flex; flex-direction: column; align-items: flex-end; gap: 8px; min-width: 0;
    }
    .cs-remove {
        width: 28px; height: 28px; border-radius: 8px; border: 1px solid #ececec;
        background: #fff; color: #6b7280; cursor: pointer; display: grid; place-items: center;
        font-size: 14px; font-weight: 700; font-family: inherit; padding: 0;
    }
    .cs-line {
        font-size: 13px; font-weight: 800; color: #a58112; text-align: right; white-space: nowrap;
    }
    .cs-coupon {
        display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px;
        margin: 12px 0; align-items: stretch;
    }
    .cs-coupon-field {
        display: flex; align-items: center; gap: 8px;
        border: 1.5px dashed #a58112; border-radius: 12px; padding: 0 12px; background: #fffef8; min-width: 0;
    }
    .cs-coupon-field span { color: #a58112; flex: 0 0 auto; }
    .cs-coupon-field input {
        border: 0; background: transparent; width: 100%; min-width: 0; padding: 11px 0; font: inherit; font-size: 13px;
    }
    .cs-coupon button, .cs-apply {
        border: 0; border-radius: 12px; background: #a58112; color: #111;
        font-weight: 800; padding: 0 14px; cursor: pointer; font-family: inherit; white-space: nowrap;
    }
    .cs-coupon-applied {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        margin: 12px 0; padding: 10px 12px; border-radius: 12px; background: #f0fdf4; border: 1px solid #bbf7d0;
        font-size: 13px; font-weight: 700; color: #166534;
    }
    .cs-coupon-applied button {
        border: 0; background: transparent; color: #dc2626; font-weight: 700; cursor: pointer; font-family: inherit;
    }
    .cs-summary {
        background: #faf6ea; border: 1px solid #f0e6c8; border-radius: 16px; padding: 12px 14px;
        margin-bottom: 12px;
    }
    .cs-summary-row {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        font-size: 13px; color: #4b5563; margin: 7px 0;
    }
    .cs-summary-row strong { color: #111827; }
    .cs-summary-divider {
        border: 0; border-top: 1px dashed #e5d7a8; margin: 8px 0;
    }
    .cs-summary-total {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        margin-top: 2px;
        padding: 10px 12px;
        border-radius: 12px;
        background: linear-gradient(135deg, #a58112, #c9a227);
        color: #111;
    }
    .cs-summary-total span { font-size: 14px; font-weight: 800; }
    .cs-summary-total strong { font-size: 20px; font-weight: 800; }

    .cs-section { margin-bottom: 12px; }
    .cs-section-label {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
    }
    .cs-customer-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        align-items: stretch;
    }
    .cs-field {
        display: flex; align-items: center; gap: 8px;
        border: 1px solid #e5e7eb; border-radius: 12px; padding: 0 12px; background: #fff;
    }
    .cs-field span { color: #9ca3af; flex: 0 0 auto; }
    .cs-field input, .cs-field select, .cs-field textarea {
        border: 0; background: transparent; width: 100%; min-width: 0; padding: 11px 0; font: inherit; font-size: 14px;
    }
    .cs-field textarea { padding: 10px 0; resize: vertical; }
    .cs-new-btn {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 0 14px; border-radius: 12px; border: 1px solid #e5e7eb;
        background: #fff; font-weight: 800; font-size: 13px; color: #111827;
        white-space: nowrap; font-family: inherit; cursor: pointer;
    }
    .cs-hint { color: #6b7280; font-size: 12px; }
    .cs-change { font-weight: 800; color: #15803d; font-size: 13px; margin-top: 6px; }
    .cs-link-btn {
        border: 0; background: transparent; color: #a58112; font-weight: 700;
        cursor: pointer; font-family: inherit; padding: 0;
    }

    .pay-pills {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }
    .price-pills {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 8px;
    }
    .pay-pill, .price-pill {
        appearance: none;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 12px;
        padding: 10px 6px;
        font-size: 12px;
        font-weight: 800;
        color: #4b5563;
        cursor: pointer;
        font-family: inherit;
        text-align: center;
        transition: background .12s ease, border-color .12s ease, color .12s ease;
    }
    .pay-pill.on, .price-pill.on {
        background: #a58112;
        border-color: #a58112;
        color: #111;
    }

    .cs-charge-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 46px;
        gap: 8px;
        margin-top: 8px;
    }
    .cs-charge {
        width: 100%;
        background: #a58112;
        color: #111;
        border: 0;
        border-radius: 14px;
        padding: 14px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: inherit;
        box-shadow: 0 8px 20px rgba(165,129,18,.28);
    }
    .cs-charge:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
    .cs-more {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        display: grid;
        place-items: center;
        color: #6b7280;
        font-family: inherit;
    }
    .cs-secure {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        margin-top: 12px; color: #9ca3af; font-size: 12px; font-weight: 600;
    }
    .cs-cart-foot { flex: 0 0 auto; }
    .cs-acc { border: 1px solid #ececec; border-radius: 14px; margin-bottom: 10px; overflow: hidden; background: #fff; }
    .cs-acc-toggle {
        width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 14px; border: 0; background: #fff; cursor: pointer; font-family: inherit; text-align: left;
    }
    .cs-acc-toggle .left {
        display: inline-flex; align-items: center; gap: 10px; font-weight: 800; font-size: 14px; color: #111827;
    }
    .cs-acc-body { display: none; padding: 0 14px 14px; }
    .cs-acc.open .cs-acc-body { display: grid; gap: 10px; }
    .cs-notes-wrap { display: none; }
    .cs-notes-wrap.show { display: block; margin-top: 8px; }

    .pos-cart-dock {
        display: flex;
        position: fixed;
        left: 12px; right: 12px; bottom: 12px;
        z-index: 35;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 18px;
        background: #111827;
        color: #fff;
        box-shadow: 0 12px 28px rgba(0,0,0,.28);
        text-decoration: none;
        max-width: calc(100% - 24px);
        box-sizing: border-box;
    }
    .pos-cart-dock strong { display: block; font-size: 15px; color: #c9a227; }
    .pos-cart-dock span { display: block; font-size: 11px; color: rgba(255,255,255,.7); margin-top: 2px; }
    .pos-cart-dock .dock-cta {
        background: #a58112;
        color: #111;
        border-radius: 999px;
        padding: 11px 14px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    @media (min-width: 981px) {
        .pos-wrap {
            grid-template-columns: minmax(0, 1.55fr) 400px;
            padding-bottom: 0;
            min-height: calc(100vh - 72px);
        }
        .pos-catalog { padding: 20px 22px 28px; }
        .pos-hero {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
        }
        .pos-greet h1 { font-size: 1.55rem; }
        .pos-scan-cta { min-width: 280px; }
        .search-field input { font-size: 14px; }
        .search-field .kbd { display: inline-block; }
        .grid { grid-template-columns: repeat(auto-fill, minmax(168px, 1fr)); gap: 14px; }
        .tile img, .tile .ph { height: 148px; }
        .tile-body strong { font-size: 14px; min-height: 36px; }
        .cart-side {
            margin: 0;
            border-radius: 0;
            border: 0;
            border-left: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            height: calc(100vh - 64px);
            max-height: calc(100vh - 64px);
            overflow: hidden;
            align-self: start;
            padding: 18px 16px 16px;
            box-shadow: none;
        }
        .cs-items {
            flex: 1 1 0;
            min-height: 160px;
            max-height: none;
            overflow-y: auto;
        }
        .cs-cart-foot {
            flex: 0 0 auto;
            max-height: 58%;
            overflow-y: auto;
            padding-top: 4px;
            border-top: 1px solid #f1f1f1;
            -webkit-overflow-scrolling: touch;
        }
        .pos-cart-dock { display: none; }
    }
</style>
@endpush

@section('content')
@php
    $posBase = !empty($scanMode) ? 'admin.pos.scan' : 'admin.pos.index';
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', auth()->user()?->name ?? 'Cashier')[0];
    $viewMode = request('view', 'grid') === 'list' ? 'list' : 'grid';
@endphp
<div class="pos-wrap {{ !empty($scanMode) ? 'scan-mode' : '' }}">
    <section class="pos-catalog">
        <div class="pos-hero">
            <div class="pos-greet">
                <h1>{{ $greeting }}, {{ $firstName }} 👋</h1>
                <p>{{ !empty($scanMode) ? 'Scan mode on — barcodes add instantly to the sale.' : 'Ready to make a sale?' }}</p>
            </div>
            <a href="{{ route('admin.pos.scan') }}" class="pos-scan-cta {{ !empty($scanMode) ? 'is-on' : '' }}">
                <span class="pos-scan-cta__ico" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M20 7V5a1 1 0 00-1-1h-2M20 17v2a1 1 0 01-1 1h-2M7 12h10"/></svg>
                </span>
                <span class="pos-scan-cta__body">
                    <strong>{{ !empty($scanMode) ? 'Scan & Sell Active' : 'Scan Barcode' }}</strong>
                    <span>{{ !empty($scanMode) ? 'Point scanner at products' : 'Use your scanner here' }}</span>
                </span>
                <span class="pos-scan-cta__key">F2</span>
            </a>
        </div>

        <form class="search-row" method="GET" action="{{ route($posBase) }}">
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            @if($viewMode === 'list')
                <input type="hidden" name="view" value="list">
            @endif
            <label class="search-field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
                <input id="pos-search-input" type="text" name="q" value="{{ request('q') }}" placeholder="{{ !empty($scanMode) ? 'Scan barcode / SKU…' : 'Search products, SKU, or scan barcode…' }}" autofocus autocomplete="off">
                <span class="kbd">⌘ K</span>
            </label>
            <button type="submit">{{ !empty($scanMode) ? 'Scan' : 'Search' }}</button>
        </form>

        <div class="toolbar-row">
            <div class="cats" style="margin:0;flex:1;">
                <a class="{{ !request('category_id') ? 'on' : '' }}" href="{{ route($posBase, array_filter(['q' => request('q'), 'view' => $viewMode === 'list' ? 'list' : null])) }}">All</a>
                @foreach($categories as $category)
                    <a class="{{ (string) request('category_id') === (string) $category->id ? 'on' : '' }}" href="{{ route($posBase, array_filter(['q' => request('q'), 'category_id' => $category->id, 'view' => $viewMode === 'list' ? 'list' : null])) }}">{{ $category->name }}</a>
                @endforeach
            </div>
            <div class="view-toggle" role="group" aria-label="View mode">
                <button type="button" class="{{ $viewMode === 'grid' ? 'on' : '' }}" onclick="location.href='{{ route($posBase, array_filter(['q' => request('q'), 'category_id' => request('category_id')])) }}'" title="Grid view" aria-label="Grid view">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>
                </button>
                <button type="button" class="{{ $viewMode === 'list' ? 'on' : '' }}" onclick="location.href='{{ route($posBase, array_filter(['q' => request('q'), 'category_id' => request('category_id'), 'view' => 'list'])) }}'" title="List view" aria-label="List view">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <div class="grid {{ $viewMode === 'list' ? 'is-list' : '' }}">
            @forelse($products as $product)
                @php
                    $hasVariants = (bool) ($product->has_variants ?? false);
                    $sellStock = $hasVariants ? (int) $product->activeVariants->sum('stock') : (int) $product->stock;
                    $lowStock = $sellStock > 0 && $sellStock <= 10;
                @endphp
                <article class="tile">
                    <div class="tile-media">
                        @if($product->image_url)
                            <img src="{{ str_starts_with($product->image_url, 'http') ? $product->image_url : asset($product->image_url) }}" alt="{{ $product->name }}">
                        @else
                            <img src="{{ asset('images/products/placeholder.svg') }}" alt="{{ $product->name }}">
                        @endif
                        <span class="tile-stock {{ $lowStock ? 'low' : '' }}">{{ $sellStock }} in stock</span>
                    </div>
                    <div class="tile-body">
                        <strong>{{ $product->name }}</strong>
                        <span class="sku">
                            @if($hasVariants)
                                {{ $product->activeVariants->count() }} variants
                            @else
                                {{ $product->sku ?: ($product->barcode ?: $product->category?->name) }}
                            @endif
                        </span>
                        <b>KES {{ number_format($product->currentPrice(), 2) }}</b>
                        @if($hasVariants)
                            <form method="POST" action="{{ route('admin.pos.add', $product) }}">
                                @csrf
                                <select name="variant_id" required>
                                    <option value="">Select variant…</option>
                                    @foreach($product->activeVariants as $variant)
                                        <option value="{{ $variant->id }}" @disabled($variant->stock < 1)>
                                            {{ $variant->name }} · KES {{ number_format($variant->currentPrice(), 2) }} ({{ $variant->stock }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" @disabled($sellStock < 1)>
                                    @if($sellStock < 1) Out of stock @else + Add to Sale @endif
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.pos.add', $product) }}">
                                @csrf
                                <button type="submit" @disabled($sellStock < 1)>
                                    @if($sellStock < 1)
                                        Out of stock
                                    @else
                                        + Add to Sale
                                    @endif
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p style="grid-column:1/-1;padding:28px;text-align:center;color:#6b7280;font-weight:600;">No products match this search.</p>
            @endforelse
        </div>

        <div class="pos-pager-bar">
            <span>
                Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                of {{ $products->total() }} products
            </span>
            <div>{{ $products->links() }}</div>
        </div>
    </section>

    <aside class="cart-side" id="pos-cart">
        <div class="cs-head">
            <div class="cs-title">
                <span class="cs-title-ico" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z"/></svg>
                </span>
                <h2>Current Sale</h2>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                @if(!empty($cart))
                <form method="POST" action="{{ route('admin.holds.store') }}">
                    @csrf
                    <button class="cs-hold" type="submit">Hold</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.pos.clear') }}">
                    @csrf
                    <button class="cs-clear" type="submit">Clear All</button>
                </form>
            </div>
        </div>

        <div class="cs-items">
            @if(!empty($cart))
                <div class="cs-items-meta">
                    <span>{{ count($cart) }} {{ count($cart) === 1 ? 'line' : 'lines' }}</span>
                    <span>{{ (int) $totals['count'] }} {{ (int) $totals['count'] === 1 ? 'item' : 'items' }}</span>
                </div>
            @endif
            @forelse($cart as $lineKey => $item)
                <div class="cs-item">
                    @if(!empty($item['image_url']))
                        <img class="cs-thumb" src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                    @else
                        <div class="cs-thumb ph"></div>
                    @endif
                    <div class="cs-info">
                        <strong>{{ $item['name'] }}</strong>
                        <span class="each">KES {{ number_format($item['price'], 2) }} each @if(!empty($item['sku'])) · {{ $item['sku'] }}@endif</span>
                        <div class="cs-qty">
                            <form method="POST" action="{{ route('admin.pos.update', $lineKey) }}">
                                @csrf
                                <input type="hidden" name="qty" value="{{ max(0, $item['qty'] - 1) }}">
                                <button type="submit" aria-label="Decrease quantity">−</button>
                            </form>
                            <form method="POST" action="{{ route('admin.pos.update', $lineKey) }}">
                                @csrf
                                <input type="number" name="qty" min="0" value="{{ $item['qty'] }}" onchange="this.form.submit()" aria-label="Quantity">
                            </form>
                            <form method="POST" action="{{ route('admin.pos.update', $lineKey) }}">
                                @csrf
                                <input type="hidden" name="qty" value="{{ $item['qty'] + 1 }}">
                                <button type="submit" aria-label="Increase quantity">+</button>
                            </form>
                        </div>
                    </div>
                    <div class="cs-right">
                        <form method="POST" action="{{ route('admin.pos.remove', $lineKey) }}">
                            @csrf
                            <button class="cs-remove" type="submit" aria-label="Remove item">×</button>
                        </form>
                        <div class="cs-line">KES {{ number_format($item['price'] * $item['qty'], 2) }}</div>
                    </div>
                </div>
            @empty
                <div class="cs-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z"/></svg>
                    No items yet. Scan a product or click <strong>+ Add to Sale</strong> to get started.
                </div>
            @endforelse
        </div>

        <div class="cs-cart-foot">
        @php $priceMode = $priceMode ?? ($totals['price_mode'] ?? 'retail'); @endphp
        <div class="cs-section" style="margin-bottom:10px;">
            <p class="cs-section-label">Pricing</p>
            <div class="price-pills" role="group" aria-label="Price mode">
                <form method="POST" action="{{ route('admin.pos.price-mode') }}" style="display:contents;">
                    @csrf
                    <input type="hidden" name="price_mode" value="retail">
                    <button type="submit" class="price-pill {{ $priceMode === 'retail' ? 'on' : '' }}">Retail</button>
                </form>
                <form method="POST" action="{{ route('admin.pos.price-mode') }}" style="display:contents;">
                    @csrf
                    <input type="hidden" name="price_mode" value="wholesale">
                    <button type="submit" class="price-pill {{ $priceMode === 'wholesale' ? 'on' : '' }}">Wholesale</button>
                </form>
            </div>
            @if($priceMode === 'wholesale')
                <small class="cs-hint">Wholesale prices must be set on each product. Items without a wholesale price cannot be added.</small>
            @endif
        </div>

        @if(($totals['sale_discount'] ?? 0) > 0)
            <div class="cs-coupon-applied">
                <span>Discount −KES {{ number_format($totals['sale_discount'], 2) }}</span>
                <form method="POST" action="{{ route('admin.pos.discount.remove') }}">
                    @csrf
                    <button type="submit">Remove</button>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('admin.pos.discount.apply') }}" class="cs-coupon" style="grid-template-columns: auto minmax(0,1fr) auto;">
                @csrf
                <select name="discount_type" aria-label="Discount type" style="border:1.5px solid #e5e7eb;border-radius:12px;padding:0 10px;background:#fff;font:inherit;font-size:13px;font-weight:700;">
                    <option value="amount">KES</option>
                    <option value="percent">%</option>
                </select>
                <label class="cs-coupon-field">
                    <span aria-hidden="true">−</span>
                    <input type="number" name="discount_value" min="0" step="0.01" placeholder="Discount">
                </label>
                <button class="cs-apply" type="submit">Apply</button>
            </form>
        @endif

        @if($totals['coupon_code'])
            <div class="cs-coupon-applied">
                <span>Coupon {{ $totals['coupon_code'] }} applied</span>
                <form method="POST" action="{{ route('admin.pos.coupon.remove') }}">
                    @csrf
                    <button type="submit">Remove</button>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('admin.pos.coupon.apply') }}" class="cs-coupon">
                @csrf
                <label class="cs-coupon-field">
                    <span aria-hidden="true">%</span>
                    <input type="text" name="coupon_code" placeholder="Coupon code">
                </label>
                <button class="cs-apply" type="submit">Apply</button>
            </form>
        @endif

        <div class="cs-summary">
            <div class="cs-summary-row">
                <span>Items</span>
                <strong>{{ $totals['count'] }}</strong>
            </div>
            <div class="cs-summary-row">
                <span>Subtotal {{ $priceMode === 'wholesale' ? '(wholesale)' : '(retail)' }}</span>
                <strong>KES {{ number_format($totals['subtotal'], 2) }}</strong>
            </div>
            @if(($totals['sale_discount'] ?? 0) > 0)
                <div class="cs-summary-row">
                    <span>Sale Discount</span>
                    <strong>- KES {{ number_format($totals['sale_discount'], 2) }}</strong>
                </div>
            @endif
            @if(($totals['coupon_discount'] ?? 0) > 0)
                <div class="cs-summary-row">
                    <span>Coupon Discount</span>
                    <strong>- KES {{ number_format($totals['coupon_discount'], 2) }}</strong>
                </div>
            @endif
            @if(($totals['loyalty_discount'] ?? 0) > 0)
                <div class="cs-summary-row">
                    <span>Loyalty Discount</span>
                    <strong>- KES {{ number_format($totals['loyalty_discount'], 2) }}</strong>
                </div>
            @endif
            @if(!empty($totals['tax_enabled']))
                <div class="cs-summary-row">
                    <span>{{ $totals['tax_label'] ?? 'Tax' }}</span>
                    <strong>KES {{ number_format($totals['tax'] ?? 0, 2) }}</strong>
                </div>
            @endif
            <hr class="cs-summary-divider">
            <div class="cs-summary-total">
                <span>Total</span>
                <strong>KES {{ number_format($totals['total'], 2) }}</strong>
            </div>
        </div>

        <div class="cs-section">
            <p class="cs-section-label">Customer</p>
            @php $loyaltyOn = ($loyaltySettings->enabled ?? false); @endphp

            @if($loyaltyOn)
                @if($posCustomer ?? null)
                    <div style="margin-bottom:10px;padding:10px 12px;border-radius:12px;background:rgba(165,129,18,.08);border:1px solid rgba(165,129,18,.25);">
                        <div style="font-weight:700;">{{ $posCustomer->name }}</div>
                        <div style="font-size:12px;opacity:.8;">{{ $posCustomer->phone ?: 'No phone' }}</div>
                        @if($loyaltyCard ?? null)
                            <div style="margin-top:6px;font-size:13px;">⭐ {{ number_format($loyaltyCard->points_balance) }} Points · {{ $loyaltyCard->card_number }}</div>
                            @if(($loyaltySettings->show_estimated_points_on_pos ?? false) && ($estimatedPoints ?? 0) > 0)
                                <div style="font-size:12px;color:#a58112;margin-top:2px;">Estimated from this sale: +{{ $estimatedPoints }}</div>
                            @endif
                        @else
                            <div style="margin-top:6px;font-size:12px;">No loyalty card — one will be issued on sale.</div>
                        @endif
                        <form method="POST" action="{{ route('admin.pos.customer.clear') }}" style="margin-top:8px;">
                            @csrf
                            <button type="submit" class="cs-link-btn" style="font-size:12px;">Clear customer</button>
                        </form>
                    </div>

                    @if(($loyaltySettings->redemption_enabled ?? false) && ($loyaltySettings->allow_redemption_at_pos ?? false) && ($loyaltyCard ?? null))
                        <div style="margin-bottom:10px;">
                            @if(($totals['loyalty_points_redeemed'] ?? 0) > 0)
                                <div style="font-size:12px;margin-bottom:6px;">
                                    Redeeming {{ $totals['loyalty_points_redeemed'] }} pts (−KES {{ number_format($totals['loyalty_discount'], 2) }})
                                </div>
                                <form method="POST" action="{{ route('admin.pos.loyalty.redeem.clear') }}">
                                    @csrf
                                    <button type="submit" class="cs-link-btn" style="font-size:12px;">Clear redemption</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.pos.loyalty.redeem') }}" style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    <input type="number" name="points" min="{{ (int) $loyaltySettings->redemption_points }}" step="{{ (int) $loyaltySettings->redemption_points }}" max="{{ (int) $loyaltyCard->points_balance }}" placeholder="{{ (int) $loyaltySettings->redemption_points }} pts" style="flex:1;min-width:0;padding:8px;border-radius:8px;border:1px solid #ddd;">
                                    <button type="submit" class="cs-link-btn" style="white-space:nowrap;">Redeem</button>
                                </form>
                                <small class="cs-hint">{{ (int) $loyaltySettings->redemption_points }} pts = KES {{ number_format((float) $loyaltySettings->redemption_value, 2) }}</small>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="cs-customer-row" style="margin-bottom:8px;">
                        <form method="GET" action="{{ route($scanMode ? 'admin.pos.scan' : 'admin.pos.index') }}" style="min-width:0;">
                            @if(request('category_id'))
                                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            @endif
                            @if($scanMode)
                                <input type="hidden" name="mode" value="scan">
                            @endif
                            <label class="cs-field">
                                <span aria-hidden="true">🔍</span>
                                <input type="text" name="customer_q" value="{{ $customerSearch ?? '' }}" placeholder="Select customer (optional)">
                            </label>
                        </form>
                        <a href="{{ route('admin.customers.index') }}" class="cs-new-btn">+ New</a>
                    </div>
                    @if(($customerResults ?? collect())->isNotEmpty())
                        <div style="margin-bottom:10px;max-height:140px;overflow:auto;">
                            @foreach($customerResults as $c)
                                <form method="POST" action="{{ route('admin.pos.customer.select') }}" style="margin-bottom:4px;">
                                    @csrf
                                    <input type="hidden" name="shop_customer_id" value="{{ $c->id }}">
                                    <button type="submit" style="width:100%;text-align:left;padding:8px 10px;border-radius:8px;border:1px solid #eee;background:#fff;cursor:pointer;font-family:inherit;">
                                        <strong>{{ $c->name }}</strong>
                                        <span style="font-size:12px;opacity:.75;"> · {{ $c->phone ?: '—' }}</span>
                                        @if($c->loyaltyCard)
                                            <span style="font-size:12px;color:#a58112;"> · ⭐ {{ $c->loyaltyCard->points_balance }}</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @endif
            @endif
        </div>

        <form method="POST" action="{{ route('admin.pos.charge') }}" id="pos-charge-form">
            @csrf

            <div class="cs-section" style="margin-top:0;">
                <label class="cs-field" style="margin-bottom:8px;">
                    <span aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $posCustomer->name ?? '') }}" placeholder="Customer name (optional)" @disabled($posCustomer ?? false)>
                </label>
                <label class="cs-field">
                    <span aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2 19.8 19.8 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.8 19.8 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
                    </span>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $posCustomer->phone ?? '') }}" placeholder="Phone (optional)" @disabled($posCustomer ?? false)>
                </label>
            </div>

            <div class="cs-section">
                <p class="cs-section-label">Payment Method</p>
                <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method', 'cash') }}">
                <div class="pay-pills" role="group" aria-label="Payment method">
                    <button type="button" class="pay-pill {{ old('payment_method', 'cash') === 'cash' ? 'on' : '' }}" data-pay="cash">Cash</button>
                    <button type="button" class="pay-pill {{ old('payment_method') === 'card' ? 'on' : '' }}" data-pay="card">Card</button>
                    <button type="button" class="pay-pill {{ old('payment_method') === 'mobile_money' ? 'on' : '' }}" data-pay="mobile_money">M-Pesa</button>
                    <button type="button" class="pay-pill {{ old('payment_method') === 'bank_transfer' ? 'on' : '' }}" data-pay="bank_transfer">Other</button>
                </div>
                <label class="cs-field" id="tendered-wrap">
                    <span aria-hidden="true">KES</span>
                    <input
                        id="amount_tendered"
                        type="number"
                        step="0.01"
                        min="0"
                        name="amount_tendered"
                        value="{{ old('amount_tendered', $totals['total'] > 0 ? number_format($totals['total'], 2, '.', '') : '') }}"
                        placeholder="Cash received"
                        data-total="{{ number_format($totals['total'], 2, '.', '') }}"
                    >
                </label>
                <small class="cs-hint" id="tendered-hint">Auto-filled with total — enter more if customer pays extra.</small>
                <div class="cs-change" id="change-due"></div>
                <div class="cs-notes-wrap" id="notes-wrap">
                    <label class="cs-field" style="margin-top:8px;">
                        <span aria-hidden="true">📝</span>
                        <textarea name="notes" rows="2" placeholder="Note (optional)">{{ old('notes') }}</textarea>
                    </label>
                </div>
            </div>

            <div class="cs-charge-row">
                <button class="cs-charge" type="submit" id="pos-complete-sale" @disabled(empty($cart))>
                    ✓ Complete Sale · KES {{ number_format($totals['total'], 2) }}
                </button>
                <button type="button" class="cs-more" id="notes-toggle" title="Add note" aria-label="Add note">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
            </div>
        </form>

        <div class="cs-secure">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 118 0v3"/></svg>
            Secure transaction
        </div>
        </div>
    </aside>
</div>

<a class="pos-cart-dock no-print" href="#pos-cart">
    <div>
        <strong>KES {{ number_format($totals['total'], 2) }}</strong>
        <span>{{ $totals['count'] }} {{ $totals['count'] === 1 ? 'item' : 'items' }} in sale</span>
    </div>
    <span class="dock-cta">View Cart &amp; Checkout</span>
</a>

<script>
    (function () {
        const total = {{ json_encode((float) $totals['total']) }};
        const method = document.getElementById('payment_method');
        const tendered = document.getElementById('amount_tendered');
        const tenderWrap = document.getElementById('tendered-wrap');
        const change = document.getElementById('change-due');
        const hint = document.getElementById('tendered-hint');
        const notesWrap = document.getElementById('notes-wrap');
        const notesToggle = document.getElementById('notes-toggle');
        let manualTender = {{ json_encode(old('amount_tendered') !== null && (float) old('amount_tendered') !== (float) $totals['total']) }};

        function formatAmount(value) {
            return Number.parseFloat(value || 0).toFixed(2);
        }

        function syncDefaultTendered() {
            if (!tendered || manualTender || total <= 0) return;
            tendered.value = formatAmount(total);
        }

        function render() {
            const isCash = method && method.value === 'cash';
            if (tenderWrap) tenderWrap.style.display = isCash ? 'flex' : 'none';
            if (hint) hint.style.display = isCash ? 'block' : 'none';
            if (!isCash || !tendered) {
                if (change) change.textContent = '';
                return;
            }
            const paid = Number.parseFloat(tendered.value || '0');
            if (paid > total) {
                change.textContent = 'Change: KES ' + (paid - total).toFixed(2);
            } else if (paid > 0 && paid < total) {
                change.textContent = 'Still short: KES ' + (total - paid).toFixed(2);
            } else {
                change.textContent = '';
            }
        }

        document.querySelectorAll('.pay-pill').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.pay-pill').forEach(function (b) { b.classList.remove('on'); });
                btn.classList.add('on');
                if (method) method.value = btn.getAttribute('data-pay');
                if (method.value === 'cash') {
                    syncDefaultTendered();
                } else {
                    manualTender = false;
                }
                render();
            });
        });

        tendered && tendered.addEventListener('input', function () {
            manualTender = true;
            render();
        });
        tendered && tendered.addEventListener('focus', function () {
            tendered.select();
        });

        notesToggle && notesToggle.addEventListener('click', function () {
            if (notesWrap) notesWrap.classList.toggle('show');
        });

        if (method && method.value === 'cash') syncDefaultTendered();
        render();
    })();
</script>
@endsection
