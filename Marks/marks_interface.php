<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['student_id'] )) {
  $student_id = intval($_GET['student_id']);
  // Use $student_id to fetch and display the student's marks
  echo "<script>console.log('Student ID: " . $student_id . "');</script>";
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

// Initialize variables
$course_id = 0;
$selected_student_id = !empty($_POST["student_id"]) ? intval($_POST["student_id"]) : null;
if ($selected_student_id) {
  $course_id_query = "SELECT course_id FROM student WHERE id = ?";
  $stmt = $conn->prepare($course_id_query);
  $stmt->bind_param("i", $selected_student_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $course_id = $row['course_id'];
  }
  $stmt->close();
}

// Fetch subjects based on the selected course_id
$subjects_query = "SELECT subject.id, subject.subject_name 
                   FROM subject 
                   JOIN subcourse 
                   WHERE subject.id = subcourse.subject_id
                   AND subcourse.course_id = ?";
$fetched_subject_id_name = "";
if ($course_id > 0) {
  $stmt = $conn->prepare($subjects_query);
  $stmt->bind_param("i", $course_id);
  $stmt->execute();
  $result_subject_id_name = $stmt->get_result();
  if ($result_subject_id_name->num_rows > 0) {
    while ($row = $result_subject_id_name->fetch_assoc()) {
      $fetched_subject_id_name .= "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["subject_name"]) . "</option>";
    }
  } else {
    $fetched_subject_id_name = "<option value=''>No Subjects Available</option>";
  }
  $stmt->close();
} else {
  $fetched_subject_id_name = "<option value=''>Select a student first</option>";
}

// Fetch students
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

// Handle form submission for adding marks
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['student_id']) && !empty($_POST['subject_id']) && isset($_POST['marks'])) {
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

// Fetch marks
$marks_query = "SELECT marks.id AS marks_id, marks.student_id, marks.subject_id, marks.marks, 
               student.student_name, subject.subject_name
              FROM marks
              JOIN student ON marks.student_id = student.id
              JOIN subject ON marks.subject_id = subject.id
              ORDER BY student.id";

$marks_result = $conn->query($marks_query);

// Initialize variables for the table
$last_student_name = "";
$index = 1;

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

    function updateSubjects() {
      var studentSelect = document.getElementById('student_id');
      if (studentSelect) {
        studentSelect.addEventListener('change', function() {

          console.log('Selected student ID:', this.value);

          this.form.submit();
        });
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      updateSubjects();
    });
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
              <?php echo $student_id; ?>
            </select>
          </div>
          <div class="input">
            <select class="option-menu" id="subject_id" name="subject_id">
              <?php echo $fetched_subject_id_name; ?>
            </select>
          </div>
          <div class="input">
            <input class="field-1" placeholder="Enter Marks" type="number" id="marks" name="marks" required />
          </div>
          <div class="input">
            <input class="submit-btn" type="submit" value="Add Marks">
          </div>
          <div style="color: red; margin-left:40%;">
            <button style="background-color: #fff; border-radius:10px; padding:4px"><a href="#right-half">View Table</a></button>
          </div>
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
          $last_student_name = "";
          $index = 1; // Initialize index
          while ($row = $marks_result->fetch_assoc()) {
            if ($last_student_name != $row['student_name']) {
              // Print the serial number for the first row of a new student
              if ($last_student_name != "") {
                echo "<tr class='gap-row'><td colspan='6'></td></tr>";
              }
              echo "<tr>";
              echo "<td>{$index}</td>";
              echo "<td>{$row['student_name']}</td>";
              $last_student_name = $row['student_name'];
              $index++;
            } else {
              // Skip printing the serial number and student name for subsequent rows with the same student
              echo "<tr>";
              echo "<td></td>"; // Empty serial number cell
              echo "<td></td>"; // Empty student name cell
            }
            echo "<td>{$row['subject_name']}</td>";
            echo "<td>{$row['marks']}</td>";
            echo "<td><a href='marks_edit.php?edit_id={$row['marks_id']}' class='button'>Edit</a></td>";
            echo "<td><a href='#' onclick='deleteDialog(event, {$row['marks_id']})' class='button'>Delete</a></td>";
            echo "</tr>";
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