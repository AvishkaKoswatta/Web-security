<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Add Google reCAPTCHA script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 1rem;
        }

        form div {
            margin-bottom: 1rem;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            max-width: 430px;
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-top: 4px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }

        .error-message {
            color: red;
            font-size: 0.875rem;
        }

        .password-info {
            display: flex;
            align-items: center;
            margin-top: 4px;
            font-size: 0.875rem;
            color: #666;
        }

        .password-info i {
            margin-right: 6px;
            color: #007bff;
            cursor: pointer;
        }

        .password-tooltip {
            display: none;
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 8px;
            font-size: 0.875rem;
            border-radius: 4px;
            width: 200px;
            top: 32px;
            left: -40px;
        }

        .password-info:hover .password-tooltip {
            display: block;
        }

        .g-recaptcha {
            margin-bottom: 1rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-info">
                    <i class="fas fa-info-circle"></i> 
                    <span>Password should contain at least 8 characters, 1 uppercase letter, 1 number, and 1 special character.
                    Example: P@ssw0rd123
                    </span>
                    <!-- <div class="password-tooltip"></div> -->
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <!-- Google reCAPTCHA widget -->
            <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}"></div>
            @error('g-recaptcha-response')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
