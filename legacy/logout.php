<?php
if(isset($_GET["adminLogin"]))
{
    unset($_SESSION["adminLogin"]);
?>
<script type="text/javascript">
    window.close();
    </script>

<?php
exit;
}


$user_logout = new \LeadMax\TrackYourStats\User\User();

$user_logout->logout();
send_to('login.php');