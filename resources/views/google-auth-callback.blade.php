<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación Google Drive</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .success .icon {
            background: #10b981;
            color: white;
        }
        .error .icon {
            background: #ef4444;
            color: white;
        }
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 24px;
        }
        p {
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
        }
        .close-btn {
            margin-top: 30px;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .close-btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container {{ $success ? 'success' : 'error' }}">
        <div class="icon">
            @if($success)
                ✓
            @else
                ✕
            @endif
        </div>
        <h1>
            @if($success)
                ¡Autenticación Exitosa!
            @else
                Error de Autenticación
            @endif
        </h1>
        <p>{{ $message }}</p>
        @if(isset($error))
            <div class="error-message">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif
        <button class="close-btn" onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>

