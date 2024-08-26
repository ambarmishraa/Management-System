<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query for fetching the course names
$sql_course = "SELECT id, course_name FROM course";
$course_result = $conn->query($sql_course);

$course_options = "";
if ($course_result->num_rows > 0) {
    while ($row = $course_result->fetch_assoc()) {
        $course_options .= "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["course_name"]) . "</option>";
    }
} else {
    $course_options = "<option value=''>No courses available</option>";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="student.css" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student</title>
  <script>
function deleteDialog(event, id) {
    // Prevent the default action of the link
    event.preventDefault();

    // Show the confirmation dialog
    if (confirm("Are you sure you want to delete?!")) {
        // If user presses OK, proceed to delete
        // Redirect to the deletion URL with the ID
        window.location.href = "?delete_id=" + id;
    } else {
        // If user presses Cancel, do nothing
        console.log("Deletion cancelled.");
    }
}
</script>
</head>

<body>
  <div class="left-half">
    <div class="inner-container">
      <form action="student.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 53px;">Add New Student</h1>
          <div class="input">
            <input
              class="field"
              placeholder="Student Name"
              type="text"
              id="student_name"
              name="student_name"
              required />
          </div>
          <div class="input">
            <input
              class="field-1"
              placeholder="Student Email"
              type="email"
              id="student_email"
              name="student_email"
              required />
          </div>
          <div class="input">
            <select class="option-menu" id="course_id" name="course_id">
            <option value="">Choose Course</option>

              <?php echo $course_options; ?>
            </select>
          </div>
          <div class="input">
            <input class="submit-btn" type="submit" value="Add Student">
          </div>
          <a href="#right-half">
            <h4 style="color: red; margin-left:10px;">View Table</h4>
          </a>
        </div>

      </form>
    </div>
  </div>