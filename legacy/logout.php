<?php

use LeadMax\TrackYourStats\User\User;

if(isset($_GET["adminLogin"], $_SESSION["adminLogin"], $_SESSION["repid"]))
{
    unset($_SESSION["adminLogin"]);
?>
<script type="text/javascript">
    window.close();
    </script>

<?php
exit;
}


$user_logout = new User();

$user_logout->logout();
send_to('login');