<?php
if(isset($_POST["dealerRegister"]))
{
    include("dbconnect.php");

    // Escape user inputs for security
    $d_name = mysqli_real_escape_string($conn, $_POST['D_name']);
    $d_phoneno =  mysqli_real_escape_string($conn, $_POST['phone_no']);
    $d_website =  mysqli_real_escape_string($conn, $_POST['website']);
    $d_email = mysqli_real_escape_string($conn, $_POST['D_email']);
    $d_password = mysqli_real_escape_string($conn, $_POST['D_password']);

    // Check if email already exists in DEALER_LOGIN table
    $check_email_query = "SELECT * FROM DEALER_LOGIN WHERE D_Email = '$d_email'";
    $check_email_result = mysqli_query($conn, $check_email_query);

    if(mysqli_num_rows($check_email_result) > 0) {
        // Email already exists, show popup message using JavaScript
        echo '<script>alert("Email already exists. Please use a different email.")</script>';
        echo '<script>window.location.href = "error.php";</script>';
        exit(); // Stop further execution
    }

    // Email does not exist, proceed with insertion
    $dealer_insert = "INSERT INTO DEALER (DName, PhoneNo, Website, D_Email) VALUES (?,?,?,?)";

    if($stmt = mysqli_prepare($conn, $dealer_insert)) {
        // Bind the variables to prepared statements as parameters
        mysqli_stmt_bind_param($stmt, "ssss", $d_name, $d_phoneno, $d_website, $d_email);
        mysqli_stmt_execute($stmt);

        // Generate verification key for email verification
        $vkey = md5(time().$d_name);

        // Insert into DEALER_LOGIN table
        $dealer_login = "INSERT INTO DEALER_LOGIN (D_Email, Password, vkey) VALUES (?, ?, ?)";
        if($stmt_login = mysqli_prepare($conn, $dealer_login)) {
            mysqli_stmt_bind_param($stmt_login, "sss", $d_email, $d_password, $vkey);
            mysqli_stmt_execute($stmt_login);
            
            // Redirect to thank you page
            header("Location: thankyou.php");
            exit(); // Stop further execution
        } else {
            echo "Error: Could not prepare the login query: " . mysqli_error($conn);
            exit(); // Stop further execution
        }
    } else {
        echo "Error: Could not prepare the insert query: " . mysqli_error($conn);
        exit(); // Stop further execution
    }

    mysqli_close($conn); // Close connection
} else {
    header("Location: error.php");
    exit(); // Stop further execution
}
?>

