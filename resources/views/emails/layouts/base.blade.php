<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? config('app.name') }}</title>
    
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        
        /* Brand colors */
        :root {
            --brand-deep-ash: #2D3748;
            --brand-light-blue: #EBF8FF;
            --brand-accent: #3182CE;
            --text-primary: #2D3748;
            --text-secondary: #4A5568;
            --text-muted: #718096;
            --border-color: #E2E8F0;
            --success-color: #48BB78;
            --warning-color: #ED8936;
            --error-color: #F56565;
        }
        
        body {
            background-color: #F7FAFC;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            width: 100% !important;
            min-width: 100%;
        }
        
        /* Main container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, var(--brand-deep-ash) 0%, #4A5568 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .tagline {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        /* Content area */
        .email-content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .content-section {
            margin-bottom: 30px;
        }
        
        .content-section:last-child {
            margin-bottom: 0;
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
            font-weight: 600;
            line-height: 1.3;
            margin: 0 0 15px 0;
        }
        
        h1 { font-size: 24px; }
        h2 { font-size: 20px; }
        h3 { font-size: 18px; }
        h4 { font-size: 16px; }
        
        p {
            margin: 0 0 15px 0;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--brand-deep-ash);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1A202C;
        }
        
        .btn-secondary {
            background-color: var(--brand-light-blue);
            color: var(--brand-accent);
            border: 1px solid var(--brand-accent);
        }
        
        .btn-secondary:hover {
            background-color: var(--brand-accent);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        /* Cards and boxes */
        .card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .highlight-box {
            background-color: var(--brand-light-blue);
            border: 1px solid var(--brand-accent);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        
        .alert-success {
            background-color: #F0FFF4;
            border: 1px solid var(--success-color);
            color: #22543D;
        }
        
        .alert-warning {
            background-color: #FFFAF0;
            border: 1px solid var(--warning-color);
            color: #744210;
        }
        
        .alert-error {
            background-color: #FED7D7;
            border: 1px solid var(--error-color);
            color: #742A2A;
        }
        
        /* Lists */
        .list-unstyled {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .list-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            background-color: #F7FAFC;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Status badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 12px;
            letter-spacing: 0.5px;
        }
        
        .badge-success { background-color: var(--success-color); color: white; }
        .badge-warning { background-color: var(--warning-color); color: white; }
        .badge-error { background-color: var(--error-color); color: white; }
        .badge-info { background-color: var(--brand-accent); color: white; }
        .badge-secondary { background-color: var(--text-muted); color: white; }
        
        /* Footer */
        .email-footer {
            background-color: #F7FAFC;
            padding: 30px 20px;
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-links a {
            color: var(--brand-accent);
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .social-links {
            margin: 15px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 5px;
            padding: 8px;
            background-color: var(--brand-deep-ash);
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            text-align: center;
            text-decoration: none;
            line-height: 20px;
        }
        
        /* Responsive design */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0;
                border-radius: 0;
            }
            
            .email-content {
                padding: 20px 15px !important;
            }
            
            .email-header {
                padding: 20px 15px !important;
            }
            
            .btn {
                display: block !important;
                width: 100% !important;
                margin: 10px 0 !important;
            }
            
            .table {
                font-size: 14px;
            }
            
            .table th,
            .table td {
                padding: 8px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #1A202C;
            }
            
            .card {
                background-color: #2D3748;
                border-color: #4A5568;
            }
            
            body {
                background-color: #1A202C;
                color: #E2E8F0;
            }
            
            h1, h2, h3, h4, h5, h6 {
                color: #E2E8F0;
            }
            
            p {
                color: #CBD5E0;
            }
        }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-muted { color: var(--text-muted); }
        .text-small { font-size: 14px; }
        .text-large { font-size: 18px; }
        .font-bold { font-weight: 600; }
        .mb-0 { margin-bottom: 0; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-0 { margin-top: 0; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
    </style>
</head>
<body>
    <!-- Email wrapper for full width background -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px 0;">
                <!-- Main email container -->
                <div class="email-container">
                    <!-- Header -->
                    <div class="email-header">
                        <div class="logo">{{ $companyName ?? config('app.name') }}</div>
                        <p class="tagline">{{ $companyTagline ?? 'Professional Kitchen Rental Solutions' }}</p>
                    </div>
                    
                    <!-- Main content -->
                    <div class="email-content">
                        @yield('content')
                    </div>
                    
                    <!-- Footer -->
                    <div class="email-footer">
                        <div class="social-links">
                            @if(isset($socialLinks))
                                @foreach($socialLinks as $platform => $url)
                                    @if($url && $url !== '#')
                                        <a href="{{ $url }}" title="{{ ucfirst($platform) }}">
                                            @switch($platform)
                                                @case('facebook')
                                                    📘
                                                    @break
                                                @case('twitter')
                                                    🐦
                                                    @break
                                                @case('instagram')
                                                    📷
                                                    @break
                                                @case('linkedin')
                                                    💼
                                                    @break
                                                @default
                                                    🔗
                                            @endswitch
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        
                        <div class="footer-links">
                            <a href="{{ $websiteUrl ?? config('app.url') }}">Visit Website</a>
                            <a href="{{ $supportUrl ?? '#' }}">Contact Support</a>
                            <a href="{{ $dashboardUrl ?? '#' }}">My Account</a>
                        </div>
                        
                        <p>
                            <strong>{{ $companyName ?? config('app.name') }}</strong><br>
                            {{ $companyAddress ?? 'London, UK' }}<br>
                            {{ $contactPhone ?? '+44 20 7946 0958' }}
                        </p>
                        
                        <p class="text-small text-muted">
                            You received this email because you have an account with {{ $companyName ?? config('app.name') }}.<br>
                            If you no longer wish to receive these emails, you can 
                            <a href="{{ $unsubscribeUrl ?? '#' }}">unsubscribe here</a>.
                        </p>
                        
                        <p class="text-small text-muted">
                            © {{ date('Y') }} {{ $companyName ?? config('app.name') }}. All rights reserved.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>