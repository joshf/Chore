<?php

//Chore, Copyright Josh Fradley (http://github.com/joshf/Chore)

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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Chore &raquo; Add</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/chore.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<h1>Chore</h1>
<ol class="breadcrumb">
<li><a href="index.php">Chore</a></li>
<li class="active">Add</li>
<li class="pull-right"><span id="add" title="Add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> <span id="settings" title="Settings" class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span id="logout" title="Logout" class="glyphicon glyphicon-log-out" aria-hidden="true"></span></li>
</ol>
<form id="addform" autocomplete="off">
<div class="form-group">
<label class="control-label" for="item">Item</label>
<input type="text" class="form-control" id="item" name="item" placeholder="Type a item..." required>
</div>
<div class="form-group">
<label class="control-label" for="details">Details</label>
<textarea class="form-control" id="details" name="details" placeholder="Type any extra details..."></textarea>
</div>
<div class="hidden" id="newcategory_holder">
<div class="form-group">
<label class="control-label" for="newcategory">Category</label>
<input type="text" class="form-control" id="newcategory" name="newcategory" placeholder="Type a new category..." required>
</div>
</div>
<div id="category_holder">
<div class="form-group">
<label class="control-label" for="category">Category</label>
<div class="input-group">
<select class="form-control" id="category" name="category">
<?php

//Don"t duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `items` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"\">None</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `items` WHERE `category` != \"\"");

while($task = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"" . $task["category"] . "\">" . ucfirst($task["category"]) . "</option>";
}

?>
</select>
<span id="addcategory" class="input-group-addon">
<i class="glyphicon glyphicon-plus"></i>
</span>
</div>
</div>
</div>
<div class="checkbox">    
<label>
<input type="checkbox" id="has_due" name="has_due"> <span id="has_due_message">Due date required</span>
</label>
</div>
<div class="form-group">
<input type="date" class="due form-control" id="due" name="due" disabled>
</div>
<div class="checkbox">
<label>
<input type="checkbox" id="highpriority" name="highpriority"> High Priority
</label>
</div>
<input type="hidden" id="action" name="action" value="add">
<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" title="Add" aria-hidden="true"></span> Add</button>
</form>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/modernizr-load/modernizr.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-validator/dist/validator.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function() {
    $("#has_due").click(function() {
        if ($(this).is(":checked")) {
            $("#due").prop("disabled", false);
            $("#due").prop("required", true);            
        } else {
            $("#due").prop("disabled", true);
            $("#due").prop("required", false);
        }
    });
    $("#newcategory").on("keydown", function(e) {
        if (e.which == 13) {
            var newcategory = $("#newcategory").val();
            if (newcategory !== null && newcategory != "") {                                             
                $("#category").append("<option value=\"" + newcategory + "\" selected=\"selected\">" + newcategory + "</option>");
                $("#newcategory_holder").addClass("hidden");
                $("#category_holder").removeClass("hidden");                
            }
            event.preventDefault();
        }
    });
    $("#addcategory").click(function() {
        $("#newcategory_holder").removeClass("hidden");
        $("#newcategory").focus();
        $("#category_holder").addClass("hidden");
        
    });
    if (!Modernizr.inputtypes.date) {
        $(".due").datepicker({
            format: "dd-mm-yyyy",
            autoclose: "true",
            todayHighlight: "true"
        });
    }
    $("#addform").validator({
        disable: true
    });
    $("#addform").on("validate.bs.validator", function (e) {
        if ($("#due").parent().hasClass("has-error")) {
            $("#has_due_message").addClass("text-danger");    
        } else {
            $("#has_due_message").removeClass("text-danger");
        }        
    });
    $("#addform").on("valid.bs.validator", function (e) {
        $("#due").parent().removeClass("has-error"); 
        $("#has_due_message").removeClass("text-danger");
    });
    $("#addform").validator().on("submit", function (e) {
        if (e.isDefaultPrevented()) {
            return false;
        }
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: $("#addform").serialize(),
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
                    message: "Item added!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                $("#addform").trigger("reset");
            }
        });
        return false;
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