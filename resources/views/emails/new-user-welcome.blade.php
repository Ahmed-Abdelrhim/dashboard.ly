<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to WAVEX CRM</title>
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
            padding: 36px 32px 30px;
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
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 8px 0 0;
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.5;
        }

        .content {
            padding: 32px;
        }

        .greeting {
            font-size: 16px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 24px;
        }

        .credentials-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .credentials-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }

        .credential-row {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .credential-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .credential-row:first-child {
            padding-top: 0;
        }

        .credential-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .credential-value {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .password-box {
            display: inline-block;
            background-color: #0f172a;
            color: #38bdf8;
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #1e293b;
        }

        .role-badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .notice-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 28px;
            font-size: 13px;
            line-height: 1.5;
            color: #92400e;
        }

        .cta-section {
            text-align: center;
            padding: 8px 0 16px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 38px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
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
                <h1>Welcome to WAVEX CRM</h1>
                <p>Your team member account has been successfully created.</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    Hello <strong>{{ $user->name }}</strong>,<br><br>
                    You have been granted access to the WAVEX CRM platform. Below are your account credentials to sign in to your dashboard.
                </div>

                <!-- Credentials Card -->
                <div class="credentials-card">
                    <div class="credentials-title">
                        🔑 Your CRM Login Credentials
                    </div>

                    <!-- Email -->
                    <div class="credential-row">
                        <div class="credential-label">Email Address</div>
                        <div class="credential-value">
                            <a href="mailto:{{ $user->email }}" style="color: #0284c7; text-decoration: none;">
                                {{ $user->email }}
                            </a>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="credential-row">
                        <div class="credential-label">Password</div>
                        <div style="margin-top: 4px;">
                            <span class="password-box">{{ $plainPassword }}</span>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="credential-row">
                        <div class="credential-label">Role</div>
                        <div style="margin-top: 4px;">
                            <span class="role-badge">{{ $user->type?->label() ?? 'Team Member' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="notice-box">
                    <strong>Security Tip:</strong> For your account security, we recommend changing your password after your first login and enabling Two-Factor Authentication (2FA) in your Profile settings.
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
                <p>If you did not expect this email, please contact your system administrator.</p>
            </div>
        </div>
    </div>
</body>

</html>
