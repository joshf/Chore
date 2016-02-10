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
    $filters = array("categories", "normal", "priority", "completed", "date", "duetoday", "overdue", "created");
    if (!in_array($filter, $filters)) {
        $filter = "normal";
    }
  //Make sure cat exists
	if ($filter == "categories") {
		if (isset($_GET["cat"])) {
		    $cat_id = mysqli_real_escape_string($con, $_GET["cat"]);
            if ($cat_id == "none") {
                $cat_id = "";
            } else {
    		    $checkcatexists = mysqli_query($con, "SELECT `id`, `category` FROM `categories` WHERE `id` = \"$cat_id\"");
    		    if (mysqli_num_rows($checkcatexists) == 0) {
    		        $filter = "normal";
    		    }
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
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<link rel="icon" href="assets/favicon.ico">
<title>Chore</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/chore.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/bower_components/bootstrap-select/dist/css/bootstrap-select.min.css" type="text/css" media="screen">
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
<select class="form-control" id="filters" name="filters" data-live-search="true">
<option value="index.php">No Filter</option>
<optgroup label="Filters">
<option value="index.php?filter=priority">High Priority Tasks</option>
<option value="index.php?filter=completed">Completed Tasks</option>
<option value="index.php?filter=duetoday">Due Today</option>
<option value="index.php?filter=overdue">Overdue</option>
</optgroup>
<optgroup label="Categories">
<option value="index.php?filter=categories&amp;cat=none">No Category</option>
<?php

//Get categories
$getcategories = mysqli_query($con, "SELECT `id`, `category` FROM `categories`");

while($category = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"index.php?filter=categories&amp;cat=" . $category["id"] . "\">" . ucfirst($category["category"]) . "</option>";
}

?>
</optgroup>
<optgroup label="Sort By">
<option value="index.php?filter=date">Due Date</option>
<option value="index.php?filter=created">Created Date</option>
</optgroup>
</select>
</div>
</div>
</div>
<ul class="list-group">
<?php

if ($filter == "completed") {
    $getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"1\"");
} elseif ($filter == "priority") {
    $getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.priority = \"1\" AND items.completed = \"0\"");
} elseif ($filter == "categories") {
	$getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\" AND categories.id = \"$cat_id\"");
} elseif ($filter == "date") {
	$getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\" AND items.has_due = \"1\" ORDER BY items.due ASC");
} elseif ($filter == "duetoday") {
    $getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\" AND items.due = CURDATE() AND items.has_due = \"1\"");
} elseif ($filter == "overdue") {
    $getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\" AND items.due < CURDATE() AND items.has_due = \"1\"");
} elseif ($filter == "created") {
	$getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\" ORDER BY items.created ASC");
} else {
    $getitems = mysqli_query($con, "SELECT items.id, categories.category, items.priority, items.item, items.has_due, items.due FROM `items` LEFT JOIN `categories` ON categories.id = items.category_id WHERE items.completed = \"0\"");
}

if (mysqli_num_rows($getitems) != 0) {
    while($item = mysqli_fetch_assoc($getitems)) {

        //Logic
        $today = strtotime(date("Y-m-d"));
        $due = strtotime($item["due"]);

        echo "<li class=\"list-group-item\"><span class=\"list\" data-id=\"" . $item["id"] . "\">";

        if ($item["priority"] == "1") {
           echo "<b>" . $item["item"] . "</b>";
        } else {
            echo $item["item"];
        }

        echo "</span><div class=\"pull-right\">";
        if ($item["has_due"] == "1") {
            if ($today >= $due) {
                echo "<span class=\"hidden-xs badge badge-red\">" . $item["due"] . "</span> ";
            } else {
                echo "<span class=\"hidden-xs badge\">" . $item["due"] . "</span> ";
            }
        }
        if ($item["category"] != "") {
            echo "<span class=\"hidden-xs badge badge-blue\">" . $item["category"] . "</span> ";
        }
        echo "<span class=\"complete glyphicon glyphicon-ok\" title=\"Complete\" data-id=\"" . $item["id"] . "\"></span></div></li>";
    }
} else {
    echo "<li class=\"list-group-item\">No items to show</li>";
}

mysqli_close($con);

?>
</ul>
<span id="update"></span>
<span class="pull-right text-muted"><small>Version <?php echo $version; ?></small></span>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function () {
    var chore_version = "<?php echo $version; ?>";
    if (!Cookies.get("chore_didcheckforupddates")) {
        $.getJSON("https://api.github.com/repos/joshf/Chore/releases").done(function(resp) {
            var data = resp[0];
            var chore_remote_version = data.tag_name;
            var url = data.zipball_url;
            if (chore_version < chore_remote_version) {
                $("#update").html("<a href=\"https://github.com/joshf/Chore/compare/" + chore_version + "..." + chore_remote_version + "\" target=\"_blank\">Version " + chore_remote_version + "</a> is now available, click <a href=\"https://github.com/joshf/Chore/wiki/Updating-Chore\" target=\"_blank\">here</a> to for instructions on how to update.");
                Cookies.set("chore_didcheckforupdates", "1", { expires: 1 });
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
   $("#filters").selectpicker();
    if (Cookies.get("filter")) {
        var filter = Cookies.get("filter");
       $("#filters").val(filter);
       $("#filters").selectpicker('refresh');
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
