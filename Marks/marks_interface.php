<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

 // Handle deletion
 if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
  $delete_sql = "DELETE FROM marks WHERE id = ?";
  $stmt = $conn->prepare($delete_sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();

  // Reload the page to reflect changes
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

$last_student_name = "";
$index = 1;

$subject_id_name = "SELECT id, subject_name FROM subject";
$result_subject_id_name = $conn->query($subject_id_name);

$fetched_subject_id_name = "";
if ($result_subject_id_name->num_rows > 0) {
  while ($row = $result_subject_id_name->fetch_assoc()) {
    $fetched_subject_id_name .= "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["subject_name"]) . "</option>";
  }
} else {
  $fetched_subject_id_name = "<option value=''>No Subjects available</option>";
}

$student_id_name = "SELECT id, student_name FROM student";
$result_student_id_name = $conn->query($student_id_name);

$fetched_student_id_name = "";
if ($result_student_id_name->num_rows > 0) {
  while ($row = $result_student_id_name->fetch_assoc()) {
    $fetched_student_id_name .= "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["student_name"]) . "</option>";
  }
} else {
  $fetched_student_id_name = "<option value=''>No Students Available</option>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $student_id = $_POST["student_id"];
  $subject_id = $_POST["subject_id"];
  $marks = $_POST["marks"];

  $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks) VALUES (?, ?, ?)");
  $stmt->bind_param("iii", $student_id, $subject_id, $marks);

  if ($stmt->execute()) {
    echo "New Marks added successfully";
  } else {
    echo "Error: " . $stmt->error;
  }
  $stmt->close();

  header("Location: marks_interface.php");
  exit();
}

$marks_query = "SELECT marks.id AS marks_id, marks.student_id, marks.subject_id, marks.marks, 
               student.student_name, subject.subject_name
              FROM marks, student, subject
              WHERE marks.student_id = student.id
              AND marks.subject_id = subject.id
              ORDER BY student.id";

$marks_result = $conn->query($marks_query);

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
      event.preventDefault();
      if (confirm("Are you sure you want to delete?!")) {
        window.location.href = "?delete_id=" + id;
      }
    }
  </script>
</head>

<body>
  <div class="left-half">
    <div class="inner-container">
      <form action="marks_interface.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 93px;">Add Marks</h1>
          <div class="input">
            <select class="option-menu" id="student_id" name="student_id">
              <option value="">Select Student</option>
              <?php echo $fetched_student_id_name; ?>
            </select>
          </div>
          <div class="input">
            <select class="option-menu" id="subject_id" name="subject_id">
              <option value="">Select Subject</option>
              <?php echo $fetched_subject_id_name; ?>
            </select>
          </div>
          <div class="input">
            <input class="field-1" placeholder="Fills Marks" type="number" id="marks" name="marks" required />
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
        <?php
         if ($marks_result->num_rows > 0) {
          $is_first_row = true; // Flag to handle the first row of each student
          while ($row = $marks_result->fetch_assoc()) {
            if ($last_student_name != $row['student_name']) {
              // Print the serial number for the first row of a new student
              if (!$is_first_row) {
                echo "<tr class='gap-row'><td colspan='6'></td></tr>";
              }
              echo "<tr>";
              echo "<td>{$index}</td>";
              echo "<td>{$row['student_name']}</td>";
              $last_student_name = $row['student_name'];
              $index++;
              $is_first_row = false; // Subsequent rows for the same student
            } else {
              // Skip printing the serial number and student name for subsequent rows with the same student
              echo "<tr>";
              echo "<td></td>"; // Empty serial number cell
              echo "<td></td>"; // Empty student name cell
            }
            echo "<td>{$row['subject_name']}</td>";
            echo "<td>{$row['marks']}</td>";
            // echo "<td><p>{$row['marks_id']}</p></td>";
            echo "<td><a href='marks_edit.php?edit_id={$row['marks_id']}' class='button'>Edit</a></td>";
            echo "<td><a href='#' onclick='deleteDialog(event, {$row['marks_id']})' class='button'>Delete</a></td>";
            echo "</tr>";
            $index++;
          }
        } else {
          echo "<tr><td colspan='6'>No records found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</body>

</html>