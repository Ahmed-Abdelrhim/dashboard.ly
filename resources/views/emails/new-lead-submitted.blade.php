<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Lead Submitted - WAVEX CRM</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 16px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%);
            padding: 32px 32px 28px;
            text-align: left;
        }

        .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            color: #38bdf8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 6px 0 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .lead-pill {
            display: inline-block;
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            font-family: monospace;
            margin-top: 14px;
        }

        .content {
            padding: 32px;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }

        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .info-value a {
            color: #0284c7;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        .tags-wrapper {
            margin-top: 6px;
        }

        .tag {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 6px;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .cta-section {
            text-align: center;
            padding: 16px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 36px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        }

        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        .footer p {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <div class="brand-badge">WAVEX CRM</div>
                <h1>New Lead Submitted</h1>
                <p>A new prospective client has just filled out the contact form.</p>
                <div class="lead-pill"># {{ $lead->lead_number }}</div>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="info-card">
                    <!-- Lead Number -->
                    <div class="info-row">
                        <div class="info-label">Lead Number</div>
                        <div class="info-value" style="font-family: monospace; color: #0369a1; font-size: 16px;">
                            {{ $lead->lead_number }}
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $lead->name }}</div>
                    </div>

                    <!-- Phone Number -->
                    <div class="info-row">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            @if ($lead->phone)
                            <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                            @else
                            <span style="color: #94a3b8; font-weight: normal;">Not provided</span>
                            @endif
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            @if ($lead->email)
                            <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                            @else
                            <span style="color: #94a3b8; font-weight: normal;">Not provided</span>
                            @endif
                        </div>
                    </div>

                    @if ($lead->age)
                    <div class="info-row">
                        <div class="info-label">Age</div>
                        <div class="info-value">{{ $lead->age }} years old</div>
                    </div>
                    @endif

                    @if ($lead->campaign)
                    <div class="info-row">
                        <div class="info-label">Campaign / Platform</div>
                        <div class="info-value">
                            {{ $lead->campaign->name }}
                            @if ($lead->campaign->platform)
                            <span style="color: #64748b; font-size: 13px;">({{ $lead->campaign->platform }})</span>
                            @endif
                        </div>
                    </div>
                    @elseif ($lead->utm_source || $lead->utm_campaign)
                    <div class="info-row">
                        <div class="info-label">Source Attribution</div>
                        <div class="info-value" style="font-size: 13px; color: #475569;">
                            {{ $lead->utm_source ?? 'Unknown' }} / {{ $lead->utm_campaign ?? 'N/A' }}
                        </div>
                    </div>
                    @endif

                    @if (! empty($lead->interested_in) && is_array($lead->interested_in))
                    <div class="info-row">
                        <div class="info-label">Interested In</div>
                        <div class="tags-wrapper">
                            @foreach ($lead->interested_in as $interest)
                            <span class="tag">{{ $interest }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if ($lead->sessions_considering)
                    <div class="info-row">
                        <div class="info-label">Sessions Considering</div>
                        <div class="info-value">{{ $lead->sessions_considering }}</div>
                    </div>
                    @endif

                    <!-- Submission Time -->
                    <div class="info-row">
                        <div class="info-label">Submitted At</div>
                        <div class="info-value" style="font-size: 13px; color: #64748b; font-weight: normal;">
                            {{ $lead->created_at ? $lead->created_at->setTimezone('Africa/Cairo')->format('M d, Y - h:i A') : now()->setTimezone('Africa/Cairo')->format('M d, Y - h:i A') }} (Cairo Time)
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="cta-section">
                    <a href="{{ $dashboardUrl }}" target="_blank" class="cta-button">
                        Open Dashboard &rarr;
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This is an automated notification from <strong>WAVEX CRM</strong>.</p>
                <p>You are receiving this because your email is registered for new lead notifications.</p>
            </div>
        </div>
    </div>
</body>

</html>