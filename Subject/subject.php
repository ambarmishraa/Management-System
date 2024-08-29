<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_name = $_POST["subject_name"];
    $subject_code = $_POST["subject_code"];

    $stmt = $conn->prepare("INSERT INTO subject (subject_name, subject_code) VALUES (?, ?)");

    $stmt->bind_param("ss", $subject_name, $subject_code);

    if($stmt->execute()){
        echo '<script>alert("New Subject added Successfully.!");</script>';
    }
    else{
        echo "Error : " . $stmt->error;
    }
    header("Location: subject_interface.php");
    exit();
    $stmt->close();
}
$conn->close();
?>