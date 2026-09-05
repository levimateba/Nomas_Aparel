<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Form — {{ $employee->fullName() }}</title>
    <style>
        :root {
            --gold: #a58112;
            --ink: #1C2434;
            --muted: #64748b;
            --line: #e5e7eb;
            --soft: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background: #eef1f6;
            color: var(--ink);
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: #fff;
            border-bottom: 1px solid var(--line);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .toolbar .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .toolbar a, .toolbar button {
            appearance: none;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            color: var(--ink);
        }
        .toolbar button.primary {
            background: var(--gold);
            border-color: var(--gold);
            color: #111;
        }
        .stage { padding: 24px 16px 40px; display: flex; justify-content: center; }
        .sheet {
            width: 210mm;
            max-width: 100%;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(28, 36, 52, .08);
        }
        .sheet-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 3px solid var(--gold);
            background: linear-gradient(180deg, #fffef8, #fff);
        }
        .brand { display: flex; gap: 12px; align-items: center; }
        .brand img {
            width: 56px; height: 56px; object-fit: contain; border-radius: 12px;
            border: 1px solid var(--line); background: #fff;
        }
        .brand-mark {
            width: 56px; height: 56px; border-radius: 12px; background: var(--gold);
            display: grid; place-items: center; font-weight: 900; color: #111; font-size: 22px;
        }
        .brand h1 { margin: 0; font-size: 18px; line-height: 1.25; }
        .brand p { margin: 2px 0 0; color: var(--muted); font-size: 12px; }
        .doc-meta { text-align: right; font-size: 12px; color: var(--muted); }
        .doc-meta strong { display: block; color: var(--ink); font-size: 15px; margin-bottom: 4px; }
        .body { padding: 22px 24px 28px; }
        .profile {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 18px;
            margin-bottom: 18px;
            align-items: start;
        }
        .photo {
            width: 120px; height: 140px; border-radius: 12px; object-fit: cover;
            border: 1px solid var(--line); background: var(--soft);
        }
        .photo-fallback {
            width: 120px; height: 140px; border-radius: 12px; border: 1px dashed #cbd5e1;
            background: var(--soft); display: grid; place-items: center;
            font-weight: 800; font-size: 28px; color: var(--gold);
        }
        .name { margin: 0; font-size: 22px; }
        .sub { margin: 4px 0 10px; color: var(--muted); font-size: 13px; }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip {
            display: inline-flex; align-items: center; border-radius: 999px;
            padding: 4px 10px; font-size: 11px; font-weight: 700;
            background: #f1f5f9; color: #334155;
        }
        .chip.ok { background: #ecfdf5; color: #047857; }
        .chip.warn { background: #fef2f2; color: #b91c1c; }
        .chip.gold { background: #faf6e8; color: #7a6210; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .section {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .section h3 {
            margin: 0;
            padding: 10px 14px;
            background: var(--soft);
            border-bottom: 1px solid var(--line);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #475569;
        }
        .rows { padding: 4px 0; }
        .row {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 8px;
            padding: 8px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .row:last-child { border-bottom: 0; }
        .row span { color: var(--muted); }
        .row b { font-weight: 600; word-break: break-word; }
        .notes {
            min-height: 64px;
            white-space: pre-wrap;
        }
        .sign {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin-top: 22px;
        }
        .sign .box {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            font-size: 12px;
            color: var(--muted);
            min-height: 56px;
        }
        .footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px dashed var(--line);
            font-size: 11px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .stage { padding: 0; }
            .sheet {
                width: 100%;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
        }
        @media (max-width: 720px) {
            .profile, .grid-2, .sign, .row { grid-template-columns: 1fr; }
            .doc-meta { text-align: left; }
            .sheet-head { flex-direction: column; }
        }
    </style>
</head>
<body>
@php
    $brand = $settings->displayName();
    $logo = $settings->logoDataUri() ?: $settings->logo;
    $gender = \App\Models\Employee::GENDERS[$employee->gender] ?? '—';
    $address = collect([$employee->address, $employee->city, $employee->county, $employee->postal_code])->filter()->implode(', ') ?: '—';
    $roleName = $employee->user
        ? ($employee->user->role?->name ?: $employee->user->roles->pluck('name')->join(', ') ?: '—')
        : '—';
@endphp

<div class="toolbar no-print">
    <div>
        <strong>Employee Form</strong>
        <div style="font-size:12px;color:#64748b;">{{ $employee->employee_number }} · {{ $employee->fullName() }}</div>
    </div>
    <div class="actions">
        <button class="primary" type="button" onclick="window.print()">Print Employee Form</button>
        <a href="{{ route('admin.employees.show', $employee) }}">Back to profile</a>
        <a href="{{ route('admin.employees.index') }}">Employees</a>
    </div>
</div>

<div class="stage">
    <article class="sheet">
        <header class="sheet-head">
            <div class="brand">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $brand }}">
                @else
                    <div class="brand-mark">N</div>
                @endif
                <div>
                    <h1>{{ $brand }}</h1>
                    <p>{{ collect([$settings->address, $settings->city, $settings->phone])->filter()->implode(' · ') ?: 'Employee HR record' }}</p>
                </div>
            </div>
            <div class="doc-meta">
                <strong>EMPLOYEE FORM</strong>
                Printed {{ now()->format('d M Y H:i') }}<br>
                Confidential — internal use
            </div>
        </header>

        <div class="body">
            <div class="profile">
                @if($employee->photoUrl())
                    <img class="photo" src="{{ $employee->photoUrl() }}" alt="">
                @else
                    <div class="photo-fallback">{{ strtoupper(substr($employee->first_name, 0, 1).substr($employee->last_name, 0, 1)) }}</div>
                @endif
                <div>
                    <h2 class="name">{{ $employee->fullName() }}</h2>
                    <p class="sub">{{ $employee->job_title ?: 'No job title' }} · {{ $employee->department ?: 'No department' }}</p>
                    <div class="chips">
                        <span class="chip gold">{{ $employee->employee_number }}</span>
                        <span class="chip {{ $employee->is_active ? 'ok' : 'warn' }}">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                        <span class="chip">{{ $employee->employmentTypeLabel() }}</span>
                        @if($employee->user_id)
                            <span class="chip ok">System user</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <section class="section">
                    <h3>Personal details</h3>
                    <div class="rows">
                        <div class="row"><span>Gender</span><b>{{ $gender }}</b></div>
                        <div class="row"><span>Date of birth</span><b>{{ optional($employee->date_of_birth)->format('d M Y') ?: '—' }}</b></div>
                        <div class="row"><span>National ID</span><b>{{ $employee->national_id ?: '—' }}</b></div>
                        <div class="row"><span>KRA PIN</span><b>{{ $employee->kra_pin ?: '—' }}</b></div>
                        <div class="row"><span>NHIF</span><b>{{ $employee->nhif_number ?: '—' }}</b></div>
                        <div class="row"><span>NSSF</span><b>{{ $employee->nssf_number ?: '—' }}</b></div>
                    </div>
                </section>

                <section class="section">
                    <h3>Contact</h3>
                    <div class="rows">
                        <div class="row"><span>Phone</span><b>{{ $employee->phone ?: '—' }}</b></div>
                        <div class="row"><span>Alt phone</span><b>{{ $employee->alt_phone ?: '—' }}</b></div>
                        <div class="row"><span>Email</span><b>{{ $employee->email ?: '—' }}</b></div>
                        <div class="row"><span>Address</span><b>{{ $address }}</b></div>
                    </div>
                </section>
            </div>

            <div class="grid-2">
                <section class="section">
                    <h3>Employment</h3>
                    <div class="rows">
                        <div class="row"><span>Department</span><b>{{ $employee->department ?: '—' }}</b></div>
                        <div class="row"><span>Job title</span><b>{{ $employee->job_title ?: '—' }}</b></div>
                        <div class="row"><span>Type</span><b>{{ $employee->employmentTypeLabel() }}</b></div>
                        <div class="row"><span>Hire date</span><b>{{ optional($employee->hire_date)->format('d M Y') ?: '—' }}</b></div>
                        <div class="row"><span>Termination</span><b>{{ optional($employee->termination_date)->format('d M Y') ?: '—' }}</b></div>
                        <div class="row"><span>Basic salary</span><b>{{ $employee->basic_salary !== null ? 'KES '.number_format((float) $employee->basic_salary, 2) : '—' }}</b></div>
                    </div>
                </section>

                <section class="section">
                    <h3>Bank &amp; emergency</h3>
                    <div class="rows">
                        <div class="row"><span>Bank</span><b>{{ collect([$employee->bank_name, $employee->bank_branch])->filter()->implode(' · ') ?: '—' }}</b></div>
                        <div class="row"><span>Account</span><b>{{ $employee->bank_account ?: '—' }}</b></div>
                        <div class="row"><span>Emergency</span><b>{{ collect([$employee->emergency_contact_name, $employee->emergency_contact_relation])->filter()->implode(' · ') ?: '—' }}</b></div>
                        <div class="row"><span>Emergency phone</span><b>{{ $employee->emergency_contact_phone ?: '—' }}</b></div>
                        <div class="row"><span>System login</span><b>{{ $employee->user?->email ?: 'None' }}</b></div>
                        <div class="row"><span>System role</span><b>{{ $roleName }}</b></div>
                    </div>
                </section>
            </div>

            <section class="section">
                <h3>Notes</h3>
                <div class="rows">
                    <div class="row" style="grid-template-columns:1fr;">
                        <b class="notes">{{ $employee->notes ?: '—' }}</b>
                    </div>
                </div>
            </section>

            <div class="sign">
                <div class="box">Employee signature / date</div>
                <div class="box">HR / Manager signature / date</div>
                <div class="box">Authorized by</div>
            </div>

            <div class="footer">
                <span>{{ $brand }} — Employee Form</span>
                <span>{{ $employee->employee_number }}</span>
            </div>
        </div>
    </article>
</div>
</body>
</html>
