<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Query for fetching the course names and id starts
$sql_course = "SELECT id, course_name FROM course";
$course_result = $conn->query($sql_course);

$course_options = "";
if ($course_result->num_rows > 0) {

  $courses = $course_result->fetch_all(MYSQLI_ASSOC);

  foreach ($courses as $row) {
    $course_id = $row["id"];
    $course_name = $row["course_name"];
    $course_options .= "<option value='$course_id'>$course_name</option>";
  }
} else {
  $course_options = "<option value=''>No courses available</option>";
}
// Query for fetching the course names and id ends


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

  <div class="right-half" id="right-half">
    <table border="1">
      <thead>
        <tr>
          <th>S.No</th>
          <th>Student Name</th>
          <th>Student Email</th>
          <th>Student Course</th>
          <th>Edit</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "student_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
          die("Connection Failed : " . $conn->connect_error);
        }

        // Handle deletion
        if (isset($_GET['delete_id'])) {
          $id = intval($_GET['delete_id']);
          $delete_sql = "DELETE FROM subject WHERE id = $id";

          if ($conn->query($delete_sql) === TRUE) {
            echo "Record deleted successfully";
          } else {
            echo "Error deleting record: " . $conn->error;
          }

          // Reload the page to reflect changes
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // Handle edit form submission
        if (isset($_POST['update'])) {
          $id = intval($_POST['id']);
          $subject_name = $_POST['subject_name'];
          $subject_code = $_POST['subject_code'];

          $update_sql = "UPDATE subject SET subject_name = ?, subject_code = ? WHERE id = ?";
          $stmt = $conn->prepare($update_sql);
          $stmt->bind_param("ssi", $subject_name, $subject_code, $id);
          $stmt->execute();
          $stmt->close();

          // Reload the page to reflect changes
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // Handle edit request
        if (isset($_GET['edit_id'])) {
          $id = intval($_GET['edit_id']);
          $edit_sql = "SELECT * FROM subject WHERE id = $id";
          $result = $conn->query($edit_sql);

          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
          }


          if ($row) {
            echo '<div class="form-container" id="#edit-form-container">';
            echo '<h3>Edit Subject</h3>';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
            echo '<label for="subject_name">Subject Name:</label>';
            echo '<input type="text" name="subject_name" value="' . $row["subject_name"] . '" required>';
            echo '<label for="subject_code">Subject Code :</label>';
            echo '<input type="text" name="subject_code" value="' . $row["subject_code"] . '" required>';

            echo '<button type="submit" name="update">Update</button>';
            echo '</form>';
            echo '</div>';
          }
        }

        $course_sql = "SELECT id, course_name FROM course";
        $course_result = $conn->query($course_sql);
        $courseName = [];
        if ($course_result->num_rows > 0) {
          foreach ($course_result as $row) {
            $course_id = $row["id"];
            $course_name = $row["course_name"];
            $courseName[$course_id] = $course_name;
          }
        }


        $sql = "SELECT id, student_name, student_email, course_id FROM student";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["student_name"] . "</td>";
            echo "<td>" . $row["student_email"] . "</td>";

            $course_id = $row["course_id"];

            if (isset($courseName[$course_id])) {
              $course_name = $courseName[$course_id];
            } else {
              $course_name = "Unknown Course";
            }

            echo "<td>" . $course_name . "</td>";

            // echo '<td><a href="?edit_id=' . $row["id"] . '" class="button">Edit</a></td>';
            echo '<td><a href="?edit_id=' . $row["id"] . '#edit-form-container" class="button">Edit</a></td>';

            echo '<td><a href="#" onclick="deleteDialog(event, ' . $row["id"] . ')" class="button">Delete</a></td>';
            echo '</tr>';
          }
        } else {
          echo "<tr><td colspan='5'>0 results</td></tr>";
        }
        $conn->close();
        ?>

      </tbody>
    </table>
  </div>
</body>

</html>