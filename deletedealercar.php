<?php

session_start();
include("dbconnect.php");

$carid = $_REQUEST["carid"];
$cartype = $_REQUEST["cartype"];
$dealerid = $_SESSION['userid'];
$status = $_REQUEST['status'];

if ($status === "sold out" || $status === "rented") {
    $_SESSION["deletesoldoutcar"] = true;
    header("Location: dealer_index.php");
    exit();
} else {

    // Delete from features
    $query2 = "DELETE FROM features WHERE carid = $carid";
    if (!mysqli_query($conn, $query2)) {
        echo "Error occurred while deleting features!";
        header("Location: error.php");
        exit();
    }

    // Delete from images
    $query5 = "DELETE FROM images WHERE carid = $carid";
    if (!mysqli_query($conn, $query5)) {
        echo "Error occurred while deleting images!";
        header("Location: error.php");
        exit();
    }

    // Delete from specific car type table
    if ($cartype === "new") {
        $query3 = "DELETE FROM newcar WHERE carid = $carid";
        if (!mysqli_query($conn, $query3)) {
            echo "Error occurred while deleting new car!";
            header("Location: error.php");
            exit();
        }
    } elseif ($cartype === "resale") {
        $query3 = "DELETE FROM preownedcar WHERE carid = $carid";
        if (!mysqli_query($conn, $query3)) {
            echo "Error occurred while deleting pre-owned car!";
            header("Location: error.php");
            exit();
        }
    } else {
        $query3 = "DELETE FROM rentalcar WHERE carid = $carid";
        if (!mysqli_query($conn, $query3)) {
            echo "Error occurred while deleting rental car!";
            header("Location: error.php");
            exit();
        }
    }

    // Delete from owns
    $query4 = "DELETE FROM owns WHERE carid = $carid AND dealerid = $dealerid";
    if (!mysqli_query($conn, $query4)) {
        echo "Error occurred while deleting ownership!";
        header("Location: error.php");
        exit();
    }

    // Delete from car table
    $query1 = "DELETE FROM car WHERE carid = $carid";
    if (!mysqli_query($conn, $query1)) {
        echo "Error occurred while deleting car!";
        header("Location: error.php");
        exit();
    } else {
        $_SESSION["deletedcar"] = true;
        header("Location: dealer_index.php");
        exit();
    }
}
?>
