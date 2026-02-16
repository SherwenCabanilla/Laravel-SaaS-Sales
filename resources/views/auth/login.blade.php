<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <!-- Left Info Panel -->
        <div class="info-panel">
            <img src="{{ asset('images/logo3.png') }}" alt="Funnel System Logo" class="info-logo">
            <h1>Grow Your Sales Efficiently</h1>
            <p class="info-subtitle">Manage leads, automate marketing campaigns, and track performance in one platform.</p>

            <ul class="features">
                <li><strong>Lead Management:</strong> Capture and organize all your leads seamlessly</li>
                <li><strong>Marketing Automation:</strong> Automate email sequences and customer follow-ups</li>
                <li><strong>Analytics Dashboard:</strong> Monitor campaign performance and conversion rates</li>
            </ul>
        </div>

        <!-- Right Login Panel -->
        <div class="login-card">
            <img src="{{ asset('images/logo2.png') }}" alt="Funnel System Logo" class="login-logo">

            <h1>Login to Funnel System</h1>
            <p class="subtitle">Access your sales and marketing dashboard</p>

            @if(session('error'))
                <div class="error-message">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Email" required>

                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit">Login</button>
            </form>

            <p class="register-link">
                Don't have an account? Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
