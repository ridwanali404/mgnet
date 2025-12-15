<!DOCTYPE html>
<html>

<head>
    <title>Welcome to MG Network</title>
</head>

<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Thank you for registering with MG Network.</p>
    <p>Here are your login credentials:</p>
    <ul>
        <li><strong>Username:</strong> {{ $user->username }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    <p>Please login and change your password immediately.</p>
    <p>Login URL: <a href="{{ url('/login') }}">{{ url('/login') }}</a></p>
</body>

</html>