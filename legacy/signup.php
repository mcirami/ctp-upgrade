<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/11/2018
 * Time: 3:34 PM
 */


$webroot = getWebRoot();

$mid = (isset($_GET["mid"]) && $_GET["mid"] != "") ? $_GET["mid"] : "";

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
	
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8"/>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link rel = "shortcut icon" type = "image/ico"
		  href = "<?PHP echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/default.css?v=1.3"/>
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/external-header.css?v=1"/>
	<link rel = "stylesheet" media = "screen" type = "text/css"
		  href = "<?php echo $webroot; ?>css/company.css"/>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
	<script type="text/javascript" src="<?php echo $webroot; ?>js/external-header.js?v=1"></script>

	<title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
</head>
<body class="signup">
<header class = "external">
	<div class="container">
		<div class="row_wrap w-100">
			<div class="nav_wrap external_nav">
				<nav class="navbar navbar-expand-lg">
					<div class="container-fluid">
						<a class="navbar-brand" href="/">
							<img src="/resources/landers/modelcash/images/logo.png" alt="">
						</a>
						<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
							<span class="navbar-toggler-icon"></span>
						</button>
						<div class="collapse navbar-collapse" id="navbarNav">
							<ul class="navbar-nav">
								<li class="nav-item">
									<a class="nav-link" aria-current="page" href="/#about">About Us</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="/#our_benefits">How It Works</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="/#tabs">Passive Income</a>
								</li>
								<li class="nav-item">
									<a href="/contact" class="nav-link">Contact</a>
								</li>
							</ul>
							<div class="buttons_wrap">
								<a class="button white" href="/login.php">Login</a>
								<a class="button value_span11 value_span2 value_span4" href="/signup.php?mid=1003">Sign Up</a>
							</div>
						</div>
					</div>
				</nav>
			</div>
		</div>
	</div>

</header> <!-- top_sec -->

<style>
	
	.white_box_outer {
		margin: 0 auto;
		width: 100%;
		display: block;
		background: #ffffff;
	}

	.content_wrap {
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
		align-items: center;
        padding: 60px 0;
    }
	
	.heading_holder {
		max-width: 750px;
		margin: 0px 0px 10px 0px;
	}

    label {
	    text-transform: uppercase;
        font-family: 'robotobold', sans-serif;
	    font-size: .8rem !important;
    }

</style>


	   <!--right_panel-->
<div class = "white_box_outer header_padding">
	<div class="container">
		<div class="content_wrap">
			<div class="heading_holder">
				<h3 class="value_span9 text-center mb-4">Sign Up to Earn Cash!</h3>
			</div>
			<div class="two_columns external">
				<div class="column">

					<form action = "/" id = "signUpForm">
							<input name="mid" type="hidden" value="<?php echo $mid; ?>">

							<div class="form-group">
								<label class = "value_span9" for = "tys_first_name">First Name: <sup>*</sup></label>
								<input class = "form-control" type = "text" name = "tys_first_name">
							</div>

							<div class="form-group">

								<label for = "tys_last_name">Last Name: <sup>*</sup></label>
								<input type = "text" name = "tys_last_name">
							</div>


							<div class="form-group">

								<label for = "tys_email">Email: <sup>*</sup></label>
								<input type = "text" name = "tys_email">
							</div>


							<div class="form-group">

								<!-- MUST BE GREATER THAN FOUR CHARACTERS -->
								<label for = "tys_username">Username: <sup>*</sup></label>
								<input type = "text" name = "tys_username">
								<small>(MUST BE GREATER THAN 4 CHARACTERS)</small>
							</div>

							<div class="form-group">

								<!-- MUST BE GREATER THAN 6 CHARACTERS -->
								<label for = "tys_password">Password: <sup>*</sup></label>
								<input type = "password" name = "tys_password">
								<small>(MUST BE GREATER THAN 6 CHARACTERS)</small>
							</div>


							<div class="form-group">

								<label for = "tys_confirm_password">Confirm Password: <sup>*</sup></label>
								<input type = "password" name = "tys_confirm_password">
							</div>


							<div class="form-group">

								<label for="im_type">Instant Messenger:</label>
								<select id="im_type" name="im_type" required>
									<option value="skype">Skype</option>
									<option value="telegram">Telegram</option>
									<option value="instagram">Instagram</option>
									<option value="facebook">Facebook</option>
								</select>
							</div>


							<div class="form-group">

								<label for="im_username">IM Username:</label>
								<input type="text" name="im_username">

							</div>


							<span class = "btn_yellow" style = "color:#1D4C9E;">
								<input type = "submit" name = "button" class="rounded-5 value_span5-1 value_span2 value_span4" value = "Sign Up"/>
							</span>


					</form>
				</div><!-- column -->
				<div class="column">
					<h3>One Link. Unlimited Cash.</h3>
					<div class="image_wrap">
						<img src="<?php echo $webroot; ?>images/mc-icon-circle.png" alt="">
					</div>
				</div>
			</div><!-- two_columns -->
		</div>
	</div><!-- white_box_outer -->
	<footer class="full_width external">
		<div class="container">
			<div class="footer_content">
				<div class="logo_wrap">
					<a href="/">
						<img class="w-100 h-auto" src="/resources/landers/modelcash/images/logo.png" alt="">
					</a>
				</div>
				<ul>
					<li>
						<a href="/#home">About Us</a>
					</li>
					<li>
						<a href="/#our_benefits">How It Works</a>
					</li>
					<li>
						<a href="/#tabs">Passive Income</a>
					</li>
					<li>
						<a href="/contact">Contact</a>
					</li>
				</ul>
				<div class="buttons_wrap">
					<a class="button white mr-4" href="/login.php">Login</a>
					<a class="button value_span11 value_span2 value_span4" href="/signup.php?mid=1003">Sign Up</a>
				</div>
			</div>
			<p class="copy">&copy; model.cash | All rights reserved.</p>
		</div>
	</footer>
	<script type = "text/javascript">
		
		function notify(message, type) {
			
			$.notify({
					
					message: message,
					
				}, {
					placement: {
						from: 'top',
						align: 'center',
					},
					type: type,
					animate: {
						enter: 'animated fadeInDown',
						exit: 'animated fadeOutUp',
					},
				},
			);
		}
		
		
		function handleResponse(responseCode) {
			
			responseCode = responseCode.replace(/\s/g, '');
			
			switch (responseCode) {
				case 'SUCCESS'    :
					let mid = '<?php echo $mid; ?>';
					mid = mid === '' ? '' : '?mid=true';

					window.location = 'signup_success.php' + mid;
					break;
				
				case 'USERNAME_OR_EMAIL_EXISTS' :
					notify('The username or email you entered already exists in the system.', 'warning');
					break;
				
				case 'INVALID_EMAIL':
					notify('The email you entered is invalid.', 'warning');
					break;
				
				case 'INVALID_USERNAME':
					notify('The username you entered is invalid, please make sure it is at least 4 characters long, and contains no special characters.', 'warning');
					break;
				
				case 'PASSWORD_MISMATCH':
					notify('Password do not match.', 'warning');
					break;
				
				case 'MISSING_OR_INVALID_FIELDS':
					notify('You have missing fields or they are invalid, please double check them', 'warning');
					break;
				
				default :
					notify('Unknown error. Please contact an administrator is this persists.', 'danger');
					break;
			}
			
		}
		
		
		$('#signUpForm').on('submit', function (event) {
			
			// stops form from submitting
			event.preventDefault();
			
			let postData = $('#signUpForm').serialize();

			$.ajax({
				type: 'post',
				url: 'scripts/affiliate_signup.php',
				data: postData,
				success: function (responseData, textStatus, jqXHR) {
					handleResponse(responseData);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(errorThrown);
				},
			});
		});
	
	</script>
	
	
	<?php include 'footer.php'; ?>


</html>











