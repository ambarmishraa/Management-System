<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed :" . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $student_id = $_POST["student_name"];
  $subject_id = $_POST["subject_name"];
  $marks = $_POST["marks"];

  $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks) VALUES (?, ?, ?)");
  $stmt->bind_param("iii", $student_id, $subject_id, $marks);


  // Execute and check if the record was inserted successfully
  if ($stmt->execute()) {
    echo "New Marks added successfully";
  } else {
    echo "Error: " . $stmt->error;
  }

  // Close statement and connection
  $stmt->close();
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="marks.css" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marks</title>
  <script>
    function deleteDialog(event, id) {
      // Prevent the default action of the link
      event.preventDefault();

      // Show the confirmation dialog
      if (confirm("Are you sure you want to delete?!")) {
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
      <form action="Marks/marks_interface.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 93px;">Add Marks</h1>
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
            <select class="option-menu" id="course_id" name="course_id">
              <option value="">Select Subject</option>

              <?php echo $course_options; ?>
            </select>
          </div>
          <div class="input">
            <input
              class="field-1"
              placeholder="Subject Name"
              type="text"
              id="subject_name"
              name="subject_name"
              required />
          </div>
          <div class="input">
            <input class="submit-btn" type="submit" value="Add Marks">
          </div>
          <a href="#right-half">
            <h4 style="color: red; margin-left:10px;">View Table</h4>
          </a>
        </div>

      </form>
    </div>
  </div>


  <div class="right-half" id="right-half">
    <table border="1">
      <thead>
        <tr>
          <th>S.No</th>
          <th>Student Name</th>
          <th>Subject Name</th>
          <th>Marks</th>
          <th>Edit</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>

        <!-- Write your Code Here -->

      </tbody>
    </table>
  </div>
</body>

</html>