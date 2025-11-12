<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .content {
            text-align: center;
            margin: 30px 0;
        }
        .content p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }
        .otp-code {
            background-color: #f0f0f0;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #007bff;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #eee;
            margin-top: 30px;
            padding-top: 20px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OMPAY</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $user->first_name }},</p>
            
            <p>Vous avez demandé un code de vérification pour compléter votre enregistrement OMPAY.</p>
            
            <p>Voici votre code OTP :</p>
            
            <div class="otp-code">{{ $otp_code }}</div>
            
            <p>Veuillez entrer ce code dans l'application pour vérifier votre compte.</p>
            
            <div class="expiry">
                ⏱️ Ce code expire dans <strong>10 minutes</strong>
            </div>
            
            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Si vous n'avez pas demandé ce code, ignorez cet email.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} OMPAY. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
