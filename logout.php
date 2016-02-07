<?php

//Chore, Copyright Josh Fradley (http://github.com/joshf/Chore)

session_start();

unset($_SESSION["chore_user"]);

if (isset($_COOKIE["chore_user_rememberme"])) {
    setcookie("chore_user_rememberme", "", time()-86400);
}

header("Location: login.php?logged_out=true");

exit;

?>
