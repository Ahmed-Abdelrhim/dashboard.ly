<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Two-Factor Authentication QR Code' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --code-bg: #090d16;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
            max-width: 440px;
            width: 100%;
            padding: 2rem;
            text-align: center;
        }

        .icon-badge {
            width: 3.5rem;
            height: 3.5rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2));
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: #60a5fa;
        }

        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.75rem;
        }

        .qr-wrapper {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            max-width: 100%;
        }

        .qr-wrapper img,
        .qr-wrapper svg {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 0.25rem;
        }

        .info-box {
            background-color: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.375rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .secret-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.95rem;
            color: #38bdf8;
            word-break: break-all;
            user-select: all;
        }

        .copy-btn {
            background: transparent;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            transition: background 0.15s;
        }

        .copy-btn:hover {
            background: rgba(59, 130, 246, 0.15);
        }

        .meta-details {
            font-size: 0.8125rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            text-align: left;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
        }

        .meta-value {
            color: var(--text-main);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"/>
                <path d="M7 7h.01"/>
                <path d="M17 7h.01"/>
                <path d="M7 17h.01"/>
                <path d="M17 17h.01"/>
            </svg>
        </div>

        <h1>{{ $heading ?? 'Scan Two-Factor QR Code' }}</h1>
        <p class="subtitle">Open Google Authenticator or your preferred 2FA app and scan the QR code below to complete setup.</p>

        <div class="qr-wrapper">
            @if(isset($qrcodeImage))
                @php
                    $isBase64Data = str_starts_with($qrcodeImage, 'data:image/');
                    $isRawSvg = str_starts_with(trim($qrcodeImage), '<svg');
                @endphp

                @if($isRawSvg)
                    {!! $qrcodeImage !!}
                @elseif($isBase64Data)
                    <img src="{{ $qrcodeImage }}" alt="2FA QR Code" width="220" height="220">
                @else
                    {{-- Default base64 PNG or SVG --}}
                    <img src="data:image/png;base64,{{ $qrcodeImage }}" alt="2FA QR Code" width="220" height="220">
                @endif
            @elseif(isset($qrCodeSvg))
                {!! $qrCodeSvg !!}
            @elseif(isset($qrCodeInline))
                {!! $qrCodeInline !!}
            @elseif(isset($qrCodeUrl))
                <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" width="220" height="220">
            @else
                <p style="color: #ef4444; font-size: 0.875rem;">No QR code data provided.</p>
            @endif
        </div>

        @if(!empty($secretKey) || !empty($secret))
            @php $displaySecret = $secretKey ?? $secret; @endphp
            <div class="info-box">
                <div class="info-label">
                    <span>Manual Entry Key</span>
                    <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('{{ $displaySecret }}').then(() => alert('Secret key copied to clipboard!'))">Copy</button>
                </div>
                <div class="secret-code">{{ $displaySecret }}</div>
            </div>
        @endif

        @if(!empty($companyName) || !empty($companyEmail))
            <div class="meta-details">
                @if(!empty($companyName))
                    <div class="meta-row">
                        <span>Account Name:</span>
                        <span class="meta-value">{{ $companyName }}</span>
                    </div>
                @endif
                @if(!empty($companyEmail))
                    <div class="meta-row">
                        <span>Email:</span>
                        <span class="meta-value">{{ $companyEmail }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
