<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT id, name FROM coordinator";
$result = $conn->query($sql);

$options = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
    }
} else {
    $options = "<option value=''>No coordinators available</option>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="course.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Course</title>
    <script>
        function deleteDialog(event,id){
            event.preventDefault();

            if(confirm ("Are you sure you want to delete?!")){
                window.location.href = "?delete_id=" + id;
            }
            else{
                console.log("Deletion cancelled.");
            }
        }
    </script>
</head>

<body>
    <div class="left-half">
        <div class="inner-container">
            <form action="course.php" method="post">
                <div class="input-box">
                    <h1 style="padding-left: 50px">Add New Course</h1>
                    <div class="input">
                        <input
                            class="field"
                            placeholder="Course Name"
                            type="text"
                            id="course_name"
                            name="course_name"
                            required />
                    </div>
                    <div class="input">
                        <input
                            class="field-1"
                            placeholder="Course Code"
                            type="text"
                            id="course_code"
                            name="course_code"
                            required />
                    </div>
                    <div class="input">
                        <select class="option-menu" id="coordinator_id" name="coordinator_id">
                        <option value="">Choose Co-Ordinator</option>
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="input">
                        <input class="submit-btn" type="submit" value="Add Course">
                    </div>
                    <!-- <a href="#right-half">
                        <h4 style="color: red; margin-left:10px;">View Table</h4>
                    </a> -->
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
                    <th>Course Name</th>
                    <th>Course Code</th>
                    <th>Coordinator Name</th>
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

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Handle deletion
                if (isset($_GET['delete_id'])) {
                    $id = intval($_GET['delete_id']);
                    $delete_sql = "DELETE FROM course WHERE id = ?";
                    $stmt = $conn->prepare($delete_sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                // Handle edit form submission
                if (isset($_POST['update'])) {
                    $id = intval($_POST['id']);
                    $course_name = $_POST['course_name'];
                    $course_code = $_POST['course_code'];
                    $coordinator_id = $_POST['coordinator'];

                    $update_sql = "UPDATE course SET course_name = ?, course_code = ?, coordinator_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("sssi", $course_name, $course_code, $coordinator_id, $id);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                // SQL query to fetch all data from the course table with coordinator names
                $sql = "SELECT course.id, course.course_name, course.course_code, coordinator.name AS coordinator_name
        FROM course
        JOIN coordinator ON course.coordinator_id = coordinator.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data for each row
                    $serialNo = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $serialNo++ . '</td>';
                        echo '<td>' . htmlspecialchars($row["course_name"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["course_code"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["coordinator_name"]) . '</td>';
                        // echo '<td><a href="?edit_id=' . $row["id"] . '" class="button">Edit</a></td>';
            echo '<td><a href="?edit_id=' . $row["id"] . '#edit-form-container" class="button">Edit</a></td>';

                        echo '<td><a href="#" onclick="deleteDialog(event, ' . $row["id"] . ')" class="button">Delete</a></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No results found</td></tr>';
                }

                // Handle edit request
                if (isset($_GET['edit_id'])) {
                    $id = intval($_GET['edit_id']);
                    $edit_sql = "SELECT * FROM course WHERE id = ?";
                    $stmt = $conn->prepare($edit_sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    if ($row) {
                        // Fetch coordinator names for the dropdown
                        $coordinator_sql = "SELECT id, name FROM coordinator";
                        $coordinator_result = $conn->query($coordinator_sql);

                        echo '<div class="form-container" id="edit-form-container">';
                        echo '<h3>Edit Course</h3>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
                        echo '<label for="course_name">Course Name:</label>';
                        echo '<input type="text" name="course_name" value="' . htmlspecialchars($row["course_name"]) . '" required>';
                        echo '<label for="course_code">Course Code:</label>';
                        echo '<input type="text" name="course_code" value="' . htmlspecialchars($row["course_code"]) . '" required>';
                        echo '<label for="coordinator_id">Coordinator Name:</label>';
                        echo '<select name="coordinator" required>';
                        while ($coordinator_row = $coordinator_result->fetch_assoc()) {
                            $selected = ($coordinator_row["id"] == $row["coordinator_id"]) ? 'selected' : '';
                            echo '<option value="' . $coordinator_row["id"] . '" ' . $selected . '>' . htmlspecialchars($coordinator_row["name"]) . '</option>';
                        }
                        echo '</select>';
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