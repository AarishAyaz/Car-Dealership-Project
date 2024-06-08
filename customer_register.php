<?php
if(isset($_POST["customerRegister"]))
{
    include("dbconnect.php");

    // Escape user inputs for security
    $c_name = mysqli_real_escape_string($conn, $_POST['C_name']);
    $c_dob = mysqli_real_escape_string($conn, $_POST['C_DOB']);
    $c_phoneno =  mysqli_real_escape_string($conn, $_POST['C_phoneNo']);
    $c_address =  mysqli_real_escape_string($conn, $_POST['C_address']);
    $c_drivingLicense =  mysqli_real_escape_string($conn, $_POST['C_drivingLicense']);
    $c_email = mysqli_real_escape_string($conn, $_POST['C_email']);
    $c_password = mysqli_real_escape_string($conn, $_POST['C_password']);

    // Check if email already exists in CUSTOMER_LOGIN table
    $check_email_query = "SELECT * FROM CUSTOMER_LOGIN WHERE C_Email = '$c_email'";
    $check_email_result = mysqli_query($conn, $check_email_query);

    if(mysqli_num_rows($check_email_result) > 0) {
        // Email already exists, show popup message using JavaScript
        echo '<script>alert("Email already exists. Please use a different email.")</script>';
        echo '<script>window.location.href = "error.php";</script>';
        exit(); // Stop further execution
    }

    // Email does not exist, proceed with insertion
    $customer_insert = "INSERT INTO CUSTOMER (CustomerName, DOB, PhoneNo, Address, DrivingLicense, C_Email) VALUES (?,?,?,?,?,?)";

    if($stmt = mysqli_prepare($conn, $customer_insert)) {
        // Bind the variables to prepared statements as parameters
        mysqli_stmt_bind_param($stmt, "ssssss", $c_name, $c_dob, $c_phoneno, $c_address, $c_drivingLicense, $c_email);
        mysqli_stmt_execute($stmt);

        // Generate verification key for email verification
        $vkey = md5(time().$c_name);

        // Insert into CUSTOMER_LOGIN table
        $customer_login = "INSERT INTO CUSTOMER_LOGIN (C_Email, Password, vkey) VALUES (?, ?, ?)";
        if($stmt_login = mysqli_prepare($conn, $customer_login)) {
            mysqli_stmt_bind_param($stmt_login, "sss", $c_email, $c_password, $vkey);
            mysqli_stmt_execute($stmt_login);

            // Redirect to thank you page
            header("Location: thankyou.php");
            exit(); // Stop further execution
        } else {
            echo "Error: Could not prepare the login query: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Could not prepare the insert query: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Close connection
} else {
    header("Location: error.php");
    exit(); // Stop further execution
}

?>

