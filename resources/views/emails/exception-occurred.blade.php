<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Alert - Payroll System</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .exception-details {
            background: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .code-block {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .severity-critical { color: #dc3545; font-weight: bold; }
        .severity-error { color: #dc3545; }
        .severity-warning { color: #ffc107; }
        .severity-info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 Exception Alert</h1>
        <p>Payroll Management System</p>
    </div>

    <div class="content">
        <p><strong>Dear Administrator,</strong></p>

        <p>An exception has occurred in the Payroll Management System that requires your attention. Please review the details below and take appropriate action.</p>

        <div class="exception-details">
            <h3>Exception Details</h3>

            <div class="info-grid">
                <span class="info-label">Type:</span>
                <span><strong>{{ $exceptionClass }}</strong></span>

                <span class="info-label">Message:</span>
                <span>{{ $message }}</span>

                <span class="info-label">File:</span>
                <span>{{ $file }}</span>

                <span class="info-label">Line:</span>
                <span>{{ $line }}</span>

                <span class="info-label">Timestamp:</span>
                <span>{{ $timestamp }}</span>
            </div>
        </div>

        <div class="exception-details">
            <h3>Request Information</h3>

            <div class="info-grid">
                <span class="info-label">URL:</span>
                <span>{{ $url }}</span>

                <span class="info-label">Method:</span>
                <span>{{ $method }}</span>

                <span class="info-label">IP Address:</span>
                <span>{{ $ip }}</span>

                <span class="info-label">User ID:</span>
                <span>{{ $userId ?: 'Not logged in' }}</span>

                <span class="info-label">User Agent:</span>
                <span>{{ $userAgent }}</span>
            </div>
        </div>

        @if(config('app.debug'))
        <div class="exception-details">
            <h3>Stack Trace</h3>
            <div class="code-block">{{ $exception->getTraceAsString() }}</div>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/admin/error-logs"
               style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                View Error Logs
            </a>
        </div>

        <div class="footer">
            <p>This is an automated message from the Payroll Management System.</p>
            <p>If you believe this is a false alarm or need assistance, please contact the development team.</p>
        </div>
    </div>
</body>
</html>