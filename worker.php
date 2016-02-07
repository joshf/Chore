<?php

//Chore, Copyright Josh Fradley (http://github.com/joshf/Chore)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

//Connect to itemsbase
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to itemsbase (" . mysqli_connect_error() . "). Check your itemsbase settings are correct.");
}

session_start();
if (isset($_POST["api_key"]) || isset($_GET["api_key"])) {
    if (isset($_POST["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_POST["api_key"]);
    } elseif (isset($_GET["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_GET["api_key"]);
    }
    if (empty($api_key)) {
        die("Error: No API key passed!");
    }
    $checkkey = mysqli_query($con, "SELECT `id`, `user` FROM `users` WHERE `api_key` = \"$api_key\"");
    $checkkeyresult = mysqli_fetch_assoc($checkkey);
    if (mysqli_num_rows($checkkey) == 0) {
        die("Error: API key is not valid!");
    } else {
        $_SESSION["chore_user"] = $checkkeyresult["id"];
    }
}

if (!isset($_SESSION["chore_user"])) {
    header("Location: login.php");
    exit;
}

$getusersettings = mysqli_query($con, "SELECT `id`, `user` FROM `users` WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} elseif (isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
	die("Error: No action passed!");
}

//Check if ID exists
$actions = array("complete", "restore", "delete", "info", "edit", "duein", "deletecategory");
if (in_array($action, $actions)) {
    if (isset($_POST["id"]) || isset($_GET["id"])) {
        if (isset($_POST["action"])) {
            $id = mysqli_real_escape_string($con, $_POST["id"]);
        } elseif (isset($_GET["action"])) {
            $id = mysqli_real_escape_string($con, $_GET["id"]);
        }

        if ($action == "deletecategory") {
            $checkid = mysqli_query($con, "SELECT `id` FROM `categories` WHERE `id` = $id");
        } else {
            $checkid = mysqli_query($con, "SELECT `id` FROM `items` WHERE `id` = $id");
        }

        if (mysqli_num_rows($checkid) == 0) {
        	die("Error: ID does not exist!");
        }
    } else {
    	die("Error: ID not set!");
    }
}

//Define variables
if (isset($_POST["item"])) {
    $item = mysqli_real_escape_string($con, $_POST["item"]);
} elseif (isset($_GET["item"])) {
    $item = mysqli_real_escape_string($con, $_GET["item"]);
}
if (isset($_POST["details"])) {
    $details = mysqli_real_escape_string($con, $_POST["details"]);
} elseif (isset($_GET["details"])) {
    $details = mysqli_real_escape_string($con, $_GET["details"]);
}
if (isset($_POST["category"])) {
    $category = mysqli_real_escape_string($con, $_POST["category"]);
} elseif (isset($_GET["category"])) {
    $category = mysqli_real_escape_string($con, $_GET["category"]);
}
if (isset($_POST["new_category"])) {
    $new_category = mysqli_real_escape_string($con, $_POST["new_category"]);
} elseif (isset($_GET["new_category"])) {
    $new_category = mysqli_real_escape_string($con, $_GET["new_category"]);
}
if (isset($_POST["due"])) {
    $due = mysqli_real_escape_string($con, $_POST["due"]);
} elseif (isset($_GET["due"])) {
    $due = mysqli_real_escape_string($con, $_GET["due"]);
}
if (isset($_POST["pk"])) {
    $pk = mysqli_real_escape_string($con, $_POST["pk"]);
} elseif (isset($_GET["pk"])) {
    $pk = mysqli_real_escape_string($con, $_GET["pk"]);
}

if ($action == "add") {

    if (empty($item)) {
        die("Error: Data was empty!");
    }

    if (isset($_POST["priority"])) {
        $priority = "1";
    } else {
        $priority = "0";
    }

    if (empty($due)) {
        $has_due = "0";
    } else {
        $has_due = "1";
    }

    $datecheck = "/\d{1,2}\-\d{1,2}\-\d{4}/";
    if (preg_match($datecheck, $due)) {
        $segments = explode("-", $due);
        if (count($segments) == 3) {
            list($day, $month, $year) = $segments;
        }
        $due = "$year-$month-$day";
    }

    mysqli_query($con, "INSERT INTO `items` (`category_id`, `priority`, `item`, `details`, `has_due`, `created`, `due`, `completed`, `date_completed`)
    VALUES (\"$category\",\"$priority\",\"$item\",\"$details\",\"$has_due\",CURDATE(),\"$due\",\"0\",\"\")");

    echo "Info: Item added!";

} elseif ($action == "addcategory") {

    if (empty($new_category)) {
        die("Error: Data was empty!");
    }

    mysqli_query($con, "INSERT INTO `categories` (`category`)
    VALUES (\"$new_category\")");

    echo mysqli_insert_id($con);


} elseif ($action == "edit") {

    if (isset($_POST["value"])) {
        $value = mysqli_real_escape_string($con, $_POST["value"]);
    } elseif (isset($_GET["value"])) {
        $value = mysqli_real_escape_string($con, $_GET["value"]);
    } else {
        die("Error: Blank value");
    }

    if ($pk == "1") {
        mysqli_query($con, "UPDATE `items` SET `item` = \"$value\" WHERE `id` = \"$id\"");
    } elseif ($pk == "2") {
        mysqli_query($con, "UPDATE `items` SET `details` = \"$value\" WHERE `id` = \"$id\"");
    } elseif ($pk == "3") {

        $datecheck = "/\d{1,2}\-\d{1,2}\-\d{4}/";
        if (preg_match($datecheck, $value)) {
            $segments = explode("-", $value);
            if (count($segments) == 3) {
                list($day, $month, $year) = $segments;
            }
            $value = "$year-$month-$day";
        }

        mysqli_query($con, "UPDATE `items` SET `has_due` = \"1\", `due` = \"$value\" WHERE `id` = \"$id\"");
    } elseif ($pk == "4") {
        mysqli_query($con, "UPDATE `items` SET `category_id` = \"$value\" WHERE `id` = \"$id\"");
    } else {
        die("Error: Unknown key!");
    }

    echo "Info: Item edited!";

} elseif ($action == "listcats") {

    $getcats = mysqli_query($con, "SELECT `id`, `category` FROM `categories`");

    while($cat = mysqli_fetch_assoc($getcats)) {
        $cats[] = array(
            "value" => $cat["id"],
            "text" => $cat["category"]
        );
    }

    $cats[] = array("text"=> "None", "value" => "");

    echo json_encode($cats);

} elseif ($action == "complete") {
    mysqli_query($con, "UPDATE `items` SET `completed` = \"1\", `date_completed` = CURDATE() WHERE `id` = \"$id\"");

    echo "Info: Item marked as completed!";

} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `items` SET `completed` = \"0\" WHERE `id` = \"$id\"");

    echo "Info: Item restored!";

} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `items` WHERE `id` = \"$id\"");

    echo "Info: Item deleted!";
} elseif ($action == "deletecategory") {
    mysqli_query($con, "DELETE FROM `categories` WHERE `id` = \"$id\"");

    echo "Info: Category deleted!";
} elseif ($action == "info") {
    $getitems = mysqli_query($con, "SELECT `id`, `category_id`, `priority`, `item`, `details`, `created`, `has_due`, `due`, `completed`, `date_completed` FROM `items` WHERE `id` = \"$id\"");

    while($item = mysqli_fetch_assoc($getitems)) {
        $items[] = array(
            "id" => $item["id"],
            "category_id" => $item["category_id"],
            "priority" => $item["priority"],
            "item" => $item["item"],
            "details" => $item["details"],
            "created" => $item["created"],
            "has_due" => $item["has_due"],
            "due" => $item["due"],
            "completed" => $item["completed"],
            "date_completed" => $item["date_completed"]
        );
    }

    echo json_encode(array("item" => $items));

} elseif ($action == "duein") {
    $getdue = mysqli_query($con, "SELECT `due` FROM `items` WHERE `id` = \"$id\"");
    $getdueresults = mysqli_fetch_assoc($getdue);
    $today = strtotime(date("Y-m-d"));
    $due = strtotime($getdueresults["due"]);
    $datediff = abs($today - $due);
    $duein = floor($datediff/(60*60*24));

    if ($today > $due) {
        if ($duein == "1") {
            $string = "Overdue by " . $duein . " day";
        } else {
            $string = "Overdue by " . $duein . " days";
        }
    } else {
        if ($duein == "1") {
            $string = "Due in " . $duein . " day";
        } else {
            $string = "Due in " . $duein . " days";
        }
    }
    if ($duein == "0") {
        $string = "Due Today";
    }

    echo $string;

} elseif ($action == "generateapikey") {
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `users` SET `api_key` = \"$api_key\" WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
    echo $api_key;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>
