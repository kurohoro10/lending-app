<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verify Your Email Address</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, Helvetica, sans-serif;
            color: #222222;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f4;
            padding: 24px 0;
        }
        .inner {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 600;
        }
        .signature {
            margin-top: 24px;
            width: 220px;
            max-width: 100%;
            height: auto;
        }
        @media only screen and (max-width: 620px) {
            .content {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="inner">
            <div class="content">
                <p>Hello!</p>

                <p>Thank you for applying for a loan at ZYA Capital, we are your trusted lender for commercial loans.</p>

                <p>Please click the button below to verify your email address.</p>

                <p>
                    <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
                </p>

                <p>If you did not create an account, no further action is required.</p>

                <p style="margin-top: 32px; margin-bottom: 8px;">Kind regards,</p>

                <p style="margin-top: 8px;">
                    <img src="https://shaded-grating-limpness.ngrok-free.dev/images/email/sample-signature.png"
                         alt="Signature"
                         class="signature">
                </p>

                {!! \App\Helpers\EmailSignature::html() !!}
            </div>
        </div>
    </div>
</body>
</html>
