<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="refresh" content="0; url=/user/forgot-password" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Redirecting — RechargeHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #040d21;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255,255,255,.5);
      font-size: 14px;
    }
    .wrap { text-align: center; }
    .spinner {
      width: 32px;
      height: 32px;
      border: 3px solid rgba(16,185,129,.2);
      border-top-color: #10b981;
      border-radius: 50%;
      animation: spin .8s linear infinite;
      margin: 0 auto 16px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    a { color: #10b981; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="spinner"></div>
    <p>Redirecting to <a href="/user/forgot-password">forgot password</a> page...</p>
  </div>
  <script>
    window.location.replace('/user/forgot-password');
  </script>
</body>
</html>
