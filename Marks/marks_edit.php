<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$edit_id = null;
$student_name = "";
$subject_name = "";
$marks = "";

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    // Fetch the record to edit
    $stmt = $conn->prepare("
    SELECT marks.id, marks.marks, student.student_name, subject.subject_name 
    FROM marks, student, subject 
    WHERE marks.student_id = student.id 
      AND marks.subject_id = subject.id 
      AND marks.id = ?");

    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_name = $row['student_name'];
        $subject_name = $row['subject_name'];
        $marks = $row['marks'];
    } else {
        echo "Record not found.";
        exit();
    }
    $stmt->close();
}

// Handle form submission to update the record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updated_student_name = $_POST['student_name'];
    $updated_subject_name = $_POST['subject_name'];
    $updated_marks = $_POST['marks'];

    // Find IDs for student and subject
    $stmt = $conn->prepare("SELECT id FROM student WHERE student_name = ?");
    $stmt->bind_param("s", $updated_student_name);
    $stmt->execute();
    $student_result = $stmt->get_result();
    $student_id = $student_result->fetch_assoc()['id'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM subject WHERE subject_name = ?");
    $stmt->bind_param("s", $updated_subject_name);
    $stmt->execute();
    $subject_result = $stmt->get_result();
    $subject_id = $subject_result->fetch_assoc()['id'];
    $stmt->close();

    // Update the record
    $stmt = $conn->prepare("UPDATE marks SET student_id = ?, subject_id = ?, marks = ? WHERE id = ?");
    $stmt->bind_param("iiii", $student_id, $subject_id, $updated_marks, $edit_id);

    if ($stmt->execute()) {
        // echo "Record updated successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: marks_interface.php");
  exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="marks_edit.css" />
    <title>Edit Marks</title>
</head>

<body>
    <div class="main-container">
        <form action="marks_edit.php?edit_id=<?php echo htmlspecialchars($edit_id); ?>" method="post">
            <table border="1">
                <thead>
                    <tr>
                        <th>Row No</th>
                        <th>Student Name</th>
                        <th>Subject Name</th>
                        <th>Marks</th>
                        <th>
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($edit_id); ?></td>
                        <td>
                            <input type="text" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" readonly />
                        </td>
                        <td>
                            <input type="text" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" readonly />
                        </td>
                        <td>
                            <input type="number" name="marks" value="<?php echo htmlspecialchars($marks); ?>" required />
                        </td>
                        <td><input type="submit" value="Update Marks" /></td>
                    </tr>
                </tbody>
            </table>

        </form>
    </div>
</body>

</html>