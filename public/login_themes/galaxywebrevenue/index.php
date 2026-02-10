<?php

$domain = $_SERVER["HTTP_HOST"];


$webroot = getWebRoot();


$user = new \LeadMax\TrackYourStats\User\User;
$company =  \LeadMax\TrackYourStats\System\Company::loadFromSession();
$company->reloadSettings();

//checks if the User is already logged in
if ($user->is_loggedin())
{
	if ($user->verify_login_session())
	{
		send_to('dashboard');
	}
}


$user->checkLoginAttempts();


//POST to login.php (self),
//if count is < 5, continue, else, too many attempts for today
if (isset($_POST['button']) && $user->count < 5)
{
	$user_name = $_POST['txt_uname_email'];
	$email     = $_POST['txt_uname_email'];
	$password  = $_POST['txt_password'];

	$result = $user->login($user_name, $email, $password);

	if ($result == \LeadMax\TrackYourStats\User\Login::RESULT_SUCCESS) {

		if (isset($_GET["redirectUri"]))
		{
			send_to(urldecode($_GET["redirectUri"]));
		}
		else
		{
			send_to('dashboard');
		}

	} else if  ($result == \LeadMax\TrackYourStats\User\Login::RESULT_PENDING){
		send_to('signup_success.php?pending=1');
	} else {

		$user->badLoginAttempt();

		if ($result == \LeadMax\TrackYourStats\User\Login::RESULT_INVALID_CRED)
		{
			$error = "Wrong Details ! <p>You have {$user->count} / 5 login attempts remaining. </p>";
		}
		else
		{
			if ($result == \LeadMax\TrackYourStats\User\Login::RESULT_BANNED)
			{
				$error = "This account has been banned. Login attempt has been logged and an administrator will be notified. ";
			}
		}
	}
}
else
{
	if (isset($_POST["button"]))
	{

		$error = "You have {$user->count} / 5 login attempts remaining. Please wait until tomorrow or contact an admin to reset your login attempt and/or password.";

	}
}


?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="/login_themes/<?=$company->login_theme?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/login_themes/<?=$company->login_theme?>/css/style.css?v=1.2">
		<script type="text/javascript" src="/login_themes/<?=$company->login_theme?>/js/jquery-3.3.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="/login_themes/<?=$company->login_theme?>/js/bootstrap/bootstrap.min.js"></script>
        <title> <?= $company->shortHand?> </title>
	</head>
	<body>
		<div class="row h-100">
			<div class="col-12 page_wrapper text-center d-flex align-content-center justify-content-center">
				<div class="row my-auto">
					<div class="col-11 col-md-4 mx-auto">
						<div class="row">
							<div class="col-12 mx-auto mb-4 p-0">
								<img src="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt="">
							</div>
						</div>
						<div class="row">
							<div class="col-12 mx-auto login_box p-4 text-left">
								<h3 class="mb-4">Sign In</h3>
								<form method="post">
									<div class="form-group">
					<?php
					if (isset($error))
					{
						?>
						<div class = "alert alert-danger" style = " padding-bottom:5px;">
							<i class = "glyphicon glyphicon-warning-sign"></i> &nbsp;<span
									style = "color:red;"><?php echo $error; ?></span>
						</div>
						<?php
					}
					?>
										<label for="login">Username or e-mail address</label>
										<input name="txt_uname_email" type="text" class="form-control" id="txt_uname_email" value="<?php echo $user->autoFillEmail; ?>">
									</div>
									<div class="form-group">
										<label for="password">Password</label>
										<input type="password" class="form-control" id="password" name="txt_password">
									</div>
									<button type="submit" class="btn btn-primary d-block text-center w-100">Sign In</button>
									<!--<a href="aff_help.php">Forgot Password?</a>-->
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
