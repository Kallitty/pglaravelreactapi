<!DOCTYPE html>
<html>
<head>
    <title>Welcome to PG Regal!</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        /* Base styles */
        body {
            font-family: 'Arial', sans-serif;
            color: #e1062c;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #e1062c;
            margin-bottom: 30px;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
        }
        
        .content {
            padding: 0 20px 20px;
        }
        
        h1 {
            color: #c00526;
            font-size: 28px;
            margin-bottom: 25px;
        }
        
        p {
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .highlight {
            color: #e1066c;
            font-weight: bold;
        }
        
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #e1062c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 25px 0;
        }
        
        .button:hover {
            background-color: #1d456e;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #eeeeee;
            margin-top: 30px;
        }
        
        ul {
            text-align: left;
            margin-left: 20px;
            margin-bottom: 25px;
            padding-left: 20px;
        }
        
        li {
            margin-bottom: 10px;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                border-radius: 0;
            }
            
            .content {
                padding: 0 10px 10px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="{{ asset('images/pgregal-logo.png') }}" alt="Pgregal Logo" class="logo">
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <h1>Welcome to PG Regal, <span class="highlight">{{ $user->name }}</span>!</h1>
            
            <p>Shop with excitement as you join our Market. Your account is now active and ready to help you achieve your shopping goals.</p>
            
            <p>Here is what you can do next:</p>
            
            <ul>
                <li>Order more items</li>
                <li>Send us a mail about your preferences</li>
                <li>Explore our premium features</li>
                <li>Set up your notification preferences</li>
            </ul>
            
            <a href="{{ url('/') }}" class="button">Access PG Regal Pro Audio</a>
            
            <p>If you have any questions about our platform or need assistance, our support team is available to help.</p>
            
            <p>We look forward to seeing all that you will accomplish with Pg Regal!</p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Warm regards,<br><strong>The PG Regal Team</strong></p>
            <p style="margin-top: 10px;">
                <small>
                    © {{ date('Y') }} PG Regal. All rights reserved.<br>
                    <a href="{{ url('/privacy') }}" style="color:  #721c24;">Privacy Policy</a> | 
                    <a href="{{ url('/terms') }}" style="color:  #721c24;">Terms of Service</a>
                </small>
            </p>
        </div>
    </div>
</body>
</html>