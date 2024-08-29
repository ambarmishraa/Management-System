<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="subject.css" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Subject</title>
  <script>
function deleteDialog(event, id) {
    event.preventDefault();

    if (confirm("Are you sure you want to delete?!")) {
        window.location.href = "?delete_id=" + id;
    } else {
        console.log("Deletion canceled.");
    }
}
</script>
</head>

<body>
  <div class="left-half">
    <div class="inner-container">
      <form action="subject.php" method="post">
        <div class="input-box">
          <h1 style="padding-left: 50px;">Add New Subject</h1>
          <div class="input">
            <input
              class="field"
              placeholder="Subject Name"
              type="text"
              id="subject_name"
              name="subject_name"
              required />
          </div>
          <div class="input">
            <input
              class="field-1"
              placeholder="Subject Code"
              type="text"
              id="subject_code"
              name="subject_code"
              required />
          </div>
          <!-- <div class="input">
            <select class="option-menu" id="course_name" name="course_name">
            <option value="">Choose Course</option>

              <?php echo $options; ?>
            </select>
          </div> -->
          <div class="input">
            <input class="submit-btn" type="submit" value="Add Subject">
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
          <th>Subject Name</th>
          <th>Subject Code</th>
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

        if($conn->connect_error){
          die("Connection Failed : " . $conn->connect_error);
        }

         // Handle deletion
         if (isset($_GET['delete_id'])) {
          $id = intval($_GET['delete_id']);
          $delete_sql = "DELETE FROM subject WHERE id = ?";
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
          $edit_sql = "SELECT * FROM subject WHERE id = ?";
          $stmt = $conn->prepare($edit_sql);
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $stmt->close();

          if ($row) {
            echo '<div class="form-container" id="edit-form-container">';
            echo '<h3>Edit Subject</h3>';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
            echo '<label for="subject_name">Subject Name:</label>';
            echo '<input type="text" name="subject_name" value="' . htmlspecialchars($row["subject_name"]) . '" required>';
            echo '<label for="subject_code">Subject Code :</label>';
            echo '<input type="text" name="subject_code" value="' . htmlspecialchars($row["subject_code"]) . '" required>';
  
            echo '<button type="submit" name="update">Update</button>';
            echo '</form>';
            echo '</div>';
          }
        }


        $sql = "SELECT id, subject_name, subject_code FROM subject";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . $row["id"] . "</td>";
              echo "<td>" . $row["subject_name"] . "</td>";
              echo "<td>" . $row["subject_code"] . "</td>";
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