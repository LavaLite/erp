<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Confirm Action' }} - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header .icon {
            font-size: 64px;
            margin-bottom: 16px;
            display: block;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .info-box {
            background: #f8fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-box h3 {
            font-size: 18px;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #1e293b;
            font-weight: 600;
        }

        .message {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 30px;
            color: #92400e;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-secondary:hover:not(:disabled) {
            background: #e2e8f0;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .result {
            display: none;
            text-align: center;
            padding: 30px;
        }

        .result.success {
            color: #059669;
        }

        .result.error {
            color: #dc2626;
        }

        .result .icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .result h2 {
            font-size: 24px;
            margin-bottom: 12px;
        }

        .result p {
            color: #64748b;
            margin-bottom: 20px;
        }

        @media (max-width: 640px) {
            .container {
                border-radius: 0;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="icon">{{ $icon ?? '✉️' }}</span>
            <h1>{{ $title ?? 'Confirm Action' }}</h1>
            <p>{{ $subtitle ?? 'Please review and confirm your action' }}</p>
        </div>

        <div class="content" id="mainContent">
            @if(isset($infoBox))
            <div class="info-box">
                <h3>{{ $infoBox['title'] ?? 'Details' }}</h3>
                @foreach($infoBox['items'] ?? [] as $item)
                <div class="info-item">
                    <span class="info-label">{{ $item['label'] }}</span>
                    <span class="info-value">{{ $item['value'] }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if(isset($message))
            <div class="message">
                {!! $message !!}
            </div>
            @endif

            @if(isset($warning))
            <div class="warning">
                ⚠️ {{ $warning }}
            </div>
            @endif

            <div class="actions">
                @foreach($actions ?? [] as $action)
                <button 
                    type="button"
                    class="btn btn-{{ $action['type'] ?? 'primary' }}"
                    onclick="handleAction('{{ $action['url'] }}', '{{ $action['method'] ?? 'POST' }}', '{{ $action['label'] }}')"
                    @if($action['disabled'] ?? false) disabled @endif
                >
                    @if(isset($action['icon']))
                    <span>{{ $action['icon'] }}</span>
                    @endif
                    {{ $action['label'] }}
                </button>
                @endforeach

                @if(isset($cancelUrl))
                <a href="{{ $cancelUrl }}" class="btn btn-secondary">Cancel</a>
                @endif
            </div>
        </div>

        <div class="loading" id="loadingState">
            <div class="spinner"></div>
            <p>Processing your request...</p>
        </div>

        <div class="result" id="resultState">
            <span class="icon" id="resultIcon"></span>
            <h2 id="resultTitle"></h2>
            <p id="resultMessage"></p>
            @if(isset($redirectUrl))
            <a href="{{ $redirectUrl }}" class="btn btn-primary">Continue</a>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

    <script>
        async function handleAction(url, method, actionLabel) {
            const mainContent = document.getElementById('mainContent');
            const loadingState = document.getElementById('loadingState');
            const resultState = document.getElementById('resultState');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');

            // Show loading state
            mainContent.style.display = 'none';
            loadingState.style.display = 'block';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const data = await response.json();

                // Hide loading, show result
                loadingState.style.display = 'none';
                resultState.style.display = 'block';

                if (response.ok) {
                    resultState.className = 'result success';
                    resultIcon.textContent = '✅';
                    resultTitle.textContent = 'Success!';
                    resultMessage.textContent = data.message || `${actionLabel} completed successfully`;

                    // Redirect after 2 seconds if redirect URL is provided
                    @if(isset($redirectUrl))
                    setTimeout(() => {
                        window.location.href = '{{ $redirectUrl }}';
                    }, 2000);
                    @endif
                } else {
                    resultState.className = 'result error';
                    resultIcon.textContent = '❌';
                    resultTitle.textContent = 'Error';
                    resultMessage.textContent = data.message || 'Something went wrong. Please try again.';
                }
            } catch (error) {
                // Hide loading, show error
                loadingState.style.display = 'none';
                resultState.style.display = 'block';
                resultState.className = 'result error';
                resultIcon.textContent = '❌';
                resultTitle.textContent = 'Connection Error';
                resultMessage.textContent = 'Unable to process your request. Please check your internet connection and try again.';
            }
        }
    </script>
</body>
</html>
