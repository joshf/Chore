<?php

//Chore, Copyright Josh Fradley (http://github.com/joshf/Chore)

require_once("assets/version.php");

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["chore_user"])) {
    header("Location: login.php");
    exit;
} 

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$getusersettings = mysqli_query($con, "SELECT `user`, `password`, `email`, `salt`, `api_key` FROM `users` WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (!empty($_POST)) {
    //Get new settings from POST
    $user = $_POST["user"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $salt = $resultgetusersettings["salt"];
    if ($password != $resultgetusersettings["password"]) {
        //Salt and hash passwords
        $randsalt = md5(uniqid(rand(), true));
        $salt = substr($randsalt, 0, 3);
        $hashedpassword = hash("sha256", $password);
        $password = hash("sha256", $salt . $hashedpassword);
    }

    //Update Settings
    mysqli_query($con, "UPDATE `users` SET `user` = \"$user\", `password` = \"$password\", `email` = \"$email\", `salt` = \"$salt\" WHERE `user` = \"" . $resultgetusersettings["user"] . "\"");
    
    //Show updated values
    header("Location: settings.php");
    
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Chore &raquo; Settings</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/chore.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<h1>Settings</h1>
<ol class="breadcrumb">
<li><a href="index.php">Chore</a></li>
<li class="active">Settings</li>
<li class="pull-right"><span id="add" title="Add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> <span id="settings" title="Settings" class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span id="logout" title="Logout" class="glyphicon glyphicon-log-out" aria-hidden="true"></span></li>
</ol>
<form id="settingsform" method="post" autocomplete="off">
<div class="form-group">
<label class="control-label" for="user">User</label>
<input type="text" class="form-control" id="user" name="user" value="<?php echo $resultgetusersettings["user"]; ?>" placeholder="Enter a username..." required>
</div>
<div class="form-group">
<label class="control-label" for="email">Email</label>
<input type="email" class="form-control" id="email" name="email" value="<?php echo $resultgetusersettings["email"]; ?>" placeholder="Type an email..." required>
</div>
<div class="form-group">
<label class="control-label" for="password">Password</label>
<input type="password" class="form-control" id="password" name="password" value="<?php echo $resultgetusersettings["password"]; ?>" placeholder="Enter a password..." required>
</div>
<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save" title="Update" aria-hidden="true"></span> Update</button>
</form>
<hr>
<h2>API Key</h2>
<p>Your API key is: <b><span id="api_key"><?php echo $resultgetusersettings["api_key"]; ?></span></b></p>
<button id="generateapikey" class="btn btn-default"><span class="glyphicon glyphicon-lock" title="Generate New Key" aria-hidden="true"></span> Generate New Key</button>
<hr>
<h2>Version</h2>
<span id="update"><p>You have Chore version <?php echo $version; ?></p></span>
<button id="checkforupdates" class="btn btn-default"><span class="glyphicon glyphicon-refresh" title="Check For Update" aria-hidden="true"></span> Check For Update</button>
<hr>
<h2>Categories</h2>
<ul id="categories" class="list-group">
<?php

$getcategories = mysqli_query($con, "SELECT `id`, `category` FROM `categories`");

while($category = mysqli_fetch_assoc($getcategories)) {
    echo "<li class=\"list-group-item\">" . $category["category"] . " <span class=\"pull-right delete glyphicon glyphicon-remove\" data-id=\"" . $category["id"] . "\"></span></li>";
}

mysqli_close($con);
    
?>
</ul>
<div class="form-group">
<label class="control-label" for="newcategory">Category</label>
<input type="text" class="form-control" id="new_category" name="new_category" placeholder="Type a new category..." required>
</div>
<button id="add_category" class="btn btn-default"><span class="glyphicon glyphicon-plus" title="Add Category" aria-hidden="true"></span> Add Category</button>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-validator/dist/validator.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() {
    var chore_version = "<?php echo $version; ?>";
    if (Cookies.get("chore_settings_updated")) {
        $.notify({
            message: "Settings updated!",
            icon: "glyphicon glyphicon-ok",
        },{
            type: "success",
            allow_dismiss: true
        });
        Cookies.remove("chore_settings_updated");
    }
    $("#settingsform").validator({
        disable: true
    });
    $("form").submit(function() {
        Cookies.set("chore_settings_updated", "1", { expires: 7 });
    });
    $("#generateapikey").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=generateapikey",
            error: function() {
                $("#api_key").html("Could not generate key. Failed to connect to worker.</b>");
            },
            success: function(api_key) {
                $("#api_key").html(api_key);
            }
        });
    });
    $("#checkforupdates").click(function() {
        $.getJSON("https://api.github.com/repos/joshf/Chore/releases").done(function(resp) {
            var data = resp[0];
            var chore_remote_version = data.tag_name;
            var url = data.zipball_url;
            if (chore_version < chore_remote_version) {
                $("#update").html("<p><a href=\"https://github.com/joshf/Chore/compare/" + chore_version + "..." + chore_remote_version + "\" target=\"_blank\">Version " + chore_remote_version + "</a> is now available, click <a href=\"https://github.com/joshf/Chore/wiki/Updating-Chore\" target=\"_blank\">here</a> to for instructions on how to update.</p>")
            } else {
                $("#update").html("<p>No update available</p>")
            }
        });
    });
    $("li").on("click", ".delete", function() {
        var id = $(this).data("id");
        var element = $(this).parent();
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=deletecategory&id="+ id +"",
                error: function() {
                    $.notify({
                        message: "Ajax query failed!",
                        icon: "glyphicon glyphicon-warning-sign",
                    },{
                        type: "danger",
                        allow_dismiss: true
                    });
                },
                success: function() {
                    $.notify({
                        message: "Category deleted!",
                        icon: "glyphicon glyphicon-ok",
                    },{
                        type: "success",
                        allow_dismiss: true
                    });
                    $(element).remove();
                }
        });
    }); 
    $("#add_category").click(function() {
        var new_category = $("#new_category").val();
        if (new_category !== null && new_category != "") {                                                         
            $.ajax({
                type: "POST",
                url: "worker.php",
                data: "action=add_category&new_category=" + new_category + "",
                error: function() {
                    $.notify({
                        message: "Ajax Error!",
                        icon: "glyphicon glyphicon-exclamation-sign",
                    },{
                        type: "danger",
                        allow_dismiss: true
                    });
                },
                success: function() {
                    $.notify({
                        message: "Category added!",
                        icon: "glyphicon glyphicon-ok",
                    },{
                        type: "success",
                        allow_dismiss: true
                    });
                    $("#newcategory").val("");
                    $("#categories").append("<li class=\"list-group-item\">" + new_category + "</li>")
                }
            });               
        }   
    });
    $("#add").click(function() {
    	window.location.href = "add.php";
    });
    $("#settings").click(function() {
    	window.location.href = "settings.php";
    });
    $("#logout").click(function() {
    	window.location.href = "logout.php";
    });
});
</script>
</body>
</html>