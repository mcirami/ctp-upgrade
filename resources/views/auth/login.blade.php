<?php
$webroot = getWebRoot();
?>

        <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/ico" href="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/default.css"/>
    <link rel="stylesheet" href="<?php echo $webroot; ?>css/company.css">
    <link href="<?php echo $webroot; ?>css/responsive_table.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $webroot; ?>css/drawer.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $webroot; ?>css/magic.min.css">
    <script type="text/javascript" src="<?php echo $webroot; ?>js/jquery_2.1.3_jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/jscolor.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/main.js"></script>
    <title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
    <style>
        body {
            background: url('<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/main-bg.jpg') no-repeat;
            background-size: cover;
        }
        .white_box {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            margin-top: 20px;
        }

        .white_box_outer {
            float: none;
            margin: 0 auto;
            max-width: 400px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -ms-flex-wrap: nowrap;
            flex-wrap: nowrap;
            justify-content: center;
            -webkit-justify-content: center;
            -webkit-align-items: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .logo img {
            width: 100%;
            border-radius: 4px;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
        }

        .btn_yellow {
            width: 100%;
        }
        .btn_yellow input[type="submit"] {
            width: 100%;
            padding: 15px 25px;
        }

        @media screen and (max-width: 768px) {
            .white_box_outer {
                max-width: none;
                float: left;
                width: 100%;
            }
        }

        .left_con01 {
            width: auto;
            padding: 10px 10px 5px 17px;
            float: none;
        }

        .heading_holder {
            margin: 0px 0px 10px 0px;
        }

    </style>
</head>
<body>
<div class="white_box_outer">
    <div class="logo">
        <a href="<?php echo $webroot ?>">
            <img src="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>" title="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>"/>
        </a>
    </div>
    <div class="white_box value_span8">
        <div class="com_acc">
            <form method="post" class="login_form">
				<?php echo csrf_field(); ?>
                @if(request()->has('redirectUri'))
                    <input type="hidden" name="redirectUri" value="{{ request('redirectUri') }}" />
                @endif
                <div class="left_con01">
                    <div class="heading_holder">
                        <span class="lft value_span9">{{ env('LOGIN_PAGE_TEXT') }}</span>
                    </div>
                    <br/>
                    @if(isset($error))
                        <div class="alert alert-danger" style=" padding-bottom:5px;">
                            <i class="glyphicon glyphicon-warning-sign"></i> &nbsp;<span style="color:red;">{{ $error }}</span>
                        </div>
                    @endif
                    <p>
                        <input type="text" name="txt_uname_email" placeholder="Enter Username" value="{{ $user->autoFillEmail }}" required/>
                    </p>
                    <p>
                        <input type="password" name="txt_password" placeholder="Enter Password" required/>
                    </p>
                    <span class="btn_yellow btn_wrap">
                        <input type="submit" name="button" class="value_span5-1 value_span2 value_span4" value="{{ env('LOGIN_PAGE_BUTTON_TEXT') }}"/>
                    </span>
                    <br/>
                </div>
            </form>
        </div>
    </div>
</div>
{{--<script type="text/javascript" src="js/dropdown.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/bootstrap-tooltip.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/widget-staticRow.min.js"></script>
<script type="text/javascript" src="js/moment-timezone-with-data.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="js/drawer.min.js" charset="utf-8"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('.drawer').drawer();
		$('[data-toggle="popover"]').popover();
	});
</script>--}}
</body>
</html>