<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/5/2018
 * Time: 4:58 PM
 */


$webroot = getWebRoot();

$mid = (isset($_GET["mid"]) && $_GET["mid"] != "") ? $_GET["mid"] : "";
$pending = (isset($_GET["pending"]) && $_GET["pending"] != "") ? $_GET["pending"] : 0;
?>

<!DOCTYPE html>
<html>
<head>
	
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8"/>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link rel = "shortcut icon" type = "image/ico"
		  href = "<?PHP echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

	<link href = "css/animate.css" rel = "stylesheet">
	
	
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/default.css"/>

	<link rel = "stylesheet" media = "screen" type = "text/css"
	      href = "<?php echo $webroot; ?>css/company.css"/>
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/font-awesome.min.css">
	<link rel = "stylesheet" href = "<?php echo $webroot; ?>css/magic.min.css">
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jquery_2.1.3_jquery.min.js"></script>
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jquery-ui.min.js"></script>
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jscolor.min.js"></script>
	<link rel = "stylesheet" href = "css/jquery-ui.min.css"/>
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/tables.js"></script>
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/bootstrap-notify.min.js"></script>
	
	
	<title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
</head>
<body style = "background-color:#EAEEF1;">
<div class = "top_sec value_span1">
	<div class = "logo">
		<a href = "<?php echo $webroot ?>">
			<img src = "<?=\LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png"
			     alt = "TRACK YOUR STATS"
			     title = "TRACK YOUR STATS"
			/>
		</a>
	</div>

</div> <!-- top_sec -->

<style>
	
	.white_box {
		
		margin-top: 40px;
	}
	
	.white_box_outer {
		float: none;
		margin: 0 auto;
		max-width: 750px;
	}
	
	.left_con01 {
		width: auto;
		padding: 5px;
		padding-top: 10px;
		padding-left: 17px;
		padding-right: 10px;
		float: none;
		
	}
	
	.heading_holder {
		margin: 0px 0px 10px 0px;
	}
	
	.left_con01 p input {
	
	}
	
	.btn {
		border-radius: 50px;
		width: 150px;
		padding: 10px 20px;
	}
</style>


	   <!--right_panel-->
<div class = "white_box_outer">
	
	<div class = "clear"></div>
	<div class = "white_box value_span8">
		<div class = "com_acc">
			
			<div class = "left_con01">

				<?php
				$company =\LeadMax\TrackYourStats\System\Company::loadFromSession();
				?>

				<?php
					if ($pending) {
				?>
						<div class = "heading_holder">
							<h3 class = " mb-2 value_span9">Your account is still Pending!</h3>
							<p>
								If you have any questions, feel free to contact the Model Ambassador who sent you the signup link!
							</p>
						</div>
				<?php
					} else { ?>

						<div class = "heading_holder">
							<h2 class = " value_span9 mb-3 text-center text-uppercase">Congratulations!</h2>
							<h3>
								Your new account is set up and automatically activated.
							</h3>
							<p class="mb-2">
								Click below to log in, grab your SmartLink from the Offer page inside your model.cash account, post the SmartLink on a new button on your link sharing platform (ex: linktree)
							</p>
							<a href="/login.php" class="btn value_span5-1 value_span2 value_span4">Login Now!</a>
							<p class="mb-0">If you have any questions, feel free to contact the Model Ambassador who sent you the signup link!</p>
						</div>
				<?php
					}
				?>
			
			</div>
		</div>
	</div><!-- white_box -->
</div><!-- white_box_outer -->


<?php include 'footer.php'; ?>


</html>










