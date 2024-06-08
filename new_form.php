<?php

session_start();

if(!isset($_SESSION['logged_in']) || (isset($_SESSION['logged_in']) && $_SESSION['usertype'] === "customer")) {
    header('location:index.php');
    exit();
}

include("dbconnect.php");

$dealerid = $_SESSION['userid'];
$dealername = $_SESSION['username'];

if(isset($_POST["submit"])) {

    $m_name = mysqli_real_escape_string($conn, $_POST["m_name"]);
    $query1 = "SELECT manufacturerid FROM manufacturer WHERE mname = '$m_name'";
    $ex1 = mysqli_query($conn, $query1);
    $res1 = mysqli_fetch_assoc($ex1);

    $m_id = $res1["manufacturerid"];
    $price = mysqli_real_escape_string($conn, $_POST["price"]);

    $prepstat = "INSERT INTO car (name, cartype, mileage, color, status, fueltype, manufacturedate, manufacturerid) VALUES (?, 'new', ?, ?, 'available', ?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $prepstat)) {

        $name = mysqli_real_escape_string($conn, $_POST["name"]);
        $color = mysqli_real_escape_string($conn, $_POST["color"]);
        $mileage = mysqli_real_escape_string($conn, $_POST["mileage"]);
        $m_date = mysqli_real_escape_string($conn, $_POST["m_date"]);
        $fueltype = mysqli_real_escape_string($conn, $_POST["fueltype"]);

        mysqli_stmt_bind_param($stmt, "ssssss", $name, $mileage, $color, $fueltype, $m_date, $m_id);

        if (mysqli_stmt_execute($stmt)) {

            $query2 = "SELECT carid FROM car WHERE name = '$name' ORDER BY uploadedtime DESC LIMIT 1";
            $ex2 = mysqli_query($conn, $query2);
            $res2 = mysqli_fetch_assoc($ex2);
            $car_id = $res2["carid"];

            $ownsquery = "INSERT INTO owns (ownsid, carid, dealerid) VALUES (NULL, $car_id, $dealerid)";

            if (mysqli_query($conn, $ownsquery)) {

                $customerid = isset($_POST["customerid"]) ? mysqli_real_escape_string($conn, $_POST["customerid"]) : 'NULL';
                $priceinsertquery = "INSERT INTO newcar (newcarid, carid, price, customerid) VALUES (NULL, $car_id, $price, $customerid)";

                if (mysqli_query($conn, $priceinsertquery)) {

                    $f1 = mysqli_real_escape_string($conn, $_POST["f1"]);
                    $f2 = mysqli_real_escape_string($conn, $_POST["f2"]);
                    $f3 = mysqli_real_escape_string($conn, $_POST["f3"]);
                    $f4 = mysqli_real_escape_string($conn, $_POST["f4"]);

                    $featuresarr = array($f1, $f2, $f3, $f4);

                    for ($i = 0; $i < 4; $i++) {
                        $featurequery = "INSERT INTO features VALUES (NULL, $car_id, '$featuresarr[$i]')";
                        if (!mysqli_query($conn, $featurequery)) {
                            echo "Error while inserting feature into table!";
                            header("Location: error.php");
                            exit();
                        }
                    }

                    if (isset($_FILES['carimage'])) {
                        $target_dir = "Images/";
                        $file_name = $_FILES['carimage']['name'];
                        $file_tmp = $_FILES['carimage']['tmp_name'];
                        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                            header("location:error.php");
                            exit();
                        } else {
                            if (!is_dir($target_dir . $car_id . "/")) {
                                mkdir($target_dir . $car_id . "/");
                            }

                            $target_dir = $target_dir . $car_id . "/";
                            $newfilename = "1." . $imageFileType;
                            move_uploaded_file($file_tmp, $target_dir . $newfilename);
                            $imgpath = $target_dir . $newfilename;

                            $imageinsert = "INSERT INTO images (carid, images) VALUES ($car_id, '$imgpath')";

                            if ($imageex = mysqli_query($conn, $imageinsert)) {
                                $_SESSION['newcaradded'] = true;
                                header("location:dealer_index.php");
                                exit();
                            } else {
                                echo "some error occured while inserting image path in database!";
                                header("Location: error.php");
                                exit();
                            }
                        }
                    }

                    $_SESSION['newcaradded'] = true;
                    header("location:dealer_index.php");
                    exit();
                } else {
                    echo "Some error occurred while inserting data into newcar table!" . mysqli_error($conn);
                    header("Location: error.php");
                    exit();
                }

            } else {
                echo "Some error occurred while inserting data into owns table!";
                header("Location: error.php");
                exit();
            }

        } else {
            echo "Error: Could not execute the query: " . mysqli_error($conn);
            header("Location: error.php");
            exit();
        }

    }
}
?>



<!DOCTYPE html>
<html>
  <head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="icon" href="icon.ico">
    <title>Add New Car - Car Point</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <style>
      html, body {
      min-height: 100%;
      background-color:#F2ECF5;
      }
      body, div, form, input, select, textarea, p { 
      padding: 0;
      margin: 0;
      outline: none;
      font-family: 'Rubik', sans-serif;
      font-size: 16px;
      color: #000;
      line-height: 30px;
      }
      h1 {
      position: absolute;
      margin: 0;
      font-size: 36px;
      color: #fff;
      z-index: 2;
      }
      .testbox {
      display: flex;
      justify-content: center;
      align-items: center;
      height: inherit;
      padding: 20px;
      }
      form {
      width: 80%;
      padding: 20px;
      border-radius: 6px;
      background: #fff;
      box-shadow: 0 0 20px 0 #333; 
      }
      .banner {
      position: relative;
      height: 210px;
      display: flex;
      justify-content: center;
      align-items: center;
      background-image:linear-gradient(to right, #5F396F, #C39BD3);
      text-align: center;
      margin-bottom:20px;
      }
      .banner::after {
      content: "";
      background-color: rgba(0, 0, 0, 0.4); 
      position: absolute;
      width: 100%;
      height: 100%;
      }
      input, select {
      margin-bottom:15px;
      margin-top:5px;
      border: 1px solid #c0c0c0;
      border-radius: 5px;
      padding: 7.5px;
      transition:all 0.15s ease-in-out;
      }

      input {
      width: calc(100% - 15px);
      }
      select {
      width: 100%;
      padding: 10px 5px;
      background: transparent;
      }
     
      .item:hover p, .item:hover i, .question:hover p, .question label:hover, input:hover::placeholder {
      color: #333;
      }
      .item input:hover, .item select:hover,.name-item input:hover,.item input:focus, .item select:focus,.name-item input:focus {
      border: 1px solid transparent;
      box-shadow: 0 0 6px 0 #5F396F;
      }
      .item {
      position: relative;
      margin: 10px 0;
      }

      .btn-block {
      margin-top: 10px;
      text-align: center;
      }
      button {
      width: 25%;
      padding: 15px;
      font-family: 'Rubik', sans-serif;
      border: none;
      border-radius: 5px; 
     background-color:#5F396F;
      font-size: 18px;
      color: #fff;
      cursor: pointer;
      transition:all 0.2s ease-in-out;
      margin-bottom:15px;
      }

      button:hover {
        background-color:#C39BD3;
      }
      @media (min-width: 568px) {
      .name-item, .city-item {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      }
      .name-item input, .city-item input {
      width: calc(50% - 20px);
      }
      .city-item select {
      width: calc(50% - 8px);
      }
      }

      @media screen and (max-width:769px)
      {
        form {
        width: 100%;
        }
        button {
        width: 100%;
        }
      }
    </style>
  </head>



  <script type="text/javascript">

function back()
{
  window.location.href="dealer_index.php";
}
</script>

  <body>



  <?php

      $manuquery = "select mname from manufacturer";
      $manuex = mysqli_query($conn, $manuquery);

  ?>
    <div class="testbox">
      <form action="" method="POST" enctype="multipart/form-data">
      <div id="back" style="border-radius:5px;padding:10px;background-color:#D7BDE2;color:#512E5F;margin-bottom:10px;cursor:pointer;width:fit-content;font-size:18px" onclick="back()">< Go back</div>
        <div class="banner">
          <h1>New Car Form</h1>
        </div>
        <div class="item">
          <b>Basic Car Details</b>
        
          <div class="item">
          <!--<input type="text" name="m_name" placeholder="Manufacturer Name"  required/>-->

          Manufacturer Name
            <select name="m_name" required>

            <?php 
            while($row=mysqli_fetch_assoc($manuex))
            { 
            ?>
              <option value="<?php echo $row['mname']?>"><?php echo $row['mname']?></option>
            <?php  
            }
            ?>

            </select>


          </div>

          <div class="item">
            <input type="text" name="name" placeholder="Car Name (along with manufacturer name | eg - Ford EcoSport)" required>
          </div>

          <div class="name-item">
            <input type="text" name="color" placeholder="Color"  required/>
            <input type="number" name="mileage" placeholder="Mileage (km/l)" step="0.1" min="0"  required>
          </div>
        </div>

      <div class="item">
        Manufacture Date
        <input type="date" name="m_date" required>
      </div>

        <div class="item">
          Fuel Type
        	<select name="fueltype" required>
              <option value="petrol" selected>Petrol</option>
              <option value="diesel">Diesel</option>
            </select>
		</div>
		<div class="item">
          <input type="number" name="price" placeholder="On-road price (in PKR only)" min="0"  required>
        </div>      
        
        
        <b>FEATURES (required)</b>
        <div class="name-item">
          <input type="text" name="f1" placeholder="Car feature 1"  required>
          <input type="text" name="f2" placeholder="Car feature 2" required>
        </div>

        <div class="name-item">
          <input type="text" name="f3" placeholder="Car feature 3" required>
          <input type="text" name="f4" placeholder="Car feature 4" required>
        </div>

        <div class="item">
        <b>Choose a car image (optional)</b>
          <input type="file" name="carimage" style="display:flex;align-items:center">
        </div>

        <div class="btn-block">
          <button type="submit" name="submit">ADD CAR</button>
        </div>
      </form>
    </div>
  </body>
</html>