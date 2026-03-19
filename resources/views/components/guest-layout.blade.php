<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - WEB CN</title>
    <link rel="icon" type="image/png" href="{{ asset('images/vigilance-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/vigilance-logo.png') }}">
    <style>
        body { background: #FFE5B4; margin: 0; font-family: Arial, sans-serif; }
    </style>
</head>
<body>
    <div style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center;">
        {{ $slot }}
    </div>
</body>
</html> 