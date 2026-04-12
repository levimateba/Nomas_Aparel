<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation #{{ $quote->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
        }
        .page {
            padding: 28px 34px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 18px;
            border-bottom: 2px solid #16456e;
            padding-bottom: 14px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-right {
            text-align: right;
        }
        .brand {
            font-size: 22px;
            font-weight: 700;
            color: #16456e;
            margin: 0;
        }
        .tagline {
            color: #4b5563;
            margin-top: 2px;
            font-size: 11px;
        }
        .doc-title {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #165752;
            font-weight: 700;
        }
        .doc-meta {
            margin-top: 4px;
            color: #4b5563;
            font-size: 11px;
        }
        .section {
            margin-top: 16px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
        }
        .section-head {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: 700;
            color: #111827;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }
        .section-body {
            padding: 12px;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            width: 50%;
            padding: 6px 0;
            vertical-align: top;
        }
        .label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }
        .value {
            color: #111827;
            font-size: 12px;
            font-weight: 600;
        }
        .quote-box {
            margin-top: 16px;
            border: 1px dashed #9ca3af;
            padding: 12px;
            background: #fcfcfd;
        }
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #fff;
            background: {{ $quote->status_color }};
            font-weight: 700;
        }
        .footer {
            margin-top: 22px;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $logoDataUri = null;

        if (!empty($settings->logo)) {
            $logo = (string) $settings->logo;

            if (str_starts_with($logo, 'data:image')) {
                $logoDataUri = $logo;
            } else {
                $logoPath = null;

                if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
                    $parsedPath = parse_url($logo, PHP_URL_PATH);
                    if (!empty($parsedPath)) {
                        $logoPath = public_path(ltrim((string) $parsedPath, '/'));
                    }
                } elseif (str_starts_with($logo, '/storage/')) {
                    $logoPath = public_path(ltrim($logo, '/'));
                } elseif (str_starts_with($logo, 'storage/')) {
                    $logoPath = public_path($logo);
                } else {
                    $logoPath = public_path(ltrim($logo, '/'));
                }

                if (!empty($logoPath) && is_file($logoPath) && is_readable($logoPath)) {
                    $mime = mime_content_type($logoPath) ?: 'image/png';
                    $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                }
            }
        }
    @endphp
    <div class="page">
        <div class="header">
            <div class="header-left">
                @if(!empty($logoDataUri))
                    <div style="margin-bottom: 6px;">
                        <img src="{{ $logoDataUri }}" alt="Logo" style="height: 42px;">
                    </div>
                @endif
                <h1 class="brand">{{ $settings->site_name ?? 'Elgon Tech' }}</h1>
                <div class="tagline">{{ $settings->site_tagline ?? 'ICT Consultancy' }}</div>
            </div>
            <div class="header-right">
                <p class="doc-title">Quotation Request</p>
                <div class="doc-meta">Reference: Q-{{ str_pad((string)$quote->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="doc-meta">Generated: {{ $generatedAt->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Client Information</div>
            <div class="section-body">
                <table class="grid">
                    <tr>
                        <td>
                            <span class="label">Full Name</span>
                            <span class="value">{{ $quote->name }}</span>
                        </td>
                        <td>
                            <span class="label">Email</span>
                            <span class="value">{{ $quote->email }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Phone</span>
                            <span class="value">{{ $quote->phone ?: 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="label">Company</span>
                            <span class="value">{{ $quote->company ?: 'N/A' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Project Summary</div>
            <div class="section-body">
                <table class="grid">
                    <tr>
                        <td>
                            <span class="label">Service Requested</span>
                            <span class="value">{{ $quote->service_type }}</span>
                        </td>
                        <td>
                            <span class="label">Budget Range</span>
                            <span class="value">{{ $quote->budget_range ?: 'Not specified' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Desired Timeline</span>
                            <span class="value">{{ $quote->timeline ? $quote->timeline->format('M d, Y') : 'Not specified' }}</span>
                        </td>
                        <td>
                            <span class="label">Current Status</span>
                            <span class="status">{{ $quote->status_label }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Client Requirements</div>
            <div class="section-body">
                <div>{{ $quote->project_details }}</div>
            </div>
        </div>

        @if(!empty($quote->admin_notes))
            <div class="quote-box">
                <strong>Internal Notes</strong><br>
                {{ $quote->admin_notes }}
            </div>
        @endif

        <div class="footer">
            This PDF was generated from the admin quotation workflow in {{ $settings->site_name ?? 'Elgon Tech' }}.
        </div>
    </div>
</body>
</html>
