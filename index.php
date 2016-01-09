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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_GET["filter"])) {
    $filter = mysqli_real_escape_string($con, $_GET["filter"]);
    //Prevent bad strings from messing with sorting
    $filters = array("categories", "normal", "highpriority", "completed", "date", "duetoday");
    if (!in_array($filter, $filters)) {
        $filter = "normal";
    }
    //Make sure cat exists
	if ($filter == "categories") {
		if (isset($_GET["cat"])) {
		    $cat = mysqli_real_escape_string($con, $_GET["cat"]);
            if ($cat == "none") {
                $cat = "";
            }
		    $checkcatexists = mysqli_query($con, "SELECT `category` FROM `items` WHERE `category` = \"$cat\"");
		    if (mysqli_num_rows($checkcatexists) == 0) {
		        $filter = "normal";
		    }
		} else {
			$filter = "normal";
		}
	}
} else {
    $filter = "normal";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Chore</title>
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
<h1>Chore</h1>
<ol class="breadcrumb">
<li><a href="index.php">Chore</a></li>
<li class="active">Home</li>
<li class="pull-right"><span id="add" title="Add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> <span id="settings" title="Settings" class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span id="logout" title="Logout" class="glyphicon glyphicon-log-out" aria-hidden="true"></span></li>
</ol>
<div class="row">
<div class="col-md-8"><div class="form-group"><input type="text" class="form-control" id="search" placeholder="Search..."></div>
</div>
<div class="col-md-4">
<div class="form-group">
<select class="form-control" id="filters" name="filters">
<option value="index.php">No Filters</option>
<optgroup label="Filters">
<option value="index.php?filter=highpriority">High Priority Tasks</option>
<option value="index.php?filter=completed">Completed Tasks</option>
<option value="index.php?filter=duetoday">Due Today</option>
</optgroup>
<optgroup label="Categories">
<?php

//Don't duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `items` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"index.php?filter=categories&amp;cat=none\">No Category</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `items` WHERE `category` != \"\"");

while($task = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"index.php?filter=categories&amp;cat=" . $task["category"] . "\">" . ucfirst($task["category"]) . "</option>";
}

?>
</optgroup>
<optgroup label="Sort">
<option value="index.php?filter=date">Due Date</option>
</optgroup>
</select>
</div>
</div>
</div>
<ul class="list-group">
<?php

if ($filter == "completed") {
    $getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `completed` = \"1\"");
} elseif ($filter == "highpriority") {
    $getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `highpriority` = \"1\" AND `completed` = \"0\"");
} elseif ($filter == "categories") {
	$getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `completed` = \"0\" AND `category` = \"$cat\"");
} elseif ($filter == "date") {
	$getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `completed` = \"0\" AND `has_due` = \"1\" ORDER BY `due` ASC");
} elseif ($filter == "duetoday") {
    $getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `completed` = \"0\" AND `due` = CURDATE() AND `has_due` = \"1\"");
} else {
    $getitems = mysqli_query($con, "SELECT * FROM `items` WHERE `completed` = \"0\"");
}

if (mysqli_num_rows($getitems) != 0) {
    while($item = mysqli_fetch_assoc($getitems)) {
        
        //Logic
        $today = strtotime(date("Y-m-d"));
        $due = strtotime($item["due"]);
        
        echo "<li class=\"list-group-item\"><span class=\"list\" data-id=\"" . $item["id"] . "\">";
        
        if ($today >= $due) { 
            if ($item["has_due"] == "1") {
                echo "<b><span class=\"text-danger\">" . $item["item"] . "</span></b>";
            } else {
                echo "" . $item["item"] . ""; 
            }
        } else {
            if ($item["highpriority"] == "1") {
               echo "<b>" . $item["item"] . "</b>"; 
            } else {
                echo "" . $item["item"] . ""; 
            }
        }
        
        echo "</span><div class=\"pull-right\">";
        if ($item["category"] != "") {
            echo "<span class=\"hidden-xs badge\">" . $item["category"] . "</span> ";
        }
        if ($item["has_due"] == "1") {
            echo "<span class=\"hidden-xs badge\">" . $item["due"] . "</span> ";
        }
        
        echo "<span class=\"complete glyphicon glyphicon-ok\" data-id=\"" . $item["id"] . "\"></span></div></li>";
    }
} else {
    echo "<li class=\"list-group-item\">No items to show</li>";
}

mysqli_close($con);

?>      
</ul>
<span class="pull-right text-muted"><small>Version <?php echo $version; ?></small></span>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootbox.js/bootbox.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function () {
    var chore_version = "<?php echo $version; ?>";
    if (!Cookies.get("chore_didcheckforupdates")) {
        $.getJSON("https://api.github.com/repos/joshf/Chore/releases").done(function(resp) {
            var data = resp[0];
            var chore_remote_version = data.tag_name;
            var url = data.zipball_url;
            if (chore_version < chore_remote_version) {
                bootbox.dialog({
                    message: "Chore " + chore_remote_version + " is available. For more information about this update click <a href=\""+ data.html_url + "\" target=\"_blank\">here</a>. Do you wish to download the update? If you click \"Not Now\" you will be not reminded for another 7 days.",
                    title: "Update Available",
                    buttons: {
                        cancel: {
                            label: "Not Now",
                            callback: function() {
                                Cookies.set("chore_didcheckforupdates", "1", { expires: 7 });
                            }
                        },
                        main: {
                            label: "Download Update",
                            className: "btn-primary",
                            callback: function() {
                                window.location.href = data.zipball_url;
                            }
                        }
                    }
                });
            }
        });
    }
    $("#filters").on("change", function() {
        Cookies.remove("filter");
        var url = $(this).val()
        Cookies.set("filter", url, { expires: 7 });
        window.location.href = url;
    });
    var ol = window.location.href;
    if (ol.indexOf("filter") == -1) {
        Cookies.remove("filter");
    }
    if (Cookies.get("filter")) {
        var filter = Cookies.get("filter");
        $("#filters").addClass("hide");
        $("#filters").val(filter);
        setTimeout(function() {
            $("#filters").removeClass("hide");
        }, 100);
    }
    $("#search").keyup(function() {
        $("#search-error").remove();
        var filter = $(this).val();
        var count = 0;
        $(".list-group .list-group-item").each(function() {
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).addClass("hidden");
                
            } else {
                $(this).removeClass("hidden");                
                count++;
            }            
        });
        if (count === 0) {
            $(".list-group").prepend("<li class=\"list-group-item\" id=\"search-error\">No items found</li>");
        }        
        
    });
    $("li").on("click", ".complete", function() {
        var id = $(this).data("id");
        var element = $(this).parent().parent();
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=complete&id="+ id +"",
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
                    message: "Item completed!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                $(element).hide();
                setTimeout(function() {
                    $(element).remove();
                }, 500);
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
    $("li").on("click", ".list", function() {
        var id = $(this).data("id");
        window.location.href = "view.php?item="+id;
    });
});
</script>
</body>
</html>