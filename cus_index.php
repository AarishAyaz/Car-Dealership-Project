<?php

// this is the session check for this page
session_start();

if (!isset($_SESSION['logged_in']) || (isset($_SESSION['logged_in']) && $_SESSION['usertype'] === "dealer")) // user not logged in or user logged in is a dealer
{
    header('location:index.php');
}

include("dbconnect.php");

$cusid = $_SESSION['userid']; // getting the customer id
$cusname = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $cusname . "'s "; ?> Dashboard - Car Point</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="icon" href="icon.ico">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <!-- BOOTSTRAP CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style>
        .searchbox {
            display: block;
            width: 80%;
            margin-top: 50px;
            transform: scaleY(0);  
            transform-origin: top;
            transition: transform 0.15s linear;
        }

        .card {
            margin-bottom: 10px;
            margin-top: 15px;
        }

        .card-img-top {
            min-height: 250px;
            max-height: 250px;
            object-fit: cover;
        }

        #listicon {
            position: absolute;
            left: 20px;
            margin-top: 1px;
            cursor: pointer;
        }

        #title {
            font-family: 'Open Sans', sans-serif;
            margin: auto;
            margin-bottom: 0.5px;
            text-align: center;   
            font-weight: 300;
            font-size: 1.5rem;
        }

        #header #logout {
            position: absolute;
            right: 20px;
            cursor: pointer;
        }

        #list {
            position: fixed;
            top: 0;
            height: 100%;
            z-index: 20;
            left: 0;
            background-color: #C39BD3;
            width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: width 0.15s ease-in-out;
        }

        #list a {
            font-weight: 350;
            text-align: center;
            color: white;
            font-size: 1.5rem;
            margin: 5px 0;
            transition: color 0.15s ease-in-out;
        }

        #list #active {
            cursor: default;
            color: #76448A;
        }

        #list a:hover {
            color: #76448A;
            text-decoration: none;
        }

        #list #closelist {
            cursor: pointer;
            background-color: #76448A;
            width: fit-content;
            position: absolute;
            top: 10px;
            padding: 5px;
            display: flex;
            align-items: center;
            right: 10px;
        }

        .row {
            align-items: flex-start;
        }

        li {
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 1px;
            max-width: 100%;
        }

        #discountbox {
            position: absolute;
            top: 0;
            right: 0;
            background-color: yellow;
            padding: 10px;
            color: black;
            font-weight: 300;
        }

        .card-subtitle {
            color: #884EA0;
        }

        @media screen and (max-width: 1000px) {
            #carname {
                font-size: 40px;
            }
        }

        @media screen and (max-width: 1200px) {
            .row {
                flex-direction: row;
                min-width: 80%;
            }

            .col-sm-3 {
                min-width: 50%;
            }
        }

        @media screen and (max-width: 769px) {
            .row {
                justify-content: center;
            }

            .col-sm-3 {
                min-width: 80%;
            }

            #explore {
                text-align: center;
                padding: 20px 0;
                border-bottom: 1px solid #C39BD3;
            }
        }
    </style>
</head>

<body>

<div id="list">
    <div id="closelist" onclick="openlist()">
        <svg class="bi bi-chevron-left" width="1.5em" height="1.5em" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 010 .708L5.707 8l5.647 5.646a.5.5 0 01-.708.708l-6-6a.5.5 0 010-.708l6-6a.5.5 0 01.708 0z" clip-rule="evenodd"/>
        </svg>
    </div>
    <a id="active">Home</a>
    <a href="cus_profile.php">Profile</a>
    <a href="cus_purchased.php">My Purchases</a>
    <a href="cus_rented.php">Rented cars</a>
</div>

<div class="container-fluid text-white py-3" id="header" style="background-color:black;position:fixed;z-index:5;top:0;display:flex;align-items:center">
    <div id="listicon" onclick="openlist()">
        <svg class="bi bi-list" width="2em" height="2em" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M2.5 11.5A.5.5 0 013 11h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4A.5.5 0 013 7h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4A.5.5 0 013 3h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z" clip-rule="evenodd"/>
        </svg>
    </div>
    <a id="logout" href="logout.php">
        <svg class="bi bi-x-square" width="1.5em" height="1.5em" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M14 1H2a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1zM2 0a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V2a2 2 0 00-2-2H2z" clip-rule="evenodd"/>
            <path fill-rule="evenodd" d="M11.854 4.146a.5.5 0 010 .708l-7 7a.5.5 0 01-.708-.708l7-7a.5.5 0 01.708 0z" clip-rule="evenodd"/>
            <path fill-rule="evenodd" d="M4.146 4.146a.5.5 0 000 .708l7 7a.5.5 0 00.708-.708l-7-7a.5.5 0 00-.708 0z" clip-rule="evenodd"/>
        </svg>
    </a>
    <img src="logow.png" height="50px" style="margin:auto">
</div>

<div class="container" style="width:80%;margin:auto;margin-top:135px">
    <h2 id="carname" class="display-4 text-center"><?php echo "Welcome " . $cusname . "!"; ?></h2>
</div>

<div class="input-group mb-3" style="width:80%;margin:auto;margin-top:65px">
    <input type="text" class="form-control" id="searchtext" placeholder="Search car name here..">
    <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" onclick="loadsearch()">Search</button>
    </div>
</div>

<div class="searchbox container-fluid py-3" id="searchbox" style="width:80%"></div>

<div class="container-fluid py-3" style="width:80%">
    <?php if (isset($_SESSION['boughtnewcar']) && $_SESSION['boughtnewcar'] === true) { ?>
    <div class="alert alert-primary" role="alert">
        Hooray! You just bought a new car! Go to <a href="cus_purchased.php">My Purchases</a>!
    </div>
    <?php unset($_SESSION['boughtnewcar']); } ?>

    <?php if (isset($_SESSION['rentedcar']) && $_SESSION['rentedcar'] === true) { ?>
    <div class="alert alert-primary" role="alert">
        Hooray! You just rented a new car! Go to <a href="cus_rented.php">My Rents</a>!
    </div>
    <?php unset($_SESSION['rentedcar']); } ?>

    <h3 id="explore" style="font-weight:lighter">Explore</h3>
    <div class="row">
    <?php
    $carQuery = "SELECT car.carid, car.name, car.status, car.cartype, images.images FROM car LEFT JOIN images ON images.carid = car.carid";
    $result1 = mysqli_query($conn, $carQuery);

    while ($row = mysqli_fetch_assoc($result1)) {
        $mainquery = "SELECT car.carid AS carid, car.name AS name, car.status AS status, car.cartype AS cartype, images.images AS images FROM car LEFT JOIN images ON images.carid = car.carid WHERE car.carid = " . (int)$row["carid"] . " LIMIT 1";
        $mainresult = mysqli_query($conn, $mainquery);

        if (!$mainresult) {
            echo "Error executing query: " . mysqli_error($conn);
            continue;
        }

        $cardet = mysqli_fetch_assoc($mainresult);

        if (!$cardet) {
            echo "No details found for carid: " . (int)$row["carid"];
            continue;
        }

        if ($cardet["cartype"] === 'new') {
            $discountquery = "SELECT discount FROM newcar WHERE newcarid = " . (int)$cardet['carid'];
        } else if ($cardet["cartype"] === 'resale') {
            $discountquery = "SELECT discount FROM preownedcar WHERE preownedcarid = " . (int)$cardet['carid'];
        } else {
            $discount = "0";
        }

        if (isset($discountquery)) {
            $disex = mysqli_query($conn, $discountquery);

            if ($disex) {
                $discountresult = mysqli_fetch_assoc($disex);

                if ($discountresult && $discountresult["discount"] !== null) {
                    $discount = $discountresult["discount"];
                } else {
                    $discount = "0";
                }
            } else {
                $discount = "0";
            }
        }
    ?>

        <div class="col-sm-3">
            <div class="card">
                <img class="card-img-top" src="<?php echo $cardet['images']; ?>" alt="<?php echo $cardet['name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $cardet['name']; ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo $cardet['cartype']; ?></h6>
                    <?php if ($discount !== "0") { ?>
                    <div id="discountbox"><?php echo $discount; ?>% off</div>
                    <?php } ?>
                </div>
            </div>
        </div>

    <?php
    }
    ?>
    </div>
</div>

<script type="text/javascript" src="JS/home.js"></script>
<script type="text/javascript" src="JS/list.js"></script>

</body>
</html>
