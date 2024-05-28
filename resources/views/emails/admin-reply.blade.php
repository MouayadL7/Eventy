<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Reply</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
        }
        .email-container {
            background-color: #f7f7f7;
            padding: 30px;
            height: 100%;
        }
        .email-content {
            max-width: 600px;
            background: white;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .email-header {
            background-color: #b37adc7f;
            color: white;
            padding: 20px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .email-body {
            padding: 20px;
            line-height: 1.5;
        }
        .email-footer {
            background-color: #f7f7f7;
            color: rgb(16, 7, 7);
            padding: 20px;
            font-size: 12px;
            text-align: center;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .email-footer a {
            color: #2b97de;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-content">
            <div class="email-header">
                <h1>Report Reply</h1>
            </div>
            <div class="email-body">
                <h2>{{ $reply['title'] }}</h2>
                <p>{{ $reply['body'] }}</p>
            </div>
            <div class="email-footer">
                <p>If you have any questions, please feel free to <a href="mailto:thesarahtlass@gmail.com">contact us</a>.</p>
                <p>© {{ date('Y') }} Eventy. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
