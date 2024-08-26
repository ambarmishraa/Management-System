<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT course_name FROM course";
$result = $conn->query($sql);

$options = "";
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $options .= "<option value='" . htmlspecialchars($row["course_name"]) . "'>" . htmlspecialchars($row["course_name"]) . "</option>";
  }
} else {
  $options = "<option value=''>No courses available</option>";
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="coordinator.css" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coordinator</title>
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
        console.log("Deletion canceled.");
    }
}
</script>
</head>

<body>
  <div class="left-half">
    <div class="inner-container">
      <form action="coordinator.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 13px;">Add New Co-Ordinator</h1>
          <div class="input">
            <input
              class="field"
              placeholder="Co-Ordinator Name"
              type="text"
              id="coordinator_name"
              name="coordinator_name"
              required />
          </div>
          <div class="input">
            <input
              class="field-1"
              placeholder="Co-Ordinator Email"
              type="email"
              id="coordinator_email"
              name="coordinator_email"
              required />
          </div>
          <!-- <div class="input">
            <select class="option-menu" id="course_name" name="course_name">
            <option value="">Choose Course</option>

              <?php echo $options; ?>
            </select>
          </div> -->
          <div class="input">
            <input class="submit-btn" type="submit" value="Add Co-Ordinator">
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
          <th>Coordinator Name</th>
          <th>Coordinator Email</th>
          <!-- <th>Course Name</th> -->
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
          die("Connection failed: " . $conn->connect_error);
        }

        // Handle deletion
        if (isset($_GET['delete_id'])) {
          $id = intval($_GET['delete_id']);
          $delete_sql = "DELETE FROM coordinator WHERE id = ?";
          $stmt = $conn->prepare($delete_sql);
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $stmt->close();

          // Reload the page to reflect changes
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // Handle edit form submission
        if (isset($_POST['update'])) {
          $id = intval($_POST['id']);
          $name = $_POST['name'];
          $email = $_POST['email'];
          // $course = $_POST['course'];

          $update_sql = "UPDATE coordinator SET name = ?, email = ?, course_name = ? WHERE id = ?";
          $stmt = $conn->prepare($update_sql);
          $stmt->bind_param("sssi", $name, $email, $course, $id);
          $stmt->execute();
          $stmt->close();

          // Reload the page to reflect changes
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        }

        // SQL query to fetch all data from the coordinator table
        $sql = "SELECT * FROM coordinator";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          // Output data for each row
          $serialNo = 1; // Initialize serial number
          while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $serialNo++ . '</td>';
            echo '<td>' . htmlspecialchars($row["name"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["email"]) . '</td>';
            // echo '<td>' . htmlspecialchars($row["course_name"]) . '</td>';
            echo '<td><a href="?edit_id=' . $row["id"] . '" class="button">Edit</a></td>';
            echo '<td><a href="#" onclick="deleteDialog(event, ' . $row["id"] . ')" class="button">Delete</a></td>';
            echo '</tr>';
          }
        } else {
          echo '<tr><td colspan="6">No results found</td></tr>';
        }

        // Handle edit request
        if (isset($_GET['edit_id'])) {
          $id = intval($_GET['edit_id']);
          $edit_sql = "SELECT * FROM coordinator WHERE id = ?";
          $stmt = $conn->prepare($edit_sql);
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $stmt->close();

          if ($row) {
            echo '<div class="form-container">';
            echo '<h3>Edit Coordinator</h3>';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
            echo '<label for="name">Coordinator Name:</label>';
            echo '<input type="text" name="name" value="' . htmlspecialchars($row["name"]) . '" required>';
            echo '<label for="email">Coordinator Email:</label>';
            echo '<input type="email" name="email" value="' . htmlspecialchars($row["email"]) . '" required>';
            echo '<label for="course">Course Name:</label>';
            // echo '<input type="text" name="course" value="' . htmlspecialchars($row["course_name"]) . '" required>';
            echo '<button type="submit" name="update">Update</button>';
            echo '</form>';
            echo '</div>';
          }
        }
        $conn->close();
        ?>
      </tbody>
    </table>
  </div>
</body>

</html>