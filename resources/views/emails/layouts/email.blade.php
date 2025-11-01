<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            color: #1a1a1a;
            padding: 40px 20px;
        }
        .email-wrapper {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background-color: #ffffff;
            padding: 40px 40px 20px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #1a1a1a;
            border-radius: 12px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        .logo {
            max-width: 180px;
            height: auto;
            margin: 0 auto 30px;
            display: block;
        }
        .email-body {
            padding: 0 40px 40px;
            background-color: #ffffff;
        }
        .email-footer {
            background-color: #f8f8f8;
            padding: 30px 40px;
            text-align: center;
            border-radius: 0 0 12px 12px;
            border-top: 1px solid #e5e5e5;
        }
        .footer-text {
            font-size: 14px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        .footer-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            .email-body {
                padding: 0 30px 30px;
            }
            .email-header {
                padding: 30px 30px 20px;
            }
            .email-footer {
                padding: 25px 30px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            @hasSection('icon')
                <div class="icon-container">
                    @yield('icon')
                </div>
            @else
                <img src="{{ config('app.logo') }}" alt="{{ config('app.name') }}" class="logo">
            @endif
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                Estás recibiendo este correo porque tienes una cuenta en {{ config('app.name') }}.
                Si no estás seguro de por qué recibiste este correo, contáctanos respondiendo a este mensaje.
            </div>
            <div style="margin-top: 20px;">
                <div class="footer-name">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
