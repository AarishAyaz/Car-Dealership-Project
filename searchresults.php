<?php

include("dbconnect.php");

// Initialize the query parameter to an empty string if not provided in the POST request
$query = isset($_POST["query"]) ? '%' . $_POST["query"] . '%' : '%';

$stmt = $conn->prepare("SELECT car.carid, car.name, car.cartype, car.status, manufacturer.mname, images.images 
                        FROM car 
                        LEFT JOIN images ON car.carid = images.carid 
                        INNER JOIN manufacturer ON manufacturer.manufacturerid = car.manufacturerid 
                        WHERE (car.name LIKE ? OR car.cartype LIKE ? OR manufacturer.mname LIKE ?) 
                        GROUP BY car.carid");
$stmt->bind_param("sss", $query, $query, $query);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $output = '<div style="border:1px solid red;margin-top:15px;padding:10px 0;text-align:center;font-size:1.2rem;font-weight:lighter;color:red">No cars found!</div>';
} else {
    $output = "<div class='row'>";

    while ($row = $result->fetch_assoc()) {
        $row["discount"] = "0"; // Default discount

        if ($row["cartype"] === 'new') {
            $discountquery = "SELECT discount FROM newcar WHERE newcarid = {$row['carid']}";
        } elseif ($row["cartype"] === 'resale') {
            $discountquery = "SELECT discount FROM preownedcar WHERE preownedcarid = {$row['carid']}";
        } else {
            $discountquery = null;
        }

        if ($discountquery) {
            $ex = mysqli_query($conn, $discountquery);

            if ($ex && mysqli_num_rows($ex) > 0) {
                $discountresult = mysqli_fetch_assoc($ex);
                $row["discount"] = $discountresult["discount"] ?? "0";
            }
        }

        $row["images"] = $row["images"] ?? "dummy.png";

        // Setting link
        if ($row["cartype"] === 'new') {
            $link = "newcar.php?carid=" . $row["carid"];
        } elseif ($row["cartype"] === 'resale') {
            $link = "resalecar.php?carid=" . $row["carid"];
        } else {
            $link = "rentalcar.php?carid=" . $row["carid"];
        }

        // Setting color
        if ($row["status"] === "rented" || $row["status"] === "sold out") {
            $statuscolor = "#E74C3C";
        } else {
            $statuscolor = "#2ECC71";
        }

        $output .= '
        <div class="col-sm-3">
            <div class="card">
                <img src="' . htmlspecialchars($row["images"], ENT_QUOTES, 'UTF-8') . '" class="card-img-top" alt="Car image">
                <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') . '</h5>
                    <h6 class="card-subtitle mb-2">
                        <span style="color:' . htmlspecialchars($statuscolor, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row["status"], ENT_QUOTES, 'UTF-8') . '</span> | TYPE : ' . htmlspecialchars($row["cartype"], ENT_QUOTES, 'UTF-8') . '
                    </h6>
                    <hr>
                    <a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" class="card-link">More Details</a>';

        if ($row["discount"] !== "0" && $row["status"] !== "sold out") {
            $output .= '<div id="discountbox">Discount - ' . htmlspecialchars($row["discount"], ENT_QUOTES, 'UTF-8') . '%</div>';
        }

        $output .= '</div>
            </div>
        </div>';
    }

    $output .= "</div>";
}

echo $output;

$stmt->close();
?>
