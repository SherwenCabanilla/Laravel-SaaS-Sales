<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/extracted/auth-setup-password-style1.css') }}">
</head>
<body>
    <div class="wrap">
        <h1>Set Your Password</h1>
        <p>Account: <strong>{{ $email ?? '-' }}</strong></p>

        @if(session('error'))
            <div class="msg error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="msg error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('setup.complete', ['token' => $token]) }}">
            @csrf
            <label for="password">New Password</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <button type="button" class="toggle-eye" data-target="password" aria-label="Show password" title="Show password">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>

            <label for="password_confirmation">Confirm Password</label>
            <div class="password-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                <button type="button" class="toggle-eye" data-target="password_confirmation" aria-label="Show confirmation password" title="Show confirmation password">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>

            <p class="hint">Use 12 to 64 characters with uppercase, lowercase, number, and special character.</p>
            <button type="submit">Set Password and Activate</button>
        </form>
    </div>
    <script>
        document.querySelectorAll('.toggle-eye').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.target);
                var icon = button.querySelector('i');
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                if (icon) {
                    icon.classList.toggle('fa-eye', !show);
                    icon.classList.toggle('fa-eye-slash', show);
                }
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.setAttribute('title', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>

