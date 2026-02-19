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
    <div style="max-width: 420px; margin: 60px auto; text-align: center;">

        <h2>Two-Factor Authentication</h2>

        <p>
            Enter the 6-digit code from your authenticator app.
        </p>

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.challenge.verify') }}">
            @csrf

            <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    autofocus
                    style="font-size: 20px; padding: 10px; text-align: center; width: 100%;"
            />

            <div style="margin-top: 20px;">
                <button class="value_span5-1 value_span2 value_span4" type="submit" style="border: none; border-radius: 4px; padding: 10px 20px;">
                    Verify
                </button>
            </div>
        </form>

        <div style="margin-top: 25px; font-size: 14px; color: #666;">
            Lost your device? You may enter one of your recovery codes instead.
        </div>

    </div>

</body>
</html>