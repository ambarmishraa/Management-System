<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// if (isset($_GET['student_id'] )) {
//   $student_id = intval($_GET['student_id']);
//   // Use $student_id to fetch and display the student's marks
//   echo "<script>console.log('Student ID: " . $student_id . "');</script>";
// }

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


// Handle form submission for adding marks
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['student_id']) && !empty($_POST['subject_id']) && isset($_POST['marks'])) {
  $student_id = intval($_POST["student_id"]); // Fetch student_id from the form submission
  $subject_id = intval($_POST["subject_id"]); // Fetch subject_id from the form submission
  $marks = intval($_POST["marks"]); // Fetch marks from the form submission

  $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks) VALUES (?, ?, ?)");
  $stmt->bind_param("iii", $student_id, $subject_id, $marks);

  if ($stmt->execute()) {
      echo "New Marks added successfully";
  } else {
      echo "Error: " . $stmt->error;
  }
  $stmt->close();

  // Redirect to the same page to avoid resubmission on page reload
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
  <link rel="stylesheet" href="../Shared/navbar/navbar.css" />

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
  // Retrieve student data from local storage
  var studentResult = localStorage.getItem('studentResult');
  if (studentResult) {
    var result = JSON.parse(studentResult);
    var studentName = result.student_name;
    var studentId = result.studentid; // Get studentid from local storage
    
    // Set the student name input value
    var studentNameInput = document.getElementById('studentName');
    if (studentNameInput) {
      studentNameInput.value = studentName; // Display the student's name in the input
    }

    // Set the hidden student ID input value
    var studentIdInput = document.getElementById('student_id');
    if (studentIdInput) {
      studentIdInput.value = studentId; // Set the value of hidden input to studentid
    }
    
    // Populate subjects in the dropdown
    var subjectSelect = document.getElementById('subject_id');
    if (subjectSelect) {
      subjectSelect.innerHTML = ''; // Clear existing options

      // Add a default option
      var defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = 'Select a subject';
      subjectSelect.appendChild(defaultOption);

      // Add subjects from local storage
      var subjects = result.subjects || [];
      subjects.forEach(function(subject) {
        if (subject.subject_name !== "Unknown") {
          var option = document.createElement('option');
          option.value = subject.subject_id; // Use subject_id from local storage
          option.textContent = subject.subject_name; // Use subject_name from local storage
          subjectSelect.appendChild(option);
        }
      });
    }
  } else {
    // Handle case where no student data is available
    var studentNameInput = document.getElementById('studentName');
    if (studentNameInput) {
      studentNameInput.value = "";
    }

    // Optionally clear subjects dropdown
    var subjectSelect = document.getElementById('subject_id');
    if (subjectSelect) {
      subjectSelect.innerHTML = ''; // Clear existing options
    }
  }
});

  </script>
</head>

<body>
<?php include '../Shared/navbar/navbar.php'; ?>

  <div class="left-half">
    <div class="inner-container">
      <form action="marks_interface.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 93px;">Add Marks</h1>
          <div class="input">
  <!-- Hidden input field for student ID -->
  <input type="hidden" id="student_id" name="student_id" />
  <input class="option-menu" placeholder="Student Name" id="studentName" readonly />
</div>
          <div class="input">
            <select class="option-menu" id="subject_id" name="subject_id">
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