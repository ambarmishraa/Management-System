<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding new relationships
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update'])) {
    $course_id = $_POST['course_id'] ?? null;
    $subject_ids = $_POST['subject_ids'] ?? null;

    if ($course_id && $subject_ids) {
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
        header("Location: sub-course_Interface.php");
        exit();
    }
}

// Handle form submission for updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $edit_course_id = $_POST['edit_course_id'] ?? null;
    $subject_ids = $_POST['subject_ids'] ?? [];

    if ($edit_course_id) {
        // Delete existing subjects for the course
        $delete_sql = "DELETE FROM subcourse WHERE course_id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt) {
            $stmt->bind_param("i", $edit_course_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }

        // Insert updated subjects
        foreach ($subject_ids as $subject_id) {
            $subject_id_int = intval($subject_id);
            $insert_sql = "INSERT INTO subcourse (course_id, subject_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("ii", $edit_course_id, $subject_id_int);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        }
        header("Location: sub-course_Interface.php");
        exit();
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_course_id = intval($_GET['delete_id']);

    $delete_sql = "DELETE FROM subcourse WHERE course_id = ?";
    $stmt = $conn->prepare($delete_sql);
    if ($stmt) {
        $stmt->bind_param("i", $delete_course_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    header("Location: sub-course_Interface.php");
    exit();
}

// Query for fetching the course names and id
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

// Query to fetch subjects names and id
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

// Initialize variables for editing
$edit_course_id = $edit_subject_ids = [];

if (isset($_GET['edit_id'])) {
    $edit_course_id = intval($_GET['edit_id']);

    // Fetch the existing subjects for the selected course
    $sql_edit = "SELECT subject_id FROM subcourse WHERE course_id = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $edit_course_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();

    $edit_subject_ids = [];
    while ($row = $result_edit->fetch_assoc()) {
        $edit_subject_ids[] = $row['subject_id'];
    }
    $stmt->close();
}
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

            <!-- Edit Form -->
            <?php if ($edit_course_id): ?>
                <h2>Edit Course-Subject Relationship</h2>
                <form action="sub-course_Interface.php" method="post">
                    <input type="hidden" name="edit_course_id" value="<?php echo $edit_course_id; ?>">
                    <p>Select subjects to update:</p>
                    <?php
                    // Generate the subject checkboxes
                    $sql_subject = "SELECT id, subject_name FROM subject";
                    $subject_result = $conn->query($sql_subject);

                    if ($subject_result->num_rows > 0) {
                        $subjects = $subject_result->fetch_all(MYSQLI_ASSOC);
                        foreach ($subjects as $row) {
                            $subject_id = $row["id"];
                            $subject_name = $row["subject_name"];
                            $checked = in_array($subject_id, $edit_subject_ids) ? 'checked' : '';
                            echo "<label><input type='checkbox' name='subject_ids[]' value='$subject_id' $checked>$subject_name</label><br>";
                        }
                    } else {
                        echo "<label>No subjects available</label>";
                    }
                    ?>
                    <div class="input">
                        <input class="submit-btn" type="submit" name="update" value="Update Subjects">
                    </div>
                </form>
            <?php endif; ?>
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
                $sql = "SELECT subcourse.id AS subcourse_id, course.id AS course_id, course.course_name, subject.subject_name 
                        FROM subcourse, course, subject 
                        WHERE subcourse.course_id = course.id 
                        AND subcourse.subject_id = subject.id 
                        ORDER BY course.id";

                $result = $conn->query($sql);

                $course_subject_map = [];
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $course_id = $row['course_id'];
                        $course_name = $row['course_name'];
                        $subject_name = $row['subject_name'];
                        $subcourse_id = $row['subcourse_id'];

                        // Initialize course entry if not already set
                        if (!isset($course_subject_map[$course_id])) {
                            $course_subject_map[$course_id] = [
                                'course_name' => $course_name,
                                'subjects' => []
                            ];
                        }

                        // Append subject to the corresponding course
                        $course_subject_map[$course_id]['subjects'][] = $subject_name;
                    }

                    $index = 1;
                    foreach ($course_subject_map as $course_id => $data) {
                        echo "<tr>";
                        echo "<td>{$index}</td>";
                        echo "<td>{$data['course_name']}</td>";
                        echo "<td>" . implode(", ", $data['subjects']) . "</td>";
                        echo "<td><a href='?edit_id={$course_id}' class='button'>Edit</a></td>";
                        echo "<td><a href='#' onclick='deleteDialog(event, {$course_id})' class='button'>Delete</a></td>";
                        echo "</tr>";
                        $index++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html> 