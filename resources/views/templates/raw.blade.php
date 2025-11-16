<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body {
            background: #edf2f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            color: #556;
            margin: 0;
            padding: 40px 0;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.04);
            padding: 40px;
        }

        .header {
            text-align: center;
            color: #9aaab6;
            margin-bottom: 18px;
        }

        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 18px 0;
        }

        .content {
            color: #556;
            line-height: 1.75;
            font-size: 16px;
        }

        .content p {
            margin: 18px 0;
        }

        .footer {
            text-align: center;
            color: #97a1aa;
            font-size: 13px;
            margin-top: 28px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="header">
                {{-- subtle logo placeholder --}}
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    style="opacity:.8">
                    <rect width="24" height="24" rx="4" fill="#e6edf2" />
                    <path d="M6 9h12v6H6z" fill="#cbd8e0" />
                </svg>
            </div>

            {{-- Allow templates to provide a prominent greeting via the rendered HTML; if not, fall back to a generic greeting --}}
            @if (!empty($subject))
                <div
                    style="font-size:14px; color:#7f8b94; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">
                    {{ $subject }}</div>
            @endif

            @if (Str::contains($html, '<h') || Str::contains($html, '<table'))
                {{-- If the HTML contains its own block-level structure, just echo it --}}
                {!! $html !!}
            @else
                <div class="greeting">Hi {{ $data['notifiable']->name ?? 'there' }},</div>
                <div class="content">{!! $html !!}</div>
            @endif

            <div class="footer">© {{ date('Y') }} {{ config('app.name', 'Application') }}. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>
