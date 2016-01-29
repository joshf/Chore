<?php

//Chore, Copyright Josh Fradley (http://github.com/joshf/Chore

require_once("../assets/version.php");

//Check if Chore has been installed
if (!file_exists("../config.php")) {
    die("Information: Chore has already not been installed! Please run the installer.");
}

require_once("../config.php");

$installedversion = trim(file_get_contents("../.version"));
$comparison = version_compare($version, $installedversion);

if (isset($_GET["start"])) {
    if ($comparison == "1") {
        $state = "upgrade";
    } elseif ($comparison == "0") {
        $state = "noupgrade";
    }
    if (isset($_GET["force"])) {
        $state = "upgrade";
    }
} else {
    $state = "welcome";   
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="../assets/favicon.ico">
<title>Chore &raquo; Upgrade</title>
<link rel="apple-touch-icon" href="../assets/icon.png">
<link rel="stylesheet" href="../assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="../assets/css/chore.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
<div class="container-fluid">
<div class="navbar-header">
<a class="navbar-brand" href="index.php">Chore</a>
</div>
</div>
</nav>
<div class="container-fluid top-pad">
<?php

if ($state == "welcome") {
    
?>
<div class="alert alert-info">
<h4 class="alert-heading">Upgrade Available</h4>
<p>Your version of chore need an upgrade. To start the upgrade click "Start Upgrade"<p><a href="?start" class="btn btn-info"><span class="glyphicon glyphicon-transfer" title="Start Upgrade" aria-hidden="true"></span> Start Upgrade</a></p>
<?php

} elseif ($state == "upgrade") {
    
    //Connect to database
    @$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno()) {
        die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
    }
    
    //mysqli_query($con, "");
    mysqli_close($con);
    
    //Write Version
    $installversion = fopen("../.version", "w");
    fwrite($installversion, $version);
    fclose($installversion);
    
?>
<div class="alert alert-success">
<h4 class="alert-heading">Upgrade Complete</h4>
<p>Chore has been successfully upgraded to version <?php echo $version; ?>.<p><a href="../login.php" class="btn btn-success"><span class="glyphicon glyphicon-log-in" title="Login" aria-hidden="true"></span> Go To Login</a></p>
<?
} elseif ($state == "noupgrade") {
?>
<div class="alert alert-info">
<h4 class="alert-heading">No upgrade required</h4>
<p>Chore is already up to date and does not require an upgrade<p><a href="../login.php" class="btn btn-info"><span class="glyphicon glyphicon-log-in" title="Login" aria-hidden="true"></span> Go To Login</a></p>
<?php
} else {    
?>
<div class="alert alert-danger">
<h4 class="alert-heading">Upgrade Error</h4>
<p>Upgrade error <small>(Installed: <?php echo $installedversion; ?>, packaged <?php echo $version; ?>)</small><p>
<a href="../login.php" class="btn btn-danger">Go To Login</a></p>
<?php
}
?>
</div>
</div>
<script src="../assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="../assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>