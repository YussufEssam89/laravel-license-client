<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Unavailable</title>
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            text-align: center;
            max-width: 520px;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 40px;
            height: 40px;
            color: #ef4444;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #f1f5f9;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .status-suspended {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-expired {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-invalid {
            background: rgba(107, 114, 128, 0.15);
            color: #9ca3af;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }

        p {
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .contact {
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
        }

        .contact p {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .contact a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
        }

        .contact a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        </div>

        <h1>Application Unavailable</h1>

        @php
            $status = $status ?? 'SUSPENDED';
        @endphp

        @if($status === 'SUSPENDED')
            <span class="status-badge status-suspended">License Suspended</span>
            <p>This application's license has been suspended. Service has been temporarily disabled by the administrator.</p>
        @elseif($status === 'EXPIRED')
            <span class="status-badge status-expired">License Expired</span>
            <p>This application's license has expired. Please renew your license to restore access to the application.</p>
        @else
            <span class="status-badge status-invalid">License Invalid</span>
            <p>This application does not have a valid license. Please contact the developer to resolve this issue.</p>
        @endif

        <div class="contact">
            <p>Need assistance? Contact the developer:</p>
            <a href="mailto:info@jo-tech.org">info@jo-tech.org</a>
        </div>
    </div>
</body>
</html>
