<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch course data
$sql_course = "SELECT id, course_name FROM course";
$course_result = $conn->query($sql_course);
$course_options = "";
if ($course_result->num_rows > 0) {
  while ($row = $course_result->fetch_assoc()) {
    $course_id = $row["id"];
    $course_name = $row["course_name"];
    $course_options .= "<option value='$course_id'>$course_name</option>";
  }
} else {
  $course_options = "<option value=''>No courses available</option>";
}

// Fetch subject data
$sql_subject = "SELECT id, subject_name FROM subject";
$subject_result = $conn->query($sql_subject);
$subjectArray = [];
if ($subject_result->num_rows > 0) {
  while ($row = $subject_result->fetch_assoc()) {
    $subjectArray[$row['id']] = $row['subject_name'];
  }
}

// Fetch subcourse data
$courseToSubcourse = [];
$sql_subcourse = "SELECT course_id, id, subject_id FROM subcourse";
$subcourse_result = $conn->query($sql_subcourse);
if ($subcourse_result->num_rows > 0) {
  while ($row = $subcourse_result->fetch_assoc()) {
    $course_id = $row['course_id'];
    $subcourse_id = $row['id'];
    $subject_id = $row['subject_id'];

    if (!isset($courseToSubcourse[$course_id])) {
      $courseToSubcourse[$course_id] = [];
    }

    $subject_name = isset($subjectArray[$subject_id]) ? $subjectArray[$subject_id] : 'Unknown';
    $courseToSubcourse[$course_id][] = [
      'id' => $subcourse_id,
      'course_id' => $course_id,
      'subject_id' => $subject_id,
      'subject_name' => $subject_name
    ];
  }
}

// Fetch student data
$studentsArray = [];
$sql_student = "SELECT id, student_name, course_id FROM student";
$student_result = $conn->query($sql_student);
if ($student_result->num_rows > 0) {
  while ($row = $student_result->fetch_assoc()) {
    $studentsArray[$row['id']] = [
      'studentid' => $row['id'],
      'student_name' => $row['student_name'],
      'course_id' => $row['course_id']
    ];
  }
}

// Map students to subjects
$studentsWithSubjects = [];
foreach ($studentsArray as $studentId => $student) {
  $courseId = $student['course_id'];

  if (isset($courseToSubcourse[$courseId])) {
    $studentsWithSubjects[$studentId] = [
      'studentid' => $student['studentid'],
      'student_name' => $student['student_name'],
      'subjects' => $courseToSubcourse[$courseId]
    ];
  } else {
    $studentsWithSubjects[$studentId] = [
      'student_name' => $student['student_name'],
      'subjects' => [] // No subjects found for the course
    ];
  }
}

$studentsWithSubjectsJson = json_encode($studentsWithSubjects);
$subjectArrayJson = json_encode($subjectArray);

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
   document.addEventListener("DOMContentLoaded", function() {
  // Ensure studentsWithSubjects and subjectArray are defined
  var studentsWithSubjects = <?php echo $studentsWithSubjectsJson; ?>;
  var subjectArray = <?php echo $subjectArrayJson; ?>;

  if (typeof studentsWithSubjects !== 'object' || typeof subjectArray !== 'object') {
    console.error('studentsWithSubjects or subjectArray is not defined or is not an object');
    return;
  }

  // Log data to the console for debugging
  console.log("Students with Subjects:", studentsWithSubjects);
  // console.log("Subject Array:", subjectArray);

  // Function to handle "Add Marks" click event
  window.addMarks = function(studentId) {
    if (studentsWithSubjects.hasOwnProperty(studentId)) {
      var student = studentsWithSubjects[studentId];
      var studentName = student.student_name;
      var subjects = student.subjects.map(sub => ({
        subject_id: sub.subject_id,
        subject_name: sub.subject_name
      }));

      // Create a new object with the student's ID, name, and subjects
      var result = {
        studentid: studentId,
        student_name: studentName,
        subjects: subjects
      };

      // Save the result and subject array to local storage
      localStorage.setItem('studentResult', JSON.stringify(result));
      // localStorage.setItem('subjectArray', JSON.stringify(subjectArray));

      // Log the result to the console
      console.log("Student Details:", result);
      console.log("Subject Array in Local Storage:", subjectArray);

      // Navigate to the new PHP page
      window.location.href = '/Marks/marks_interface.php?id=' + encodeURIComponent(studentId);
    } else {
      console.log("Student with ID " + studentId + " not found.");
    }
  };});
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
          <th>Student Email</th>
          <th>Student Course</th>
          <th>Edit</th>
          <th>Delete</th>
          <th>Add Marks</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Reconnect for table display
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
          die("Connection Failed : " . $conn->connect_error);
        }

        // Handle deletion
        if (isset($_GET['delete_id'])) {
          $id = intval($_GET['delete_id']);
          $delete_sql = "DELETE FROM student WHERE id = $id";

          if ($conn->query($delete_sql) === TRUE) {
            echo "Record deleted successfully";
          } else {
            echo "Error deleting record: " . $conn->error;
          }

          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // Handle edit form submission
        if (isset($_POST['update'])) {
          $id = intval($_POST['id']);
          $student_name = $_POST['student_name'];
          $course_id = $_POST['course_id'];

          $update_sql = "UPDATE student SET student_name = ?, course_id = ? WHERE id = ?";
          $stmt = $conn->prepare($update_sql);
          $stmt->bind_param("sii", $student_name, $course_id, $id);
          $stmt->execute();
          $stmt->close();

          // Reload the page to reflect changes
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // Handle edit request
        if (isset($_GET['edit_id'])) {
          $id = intval($_GET['edit_id']);
          $edit_sql = "SELECT * FROM student WHERE id = $id";
          $result = $conn->query($edit_sql);

          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
          }

          if ($row) {
            echo '<div class="form-container" id="#edit-form-container">';
            echo '<h3>Edit Student</h3>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
            echo '<div class="input-box">';
            echo '<input type="text" name="student_name" value="' . $row['student_name'] . '" required>';
            echo '<select name="course_id" required>';

            $course_result = $conn->query("SELECT id, course_name FROM course");
            while ($course = $course_result->fetch_assoc()) {
              $selected = $course['id'] == $row['course_id'] ? 'selected' : '';
              echo '<option value="' . $course['id'] . '" ' . $selected . '>' . $course['course_name'] . '</option>';
            }

            echo '</select>';
            echo '<button type="submit" name="update">Update</button>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
          }
        }

        // Fetch and display students
        $sql = "SELECT s.id, s.student_name, s.student_email, c.course_name 
                FROM student s 
                JOIN course c ON s.course_id = c.id";
        $result = $conn->query($sql);
        $serial_no = 1;

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $serial_no++ . "</td>";
            echo "<td>" . $row["student_name"] . "</td>";
            echo "<td>" . $row["student_email"] . "</td>";
            echo "<td>" . $row["course_name"] . "</td>";
            echo '<td><a href="?edit_id=' . $row["id"] . '">Edit</a></td>';
            echo '<td><a href="?delete_id=' . $row["id"] . '">Delete</a></td>';
            echo '<td><button onclick="addMarks(' . $row["id"] . ')">Add Marks</button></td>';
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='7'>No students found</td></tr>";
        }

        $conn->close();
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>
