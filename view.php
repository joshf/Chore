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

if (isset($_GET["item"])) {
	$itemid = mysqli_real_escape_string($con, $_GET["item"]);	
} else {
	die("Error: No item passed!");
}

$itemcheck = mysqli_query($con, "SELECT `id` FROM `items` WHERE `id` = $itemid");
if ($itemcheck === FALSE || mysqli_num_rows($itemcheck) == "0") {
    die("Error: Item does not exist!");   
}
$resultitemcheck = mysqli_fetch_assoc($itemcheck);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<link rel="icon" href="assets/favicon.ico">
<title>Chore &raquo; View</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/chore.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/bower_components/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css" type="text/css" media="screen">
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
<li class="active">View</li>
<li class="pull-right"><span id="add" title="Add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> <span id="settings" title="Settings" class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span id="logout" title="Logout" class="glyphicon glyphicon-log-out" aria-hidden="true"></span></li></ol>
</ol>
<?php

$getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `id` = \"$itemid\"");

if (mysqli_num_rows($getitems) != 0) {
    while($item = mysqli_fetch_assoc($getitems)) {
        
        echo "<p><span class=\"glyphicon glyphicon-pencil\" title=\"Item\" aria-hidden=\"true\"></span> <span id=\"item\">" . $item["item"] . "</span></p>";
        echo "<p><span class=\"glyphicon glyphicon-zoom-in\" title=\"Details\" aria-hidden=\"true\"></span> <span id=\"details\">" . $item["details"] . "</span></p>";
        echo "<p><span class=\"glyphicon glyphicon-info-sign\" title=\"Created\" aria-hidden=\"true\"></span> <span id=\"created\">" . $item["created"] . "</span></p>";
  
        if (!empty($item["category"])) {
            echo "<p><span class=\"glyphicon glyphicon-tags\" title=\"Category\" aria-hidden=\"true\"></span> <span id=\"category\">" . $item["category"] . "</span></p>";
        }
        
        if ($item["has_due"] == "1") {
            if (!empty($item["due"])) {
 
                $today = strtotime(date("Y-m-d"));
                $due = strtotime($item["due"]);
                $datediff = abs($today - $due);
                $duein = floor($datediff/(60*60*24));
                
                echo "<p><span class=\"glyphicon glyphicon-calendar\" title=\"Due\" aria-hidden=\"true\"></span> <span id=\"due\">" . $item["due"] . "</span> ";
                
                    if ($today > $due) {
                        if ($duein == "1") {
                            $string = "Overdue by " . $duein . " day";
                        } else {
                            $string = "Overdue by " . $duein . " days";
                        }                    
                        $overdue = "true";
                    } else {
                        if ($duein == "1") {
                            $string = "Due in " . $duein . " day";
                        } else {
                            $string = "Due in " . $duein . " days";
                        } 
                    }
                    if ($duein == "0") {
                        $string = "Due Today";
                        $overdue = "true";
                    }                 
                    if ($overdue == "true") {
                        echo "<span id=\"duein\" class=\"text-danger\">";
                    } else {
                        echo "<span id=\"duein\">";
                    }
                    echo "($string)</span></p>";
            }
        }
        echo "<div class=\"btn-group\" role=\"group\">";
        if ($item["completed"] == "0") {
            echo "<button type=\"button\" id=\"complete\" class=\"btn btn-default\"><span class=\"glyphicon glyphicon-ok\" title=\"Complete\" aria-hidden=\"true\"></span> Complete</button>";
        } else {
            echo "<button type=\"button\" id=\"restore\" class=\"btn btn-default\"><span class=\"glyphicon glyphicon-repeat\" title=\"Restore\" aria-hidden=\"true\"></span> Restore</button>";
        }
        echo "<button type=\"button\" id=\"delete\" class=\"btn btn-default\"><span class=\"glyphicon glyphicon-remove\" title=\"Delete\" aria-hidden=\"true\"></span> Delete</button></div>";
    }
}

mysqli_close($con);

?>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/moment.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function() {
    var id = <?php echo $itemid; ?>;
    $.fn.editable.defaults.mode = "popup";    
    $.fn.editable.defaults.placement = "bottom";  
    $.fn.editable.defaults.showbuttons = true;
    $.fn.editable.defaults.url = "worker.php?action=edit&id=" + id + "";
    $.fn.editable.defaults.savenochange = true;
    $.fn.editable.defaults.highlight = "#5cb85c";
    $.fn.editable.defaults.toggle = "click";
    $("#item").editable({
        type: "text",
        pk: 1,
        title: "Item",
    });
    $("#details").editable({
        type: "text",
        pk: 2,
        title: "Details",
    });
    var due = $("#due").editable({
        type: "combodate",
        combodate: {
            minYear: moment().get("year"),
            maxYear: moment().get("year") + 2,
            firstItem: "none",
            smartDays: true
        },
        pk: 3,
        title: "Due",
    });
   $("#due").on("save", function(e, params) {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=duein&id="+ id +"",
            error: function() {
                console.log("Error: could not connect to worker!");
            },
            success: function(duein) {
                $("#duein").removeClass("text-danger");
                if(duein.indexOf("Overdue") != -1){
                    $("#duein").addClass("text-danger");
                }
                if(duein.indexOf("Due Today") != -1){
                    $("#duein").addClass("text-danger");
                }
                $("#duein").html("(" + duein + ")");
            }
        });
    });
    $("#category").editable({
        type: "select",
        pk: 4,
        source: "worker.php?action=listcats",
        title: "Category",
    });
    $("#delete").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=delete&id="+ id +"",
            error: function() {
                console.log("Error: could not connect to worker!");
            },
            success: function() {
            	window.location.href = "index.php";
            }
        });
    });
    $("#complete").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=complete&id="+ id +"",
            error: function() {
                console.log("Error: could not connect to worker!");
            },
            success: function() {
            	window.location.href = "index.php";
            }
        });
    });
    $("#restore").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=restore&id="+ id +"",
            error: function() {
                console.log("Error: could not connect to worker!");
            },
            success: function() {
            	window.location.href = "index.php";  
            }
        });
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