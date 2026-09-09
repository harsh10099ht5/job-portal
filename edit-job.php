    <?php
session_start();

require_once "config/database.php";

// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$role = strtolower($_SESSION["role"] ?? "");

// Only employer/admin
if ($role !== "employer" && $role !== "admin") {
    die("Access denied.");
}


// --------------------------------------------------
// Get Job ID
// --------------------------------------------------

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid job ID.");
}

$job_id = intval($_GET["id"]);


// --------------------------------------------------
// Fetch Job
// --------------------------------------------------

$sql = "
    SELECT *
    FROM jobs
    WHERE id = ?
    AND employer_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $job_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Job not found or you do not have permission to edit it.");
}

$job = $result->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// Update Job
// --------------------------------------------------

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $company = trim($_POST["company"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $description = trim($_POST["description"] ?? "");


    // Validation

    if (
        empty($title) ||
        empty($company) ||
        empty($location) ||
        empty($description)
    ) {

        $error = "Please fill all required fields.";

    } elseif (strlen($title) < 3) {

        $error = "Job title must contain at least 3 characters.";

    } elseif (strlen($description) < 20) {

        $error = "Job description must contain at least 20 characters.";

    } else {

        // Update only owner's job

        $update_sql = "
            UPDATE jobs
            SET
                title = ?,
                company = ?,
                location = ?,
                salary = ?,
                description = ?
            WHERE id = ?
            AND employer_id = ?
        ";

        $update_stmt = $conn->prepare($update_sql);

        $update_stmt->bind_param(
            "sssssii",
            $title,
            $company,
            $location,
            $salary,
            $description,
            $job_id,
            $user_id
        );

        if ($update_stmt->execute()) {

            $message = "Job updated successfully!";

            // Update displayed values
            $job["title"] = $title;
            $job["company"] = $company;
            $job["location"] = $location;
            $job["salary"] = $salary;
            $job["description"] = $description;

        } else {

            $error = "Unable to update job.";

        }

        $update_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Edit Job - Job In India</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f8;
            color: #222;
        }


        /* Navbar */

        .navbar {
            background: #0d6efd;
            color: white;

            padding: 18px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }


        /* Container */

        .container {
            max-width: 750px;
            margin: 40px auto;
            padding: 20px;
        }


        /* Form Card */

        .form-card {
            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 4px 20px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }


        /* Messages */

        .success {
            background: #d1e7dd;
            color: #0f5132;

            padding: 13px;

            border-radius: 7px;

            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;

            padding: 13px;

            border-radius: 7px;

            margin-bottom: 20px;
        }


        /* Form */

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }

        .required {
            color: #dc3545;
        }

        input,
        textarea {
            width: 100%;

            padding: 13px;

            border: 1px solid #ced4da;

            border-radius: 7px;

            font-size: 15px;

            outline: none;
        }

        input:focus,
        textarea:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 2px rgba(13,110,253,0.1);
        }

        textarea {
            min-height: 160px;

            resize: vertical;
        }


        /* Update Button */

        .update-btn {
            width: 100%;

            padding: 14px;

            background: #0d6efd;

            color: white;

            border: none;

            border-radius: 7px;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        .update-btn:hover {
            background: #0b5ed7;
        }


        /* Back */

        .back {
            display: inline-block;

            margin-top: 20px;

            color: #0d6efd;

            text-decoration: none;

            font-weight: bold;
        }

        .back:hover {
            text-decoration: underline;
        }


        @media (max-width: 600px) {

            .navbar {
                padding: 15px 20px;
            }

            .container {
                margin: 20px auto;
            }

            .form-card {
                padding: 25px 20px;
            }

        }

    </style>

</head>


<body>


<!-- Navbar -->

<div class="navbar">

    <div class="brand">
        Job In India
    </div>

    <a href="dashboard.php">
        Dashboard
    </a>

</div>


<!-- Main -->

<div class="container">

    <div class="form-card">

        <h1>
            Edit Job
        </h1>

        <p class="subtitle">
            Update your job posting details.
        </p>


        <?php if ($message !== ""): ?>

            <div class="success">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- Job Title -->

            <div class="form-group">

                <label>
                    Job Title
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="title"
                    value="<?php echo htmlspecialchars($job["title"]); ?>"
                    required>

            </div>


            <!-- Company -->

            <div class="form-group">

                <label>
                    Company
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="company"
                    value="<?php echo htmlspecialchars($job["company"]); ?>"
                    required>

            </div>


            <!-- Location -->

            <div class="form-group">

                <label>
                    Location
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="location"
                    value="<?php echo htmlspecialchars($job["location"]); ?>"
                    required>

            </div>


            <!-- Salary -->

            <div class="form-group">

                <label>
                    Salary
                </label>

                <input
                    type="text"
                    name="salary"
                    value="<?php echo htmlspecialchars($job["salary"]); ?>"
                    placeholder="e.g. ₹5-8 LPA">

            </div>


            <!-- Description -->

            <div class="form-group">

                <label>
                    Job Description
                    <span class="required">*</span>
                </label>

                <textarea
                    name="description"
                    required><?php echo htmlspecialchars($job["description"]); ?></textarea>

            </div>


            <button
                type="submit"
                class="update-btn">

                Update Job

            </button>


        </form>


        <a
            href="my-jobs.php"
            class="back">

            ← Back to My Jobs

        </a>

    </div>

</div>


</body>

</html>