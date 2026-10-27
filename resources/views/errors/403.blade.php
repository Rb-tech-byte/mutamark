<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Restricted | Mutamark ERP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .card {
            background: #ffffff;
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 32rem;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .icon-circle {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1rem;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            font-weight: 800;
        }
        .badge {
            display: inline-block;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 0.5rem;
            color: #0f172a;
            letter-spacing: -0.025em;
        }
        p {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 1.5rem;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media(min-width: 640px) {
            .actions {
                flex-direction: row;
                justify-content: center;
            }
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-primary {
            background: #10b981;
            color: #ffffff;
            border: 1px solid transparent;
        }
        .btn-primary:hover {
            background: #059669;
        }
        .btn-outline {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-outline:hover {
            background: #f8fafc;
        }
        .footer-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 1.5rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">🛡️</div>
        <div class="badge">HTTP 403 • Unauthorized</div>
        <h1>Access Restricted</h1>
        <p>{{ $message ?? 'You do not have the required permissions to access this enterprise module or perform this action.' }}</p>
        
        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
        </div>

        <p class="footer-text">Mutamark Enterprises Ltd • Protected by Wolinet Security Architecture</p>
    </div>
</body>
</html>
