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


// Query to fetch subjects names and id starts
$sql_subject = "SELECT id, subject_name FROM subject";
$subject_result = $conn->query($sql_subject);

$subject_options = "";

if ($subject_result->num_rows > 0) {

    $subjects = $subject_result->fetch_all(MYSQLI_ASSOC);

    foreach ($subjects as $row) {
        $subject_id = $row["id"];
        $subject_name = $row["subject_name"];

        $subject_options .= "<label><input type='checkbox' name='subject_ids[]' value='$subject_id'>$subject_name</label><br>";
    }
} else {
    $subject_options = "<label>No subjects available</label>";
}
// Query to fetch subjects names and id ends



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $subject_ids = $_POST['subject_ids'] ?? null;

    if (is_array($subject_ids)) {
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
    } else {
        echo "No subjects selected or incorrect form input.";
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
                    <th>Subject Name</th>
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
                    $delete_sql = "DELETE FROM subcourse WHERE id = ?";
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
                    $course_id = intval($_POST['course_id']);
                    $subject_id = intval($_POST['subject_id']);

                    $update_sql = "UPDATE subcourse SET course_id = ?, subject_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("iii", $course_id, $subject_id, $id);
                    $stmt->execute();
                    $stmt->close();

                    // Reload the page to reflect changes
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                // Handle edit request
                if (isset($_GET['edit_id'])) {
                    $id = intval($_GET['edit_id']);
                    $edit_sql = "SELECT * FROM subcourse WHERE id = ?";
                    $stmt = $conn->prepare($edit_sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    if ($row) {
                        $course_id = $row['course_id'];
                        $subject_id = $row['subject_id'];

                        echo '<div class="form-container">';
                        echo '<h3>Edit Subcourse</h3>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="id" value="' . $id . '">';

                        // Fetch course options
                        $course_options = "";
                        $course_sql = "SELECT id, course_name FROM course";
                        $course_result = $conn->query($course_sql);
                        if ($course_result->num_rows > 0) {
                            while ($course_row = $course_result->fetch_assoc()) {
                                $selected = $course_row['id'] == $course_id ? ' selected' : '';
                                $course_options .= "<option value='{$course_row['id']}'{$selected}>{$course_row['course_name']}</option>";
                            }
                        }
                        echo '<label for="course_id">Course Name:</label>';
                        echo '<select name="course_id" required>';
                        echo '<option value="">Choose Course</option>';
                        echo $course_options;
                        echo '</select>';

                        // Fetch subject options
                        $subject_options = "";
                        $subject_sql = "SELECT id, subject_name FROM subject";
                        $subject_result = $conn->query($subject_sql);
                        if ($subject_result->num_rows > 0) {
                            while ($subject_row = $subject_result->fetch_assoc()) {
                                $selected = $subject_row['id'] == $subject_id ? ' selected' : '';
                                $subject_options .= "<option value='{$subject_row['id']}'{$selected}>{$subject_row['subject_name']}</option>";
                            }
                        }
                        echo '<label for="subject_id">Subject Code:</label>';
                        echo '<select name="subject_id" required>';
                        echo '<option value="">Choose Subject</option>';
                        echo $subject_options;
                        echo '</select>';

                        echo '<button type="submit" name="update">Update</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                }



                $subcourse_sql = "SELECT id, course_id, subject_id FROM subcourse";
                $subcourse_result = $conn->query($subcourse_sql);


                $course_sql = "SELECT id, course_name FROM course";
                $course_result = $conn->query($course_sql);
                $course_names = [];
                if ($course_result->num_rows > 0) {

                    $courses = $course_result->fetch_all(MYSQLI_ASSOC);
                    foreach ($courses as $row) {
                        $course_id = $row['id'];
                        $course_name = $row['course_name'];
                        $course_names[$course_id] = $course_name;
                    }
                }


                $subject_sql = "SELECT id, subject_name FROM subject";
                $subject_result = $conn->query($subject_sql);
                $subject_names = [];
                if ($subject_result->num_rows > 0) {

                    $subjects = $subject_result->fetch_all(MYSQLI_ASSOC);
                    foreach ($subjects as $row) {
                        $subject_id = $row['id'];
                        $subject_name = $row['subject_name'];
                        $subject_names[$subject_id] = $subject_name;
                    }
                }


                if ($subcourse_result->num_rows > 0) {
                    foreach ($subcourse_result as $row) {
                        $id = $row['id'];
                        $course_id = $row['course_id'];
                        $subject_id = $row['subject_id'];


                        if (isset($course_names[$course_id])) {
                            $course_name = $course_names[$course_id];
                        } else {
                            $course_name = 'Unknown';
                        }


                        if (isset($subject_names[$subject_id])) {
                            $subject_name = $subject_names[$subject_id];
                        } else {
                            $subject_name = 'Unknown';
                        }

                        echo "<tr>";
                        echo "<td>" . $id . "</td>";
                        echo "<td>" . $course_name . "</td>";
                        echo "<td>" . $subject_name . "</td>";
                        echo '<td><a href="?edit_id=' . $id . '" class="button">Edit</a></td>';
                        echo '<td><a href="#" onclick="deleteDialog(event, ' . $id . ')" class="button">Delete</a></td>';
                        echo '</tr>';


                        // echo "Course ID: $course_id - Course Name: $course_name, Subject ID: $subject_id - Subject Name: $subject_name<br>";
                    }
                } else {
                    echo "No records found.";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>