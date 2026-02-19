<?php

use LeadMax\TrackYourStats\System\Company;

$webroot = getWebRoot();
?>

        <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/ico" href="<?= Company::loadFromSession()->getImgDir() ?>/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/default.css"/>
    <link rel="stylesheet" href="<?php echo $webroot; ?>css/company.css">
    <link href="<?php echo $webroot; ?>css/responsive_table.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $webroot; ?>css/drawer.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $webroot; ?>css/magic.min.css">
    <script type="text/javascript" src="<?php echo $webroot; ?>js/jquery_2.1.3_jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/jscolor.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/main.js"></script>
    <title><?php echo Company::loadFromSession()->getShortHand(); ?></title>
</head>
<body style="background-color:#EAEEF1;">
<div class="top_sec value_span1">
    <div class="logo">
        <a href="<?php echo $webroot ?>">
            <img src="<?= Company::loadFromSession()->getImgDir() ?>/logo.png" alt="<?php echo Company::loadFromSession()->getShortHand(); ?>" title="<?php echo Company::loadFromSession()->getShortHand(); ?>"/>
        </a>
    </div>
</div>
<div style="max-width: 520px; margin: 60px auto; text-align: center;">
    <h2>Set Up Two-Factor Authentication</h2>

    <p style="margin-bottom: 25px;">
        Two-factor authentication is required for your account role.
    </p>

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            {{ $errors->first() }}
        </div>
    @endif

    @if (!$user->two_factor_secret && !$user->two_factor_enabled)
        <form method="POST" action="{{ route('2fa.enroll.start') }}">
            @csrf
            <button class="value_span5-1 value_span2 value_span4" type="submit" style="border: none; border-radius: 4px; padding: 10px 20px; ">
                Generate New Authenticator Secret
            </button>
        </form>
    @endif

    @if ($user->two_factor_secret && !$user->two_factor_enabled)
        <div style="margin: 24px 0;">
            {!! QrCode::size(220)->generate($qrUrl) !!}
        </div>

        <p style="font-size: 14px; color: #666;">
            Scan this QR code with Google Authenticator, Authy, or another TOTP app.
        </p>

        <form method="POST" action="{{ route('2fa.enroll.confirm') }}" style="margin-top: 20px;">
            @csrf
            <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autofocus
                    style="font-size: 20px; padding: 10px; text-align: center; width: 100%;"
                    placeholder="Enter 6-digit code"
            />

            <div style="margin-top: 20px;">
                <button class="value_span5-1 value_span2 value_span4" type="submit" style="border: none; border-radius: 4px; padding: 10px 20px;">
                    Confirm And Continue
                </button>
            </div>
        </form>

        @if (is_array($user->two_factor_recovery_codes) && count($user->two_factor_recovery_codes))
            <div style="margin-top: 26px; text-align: left;">
                <h4>Recovery Codes</h4>
                <p style="font-size: 13px; color: #666;">
                    Save these codes. Each code can be used once.
                </p>
                <ul style="columns: 2; -webkit-columns: 2; -moz-columns: 2; padding-left: 20px;">
                    @foreach ($user->two_factor_recovery_codes as $recoveryCode)
                        <li style="font-family: monospace; margin-bottom: 6px;">{{ $recoveryCode }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>
</body>
</html>

