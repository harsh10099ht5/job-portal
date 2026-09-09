<?php
session_start();

require_once "../config/database.php";

// Check if student is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

// Check job ID
if (!isset($_GET['job_id'])) {
    die("Job not found.");
}

$job_id = intval($_GET['job_id']);
$user_id = $_SESSION['user_id'];

// Get job details
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();

$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    die("Job not found.");
}

// Check if already applied
$check = "SELECT id FROM applications WHERE job_id = ? AND user_id = ?";
$stmt = $conn->prepare($check);
$stmt->bind_param("ii", $job_id, $user_id);
$stmt->execute();

$already_applied = $stmt->get_result()->num_rows > 0;

// Handle application
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_applied) {

    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] != 0) {
        $error = "Please upload your resume.";
    } else {

        $upload_dir = "../uploads/";

        // Create uploads folder if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['resume']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed = ["pdf", "doc", "docx"];

        if (!in_array($file_ext, $allowed)) {
            $error = "Only PDF, DOC and DOCX files are allowed.";
        } else {

            $new_file_name = time() . "_" . $user_id . "_" . $file_name;
            $file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $file_path)) {

                // Save application in database
                $insert = "INSERT INTO applications 
                           (job_id, user_id, resume, status)
                           VALUES (?, ?, ?, 'Applied')";

                $stmt = $conn->prepare($insert);
                $stmt->bind_param("iis", $job_id, $user_id, $file_path);

                if ($stmt->execute()) {
                    $success = "Application submitted successfully!";
                    $already_applied = true;
                } else {
                    $error = "Application failed.";
                }

            } else {
                $error = "Resume upload failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply - Job In India</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1464f4;
        }

        .job-info {
            margin-bottom: 25px;
        }

        .job-info p {
            font-size: 17px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="file"] {
            margin-bottom: 20px;
        }

        button {
            background: #1464f4;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0d4dcc;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .already {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Apply for Job</h1>

    <div class="job-info">
        <h2><?php echo htmlspecialchars($job['title']); ?></h2>

        <p>
            <strong>Company:</strong>
            <?php echo htmlspecialchars($job['company']); ?>
        </p>

        <p>
            <strong>Location:</strong>
            <?php echo htmlspecialchars($job['location']); ?>
        </p>

        <p>
            <strong>Salary:</strong>
            <?php echo htmlspecialchars($job['salary']); ?>
        </p>

        <p>
            <strong>Description:</strong><br>
            <?php echo htmlspecialchars($job['description']); ?>
        </p>
    </div>

    <?php if (isset($success)): ?>

        <div class="success">
            <?php echo $success; ?>
        </div>

    <?php elseif (isset($error)): ?>

        <div class="error">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>


    <?php if ($already_applied): ?>

        <div class="already">
            You have already applied for this job.
        </div>

    <?php else: ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Upload Resume</label>

            <input type="file"
                   name="resume"
                   accept=".pdf,.doc,.docx"
                   required>

            <br>

            <button type="submit">
                Submit Application
            </button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>