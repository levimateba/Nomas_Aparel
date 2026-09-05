<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card — {{ $user->name }}</title>
    <style>
        :root {
            --gold: #d4af37;
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: #f3f4f6;
            color: var(--ink);
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 16px 20px;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }
        .toolbar a, .toolbar button {
            appearance: none;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            color: var(--ink);
        }
        .toolbar button.primary {
            background: linear-gradient(135deg, #d4af37, #b8942d);
            border-color: #b8942d;
            color: #1a1300;
        }
        .stage {
            padding: 28px 16px 40px;
            display: flex;
            justify-content: center;
        }
        .card {
            width: 340px;
            background: #111;
            color: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0,0,0,.18);
        }
        .card-top {
            padding: 18px 18px 14px;
            background: linear-gradient(160deg, #1c1c1c, #0f0f0f);
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            background: #222;
            border: 1px solid #333;
        }
        .brand-fallback {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--gold);
            color: #1a1300;
            display: grid;
            place-items: center;
            font-weight: 900;
        }
        .brand-copy strong {
            display: block;
            font-size: 14px;
            line-height: 1.2;
        }
        .brand-copy small {
            color: var(--gold);
            font-size: 11px;
            font-weight: 700;
        }
        .card-body {
            padding: 22px 18px 20px;
            text-align: center;
        }
        .photo {
            width: 118px;
            height: 118px;
            border-radius: 50%;
            margin: 0 auto 14px;
            overflow: hidden;
            border: 3px solid var(--gold);
            background: #222;
            display: grid;
            place-items: center;
            font-size: 42px;
            font-weight: 800;
            color: var(--gold);
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .name {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
        }
        .title {
            margin: 6px 0 0;
            color: var(--gold);
            font-weight: 700;
            font-size: 14px;
        }
        .meta {
            margin-top: 16px;
            text-align: left;
            display: grid;
            gap: 8px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 12px;
        }
        .meta div {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .meta span { color: #9ca3af; }
        .meta strong { color: #fff; font-weight: 700; text-align: right; }
        .card-foot {
            padding: 12px 18px 16px;
            border-top: 1px solid #2a2a2a;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .stage { padding: 0; }
            .card { box-shadow: none; }
        }
    </style>
</head>
<body>
@php
    $store = $settings->displayName();
    $rawLogo = method_exists($settings, 'getRawOriginal') ? $settings->getRawOriginal('logo') : ($settings->logo ?? null);
    $logo = \App\Support\PublicStorageUrl::fromPath($rawLogo) ?? ($settings->logo ?? null);
    $role = $user->rolesCollection()->pluck('name')->implode(', ') ?: ($user->role?->name ?: 'Staff');
    $job = $user->job_title ?: $role;
@endphp
<div class="toolbar no-print">
    <a href="{{ route('admin.users.edit', $user) }}">← Back to user</a>
    <button class="primary" type="button" onclick="window.print()">Print Job Card</button>
</div>
<div class="stage">
    <article class="card">
        <div class="card-top">
            @if($logo)
                <img class="brand-mark" src="{{ $logo }}" alt="{{ $store }}">
            @else
                <div class="brand-fallback">{{ strtoupper(substr($store, 0, 1)) }}</div>
            @endif
            <div class="brand-copy">
                <strong>{{ $store }}</strong>
                <small>Staff Job Card</small>
            </div>
        </div>
        <div class="card-body">
            <div class="photo">
                @if($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}">
                @else
                    {{ $user->initials() }}
                @endif
            </div>
            <h1 class="name">{{ $user->name }}</h1>
            <p class="title">{{ $job }}</p>
            <div class="meta">
                <div><span>Employee ID</span><strong>{{ $user->employee_code }}</strong></div>
                <div><span>Role</span><strong>{{ $role }}</strong></div>
                @if($user->group)<div><span>Group</span><strong>{{ $user->group->name }}</strong></div>@endif
                <div><span>Email</span><strong>{{ $user->email }}</strong></div>
                @if($user->phone)<div><span>Phone</span><strong>{{ $user->phone }}</strong></div>@endif
            </div>
        </div>
        <div class="card-foot">
            Issued {{ now()->format('d M Y') }} · Valid for authorized store duties
        </div>
    </article>
</div>
</body>
</html>
