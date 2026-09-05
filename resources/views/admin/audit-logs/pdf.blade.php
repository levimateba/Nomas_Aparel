<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .muted { color: #666; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>{{ $settings->displayName() }} — Audit Logs</h1>
    <p class="muted">Generated {{ $generatedAt->format('d M Y H:i') }} · {{ $logs->count() }} entries</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Target</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $i => $log)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td>{{ $log->user?->name ?: 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->target_label ?: '—' }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
