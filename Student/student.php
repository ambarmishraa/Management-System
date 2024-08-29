<?php 
$servername ="localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
    die("Connection failed :" . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$student_name = $_POST["student_name"];
$student_email = $_POST["student_email"];
$course_id = $_POST["course_id"];

$stmt = $conn->prepare("INSERT INTO student (student_name, student_email, course_id) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $student_name, $student_email, $course_id);
    if ($stmt->execute()) {
        echo "New coordinator added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    header("Location: student_Interface.php");
    exit();
    $stmt->close();
}

$conn->close();
?>