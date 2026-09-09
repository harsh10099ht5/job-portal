<?php
session_start();

require_once "config/database.php";

/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* =========================
   ADMIN ONLY
========================= */

$role = strtolower($_SESSION["role"] ?? "");

if ($role !== "admin") {
    header("Location: dashboard.php");
    exit();
}


/* =========================
   VARIABLES
========================= */

$message = "";
$error = "";

$title = "";
$company = "";
$location = "";
$salary = "";
$description = "";


/* =========================
   FORM SUBMISSION
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $company = trim($_POST["company"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $description = trim($_POST["description"] ?? "");

    /* Validation */

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

        $admin_id = $_SESSION["user_id"];

        $sql = "INSERT INTO jobs
                (title, company, location, description, salary, employer_id)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "sssssi",
                $title,
                $company,
                $location,
                $description,
                $salary,
                $admin_id
            );

            if ($stmt->execute()) {

                $message = "Job posted successfully.";

                $title = "";
                $company = "";
                $location = "";
                $salary = "";
                $description = "";

            } else {

                $error = "Unable to post job. Please try again.";
            }

            $stmt->close();

        } else {

            $error = "Database error. Please try again later.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Post a Job | Job In India</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f7fb;
    color: #1f2937;
}


/* =========================
   NAVBAR
========================= */

.navbar {
    background: linear-gradient(135deg, #0d6efd, #084298);
    color: white;

    padding: 18px 45px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 3px 15px rgba(0,0,0,0.15);
}

.logo {
    font-size: 23px;
    font-weight: bold;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-badge {
    background: rgba(255,255,255,0.18);
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 13px;
}

.back-btn {
    background: white;
    color: #0d6efd;

    text-decoration: none;

    padding: 9px 17px;

    border-radius: 7px;

    font-weight: bold;

    transition: 0.2s;
}

.back-btn:hover {
    background: #e9f1ff;
}


/* =========================
   MAIN
========================= */

.container {
    max-width: 850px;

    margin: 45px auto;

    padding: 0 20px;
}


/* =========================
   CARD
========================= */

.form-card {
    background: white;

    padding: 38px;

    border-radius: 16px;

    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.form-header {
    margin-bottom: 30px;
}

.form-header h1 {
    font-size: 30px;

    margin-bottom: 8px;

    color: #111827;
}

.form-header p {
    color: #6b7280;

    line-height: 1.6;
}


/* =========================
   ALERTS
========================= */

.success {
    background: #d1e7dd;

    color: #0f5132;

    padding: 14px 16px;

    border-radius: 8px;

    margin-bottom: 22px;

    font-weight: 500;
}

.error {
    background: #f8d7da;

    color: #842029;

    padding: 14px 16px;

    border-radius: 8px;

    margin-bottom: 22px;

    font-weight: 500;
}


/* =========================
   FORM
========================= */

.form-group {
    margin-bottom: 22px;
}

label {
    display: block;

    font-weight: bold;

    margin-bottom: 8px;

    color: #374151;
}

.required {
    color: #dc3545;
}

input,
textarea {
    width: 100%;

    padding: 13px 14px;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    font-size: 15px;

    color: #111827;

    background: white;

    transition: 0.2s;
}

input:focus,
textarea:focus {
    outline: none;

    border-color: #0d6efd;

    box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
}

textarea {
    min-height: 170px;

    resize: vertical;
}


/* =========================
   TWO COLUMN
========================= */

.row {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 18px;
}


/* =========================
   BUTTON
========================= */

.submit-btn {
    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: linear-gradient(135deg, #0d6efd, #084298);

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

    margin-top: 5px;
}

.submit-btn:hover {
    transform: translateY(-1px);

    box-shadow: 0 5px 15px rgba(13,110,253,0.25);
}


/* =========================
   BOTTOM NAVIGATION
========================= */

.bottom-links {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-top: 25px;
}

.dashboard-link {
    color: #0d6efd;

    text-decoration: none;

    font-weight: bold;
}

.dashboard-link:hover {
    text-decoration: underline;
}


/* =========================
   FOOTER
========================= */

.footer {
    text-align: center;

    color: #777;

    font-size: 13px;

    margin-top: 30px;

    padding-bottom: 25px;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 650px) {

    .navbar {
        padding: 15px 20px;
    }

    .logo {
        font-size: 19px;
    }

    .admin-badge {
        display: none;
    }

    .container {
        margin: 25px auto;
    }

    .form-card {
        padding: 25px 20px;
    }

    .form-header h1 {
        font-size: 25px;
    }

    .row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .bottom-links {
        flex-direction: column;
        gap: 15px;
    }
}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <div class="logo">
        Job In India
    </div>

    <div class="nav-right">

        <span class="admin-badge">
            Admin Panel
        </span>

        <a href="dashboard.php" class="back-btn">
            ← Back to Website
        </a>

    </div>

</div>


<!-- =========================
     MAIN
========================= -->

<div class="container">

    <div class="form-card">


        <div class="form-header">

            <h1>
                Post a New Job
            </h1>

            <p>
                Create and publish a new job opportunity
                for students and job seekers.
            </p>

        </div>


        <!-- SUCCESS -->

        <?php if ($message !== ""): ?>

            <div class="success">

                ✓
                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="error">

                ⚠
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">


            <!-- TITLE -->

            <div class="form-group">

                <label>
                    Job Title
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="title"
                    value="<?php echo htmlspecialchars($title); ?>"
                    placeholder="e.g. Software Developer"
                    required
                >

            </div>


            <!-- COMPANY + LOCATION -->

            <div class="row">


                <div class="form-group">

                    <label>
                        Company
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="company"
                        value="<?php echo htmlspecialchars($company); ?>"
                        placeholder="e.g. TCS"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Location
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="location"
                        value="<?php echo htmlspecialchars($location); ?>"
                        placeholder="e.g. Bangalore / Remote"
                        required
                    >

                </div>


            </div>


            <!-- SALARY -->

            <div class="form-group">

                <label>
                    Salary
                </label>

                <input
                    type="text"
                    name="salary"
                    value="<?php echo htmlspecialchars($salary); ?>"
                    placeholder="e.g. ₹5 - ₹8 LPA"
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label>
                    Job Description
                    <span class="required">*</span>
                </label>

                <textarea
                    name="description"
                    placeholder="Describe responsibilities, requirements, qualifications, skills and other important details..."
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="submit-btn">

                Publish Job

            </button>


        </form>


        <!-- LINKS -->

        <div class="bottom-links">

            <a
                href="dashboard.php"
                class="dashboard-link">

                ← Back to Dashboard

            </a>

            <a
                href="jobs.php"
                class="dashboard-link">

                View Published Jobs →

            </a>

        </div>


    </div>


    <div class="footer">

        © <?php echo date("Y"); ?>
        Job In India. All rights reserved.

    </div>

</div>


</body>

</html>