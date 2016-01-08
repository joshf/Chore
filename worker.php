<?php

//Lists, Copyright Josh Fradley (http://github.com/joshf/Lists)

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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
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
$actions = array("complete", "restore", "delete", "info", "edit");
if (in_array($action, $actions)) {
    if (isset($_POST["id"]) || isset($_GET["id"])) {
        if (isset($_POST["action"])) {
            $id = mysqli_real_escape_string($con, $_POST["id"]);
        } elseif (isset($_GET["action"])) {
            $id = mysqli_real_escape_string($con, $_GET["id"]);
        }
                
        $checkid = mysqli_query($con, "SELECT `id` FROM `items` WHERE `id` = $id");
               
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
}
if (isset($_POST["details"])) {
    $details = mysqli_real_escape_string($con, $_POST["details"]);
}
if (isset($_POST["category"])) {
    $category = mysqli_real_escape_string($con, $_POST["category"]);
}
if (isset($_POST["due"])) {
    $due = mysqli_real_escape_string($con, $_POST["due"]);
}
if (isset($_POST["pk"])) {
    $pk = mysqli_real_escape_string($con, $_POST["pk"]);
}


if ($action == "add") {
    
    if (empty($item)) {
        die("Error: Data was empty!");
    }

    if (isset($_POST["highpriority"])) {
        $highpriority = "1";
    } else {
        $highpriority = "0";
    }
    
    
    if (empty($due)) {
        $has_due = "0";
    } else {
        $has_due = "1";
    }
    
    mysqli_query($con, "INSERT INTO `items` (`category`, `highpriority`, `item`, `details`, `has_due`, `created`, `due`, `completed`, `datecompleted`)
    VALUES (\"$category\",\"$highpriority\",\"$item\",\"$details\",\"$has_due\",CURDATE(),\"$due\",\"0\",\"\")");
    
    
    echo "Info: Item added!";
    
} elseif ($action == "edit") {
    
    if (isset($_POST["value"])) {
        $value = mysqli_real_escape_string($con, $_POST["value"]);
    } else {
        die("Error: Blank value");
    }
    
    if ($pk == "1") {
        mysqli_query($con, "UPDATE `items` SET `item` = \"$value\" WHERE `id` = \"$id\"");
        echo "called 1";
    } elseif ($pk == "2") {
        mysqli_query($con, "UPDATE `items` SET `details` = \"$value\" WHERE `id` = \"$id\"");
    } elseif ($pk == "3") {
        mysqli_query($con, "UPDATE `items` SET `due` = \"$value\" WHERE `id` = \"$id\"");
    } elseif ($pk == "4") {
        mysqli_query($con, "UPDATE `items` SET `category` = \"$value\" WHERE `id` = \"$id\"");
    } else {
        die("Error: Unknown key");
    }

    echo "Info: Item edited!";
    
} elseif ($action == "listcats") {
    
    $getitems = mysqli_query($con, "SELECT DISTINCT(category) FROM `items`");
    
    while($item = mysqli_fetch_assoc($getitems)) {
        if ($item["category"] == "") {
            $textcat = "None";
        } else {
            $textcat = $item["category"];
        }
    
        $items[] = array(
            "value" => $item["category"],
            "text" => $textcat
        );
    }
    echo json_encode($items);

} elseif ($action == "complete") {
    mysqli_query($con, "UPDATE `items` SET `completed` = \"1\", `datecompleted` = CURDATE() WHERE `id` = \"$id\"");
    
    echo "Info: Item marked as completed!";
    
} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `items` SET `completed` = \"0\" WHERE `id` = \"$id\"");
    
    echo "Info: Item restored!";
    
} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `items` WHERE `id` = \"$id\"");
    
    echo "Info: Item deleted!";
} elseif ($action == "info") {
    
    $getitems = mysqli_query($con, "SELECT `id`, `category`, `highpriority`, `item`, `details`, `created`, `has_due`, `due`, `completed`, `datecompleted` FROM `items` WHERE `id` = \"$id\"");
    
    while($item = mysqli_fetch_assoc($getitems)) {
    
        $items[] = array(
            "id" => $item["id"],
            "category" => $item["category"],
            "highpriority" => $item["highpriority"],
            "item" => $item["item"],
            "details" => $item["details"],
            "created" => $item["created"],
            "has_due" => $item["has_due"],
            "due" => $item["due"],
            "completed" => $item["completed"],
            "datecompleted" => $item["datecompleted"]
            
        );
    
    }
    echo json_encode(array("data" => $items));
    
} elseif ($action == "generateapikey") {
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `users` SET `api_key` = \"$api_key\" WHERE `id` = \"" . $_SESSION["chore_user"] . "\"");
    echo $api_key;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>