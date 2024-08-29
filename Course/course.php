<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $coordinator_id = $_POST['coordinator_id'];

    $coordinator_check = $conn->prepare("SELECT id FROM coordinator WHERE id = ?");
    $coordinator_check->bind_param("s", $coordinator_id);
    $coordinator_check->execute();
    $coordinator_check->store_result();

    if ($coordinator_check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO course (course_name, course_code, coordinator_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $course_name, $course_code, $coordinator_id);

        if ($stmt->execute()) {
            echo "New course added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: The coordinator ID does not exist.";
    }
    header("Location: course-interface.php");
    exit();
    $coordinator_check->close();
}

$conn->close();
