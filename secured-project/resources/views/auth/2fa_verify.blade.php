<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification</title>
</head>
<body>
    <h1>Two-Factor Authentication</h1>
    <form action="{{ route('2fa.verify') }}" method="POST">
        @csrf
        <label for="two_fa_answer">Answer your security question:</label>
        <input type="text" id="two_fa_answer" name="two_fa_answer" required>
        @error('two_fa_answer')
            <div style="color: red;">{{ $message }}</div>
        @enderror
        <button type="submit">Verify</button>
    </form>
</body>
</html>
