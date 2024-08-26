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

// Query for fetching the subject names
$sql_subject = "SELECT id, subject_name FROM subject";
$subject_result = $conn->query($sql_subject);

$subject_options = "";
if ($subject_result->num_rows > 0) {
    while ($row = $subject_result->fetch_assoc()) {
        $subject_options .= "<label><input type='checkbox' name='subject_ids[]' value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["subject_name"]) . "</label><br>";
    }
} else {
    $subject_options = "<label>No subjects available</label>";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST['course_id']);
    $subject_ids = $_POST['subject_ids'] ?? [];

    foreach ($subject_ids as $subject_id) {
        $subject_id_int = intval($subject_id); 
        $insert_sql = "INSERT INTO subcourse (course_id, subject_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt) {
            $stmt->bind_param("ii", $course_id, $subject_id_int);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="sub-course.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subject & Course</title>
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
            <form action="sub-course_Interface.php" method="post">
                <div class="input-box">
                    <h1 style="padding-left: 5px; margin-bottom:60px">Relate Subject & Course</h1>

                    <div class="input">
                        <select class="option-menu" id="course_id" name="course_id">
                            <option value="">Choose Course</option>
                            <?php echo $course_options; ?>
                        </select>
                    </div>
                    <p style="padding-left:35px; color:white;font-size:15px">Choose Subject : </p>
                    <div class="checkbox-input">
                        <?php echo $subject_options; ?>
                    </div>
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
                    <th>Course Name</th>
                    <th>Course Code</th>
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
                    $delete_sql = "DELETE FROM course WHERE id = ?";
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
                    $course_name = $_POST['course_name'];
                    $course_code = $_POST['course_code'];

                    $update_sql = "UPDATE course SET course_name = ?, course_code = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("ssi", $course_name, $course_code, $id);
                    $stmt->execute();
                    $stmt->close();

                    // Reload the page to reflect changes
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
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
                        echo '<div class="form-container">';
                        echo '<h3>Edit Course</h3>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
                        echo '<label for="course_name">Course Name:</label>';
                        echo '<input type="text" name="course_name" value="' . htmlspecialchars($row["course_name"]) . '" required>';
                        echo '<label for="course_code">Course Code :</label>';
                        echo '<input type="text" name="course_code" value="' . htmlspecialchars($row["course_code"]) . '" required>';

                        echo '<button type="submit" name="update">Update</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                }


                $sql = "SELECT id, course_name, course_code FROM course";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["course_name"] . "</td>";
                        echo "<td>" . $row["course_code"] . "</td>";
                        echo '<td><a href="?edit_id=' . $row["id"] . '" class="button">Edit</a></td>';
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
<div class="gap">
    
</div>

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

                if ($conn->connect_error) {
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
                        echo '<div class="form-container">';
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
                        echo '<td><a href="?edit_id=' . $row["id"] . '" class="button">Edit</a></td>';
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