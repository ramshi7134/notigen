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

        .header-banner {
            background: #eef4f8;
            /* pale banner similar to screenshot */
            border-radius: 6px;
            padding: 18px 0;
            margin: 0 -40px 18px -40px;
            /* extend banner to card edges */
        }

        .header-banner .app-name {
            font-weight: 700;
            color: #7f8b94;
            letter-spacing: 0.02em;
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
                <div class="header-banner">
                    <div class="app-name">{{ config('app.name', 'Laravel') }}</div>
                </div>
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
