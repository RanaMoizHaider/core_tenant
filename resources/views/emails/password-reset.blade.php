<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 20px;
            margin: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4CAF50;
            font-size: 24px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
        }

        .password {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            color: #333;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #45a049;
        }
        .footer p {
            font-size: 14px;
            color: #999;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <h1>Hello, {{ $name }}!</h1>

        <p>Your password has been successfully reset. Here's your new password:</p>

        <div class="password">
            {{ $password }}
        </div>

        <p>We recommend changing your password after logging in.</p>

        <div class="button-container">
            <a href="{{ env('APP_URL') }}" class="button">Access the System</a>
        </div>
        <br>
        <div class="footer">
          <p>Best regards, {{ config('app.name') }} Team</p>
        </div>
    </div>

</body>

</html>
