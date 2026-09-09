<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$error = "";

/*
|--------------------------------------------------------------------------
| Get Job ID
|--------------------------------------------------------------------------
*/

$job_id = isset($_GET["job_id"]) ? (int)$_GET["job_id"] : 0;

if ($job_id <= 0) {
    header("Location: jobs.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Get Job Details
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, title, company, location, salary
    FROM jobs
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error.");
}

$stmt->bind_param("i", $job_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: jobs.php");
    exit();
}

$job = $result->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Existing Application
|--------------------------------------------------------------------------
*/

$check_sql = "
    SELECT id
    FROM applications
    WHERE user_id = ?
    AND job_id = ?
    LIMIT 1
";

$check_stmt = $conn->prepare($check_sql);

if ($check_stmt) {

    $check_stmt->bind_param("ii", $user_id, $job_id);
    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "You have already applied for this job.";
    }

    $check_stmt->close();
}

/*
|--------------------------------------------------------------------------
| Handle Resume Upload
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === "") {

    if (!isset($_FILES["resume"])) {

        $error = "Please select your resume.";

    } else {

        $file = $_FILES["resume"];

        /*
        |--------------------------------------------------------------------------
        | Upload Error
        |--------------------------------------------------------------------------
        */

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "Unable to upload the file. Please try again.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */

            $original_name = $file["name"];
            $tmp_name = $file["tmp_name"];
            $file_size = $file["size"];

            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            /*
            |--------------------------------------------------------------------------
            | Allowed Extensions
            |--------------------------------------------------------------------------
            */

            $allowed_extensions = [
                "pdf",
                "doc",
                "docx"
            ];

            /*
            |--------------------------------------------------------------------------
            | Maximum Size - 5 MB
            |--------------------------------------------------------------------------
            */

            $max_size = 5 * 1024 * 1024;

            /*
            |--------------------------------------------------------------------------
            | Validation
            |--------------------------------------------------------------------------
            */

            if (!in_array($extension, $allowed_extensions, true)) {

                $error = "Only PDF, DOC and DOCX files are allowed.";

            } elseif ($file_size > $max_size) {

                $error = "Resume size must be less than 5 MB.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Create Upload Directory
                |--------------------------------------------------------------------------
                */

                $upload_dir = "uploads/resumes/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                /*
                |--------------------------------------------------------------------------
                | Generate Secure File Name
                |--------------------------------------------------------------------------
                */

                $new_file_name =
                    "resume_" .
                    $user_id .
                    "_" .
                    $job_id .
                    "_" .
                    time() .
                    "." .
                    $extension;

                $file_path = $upload_dir . $new_file_name;

                /*
                |--------------------------------------------------------------------------
                | Move File
                |--------------------------------------------------------------------------
                */

                if (move_uploaded_file($tmp_name, $file_path)) {

                    /*
                    |--------------------------------------------------------------------------
                    | Insert Application
                    |--------------------------------------------------------------------------
                    */

                    $insert_sql = "
                        INSERT INTO applications
                        (
                            user_id,
                            job_id,
                            resume,
                            status,
                            applied_at
                        )
                        VALUES (?, ?, ?, 'Applied', NOW())
                    ";

                    $insert_stmt = $conn->prepare($insert_sql);

                    if (!$insert_stmt) {

                        $error = "Database error. Application could not be submitted.";

                        /*
                        |--------------------------------------------------------------------------
                        | Delete Uploaded File If DB Insert Fails
                        |--------------------------------------------------------------------------
                        */

                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }

                    } else {

                        $insert_stmt->bind_param(
                            "iis",
                            $user_id,
                            $job_id,
                            $file_path
                        );

                        if ($insert_stmt->execute()) {

                            $message =
                                "Application submitted successfully.";

                        } else {

                            $error =
                                "Application could not be submitted.";

                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                        }

                        $insert_stmt->close();
                    }

                } else {

                    $error =
                        "Unable to save your resume. Please try again.";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Apply for Job - Job In India</title>

<style>

/* =========================================================
   RESET
========================================================= */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family:
        Inter,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Roboto,
        Arial,
        sans-serif;

    background: #f7f9fc;
    color: #172033;
    line-height: 1.5;
}

/* =========================================================
   NAVBAR
========================================================= */

.navbar {
    height: 72px;
    background: #ffffff;

    border-bottom: 1px solid #e7eaf0;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 6%;

    position: sticky;
    top: 0;
    z-index: 100;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;

    text-decoration: none;
    color: #111827;

    font-size: 21px;
    font-weight: 700;
    letter-spacing: -0.4px;
}

.brand-mark {
    width: 34px;
    height: 34px;

    border-radius: 9px;

    background: #1769ff;
    color: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 17px;
    font-weight: 800;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-links a {
    text-decoration: none;
    color: #4b5563;

    padding: 9px 14px;

    border-radius: 8px;

    font-size: 14px;
    font-weight: 600;
}

.nav-links a:hover {
    background: #f1f5f9;
    color: #1769ff;
}

/* =========================================================
   MAIN
========================================================= */

.page {
    max-width: 1100px;
    margin: 0 auto;

    padding: 55px 25px 80px;
}

/* =========================================================
   HEADER
========================================================= */

.page-header {
    margin-bottom: 28px;
}

.eyebrow {
    color: #1769ff;

    font-size: 13px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: 1px;

    margin-bottom: 8px;
}

.page-header h1 {
    font-size: 34px;
    letter-spacing: -1px;

    margin-bottom: 7px;
}

.page-header p {
    color: #6b7280;
    font-size: 15px;
}

/* =========================================================
   LAYOUT
========================================================= */

.application-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        390px;

    gap: 25px;

    align-items: start;
}

/* =========================================================
   JOB CARD
========================================================= */

.job-card,
.upload-card {
    background: #ffffff;

    border: 1px solid #e5e9f0;

    border-radius: 16px;

    box-shadow:
        0 8px 30px rgba(20, 35, 60, 0.06);
}

.job-card {
    padding: 32px;
}

.job-label {
    color: #6b7280;

    font-size: 12px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .7px;

    margin-bottom: 8px;
}

.job-title {
    font-size: 27px;
    line-height: 1.2;

    margin-bottom: 8px;
}

.company {
    color: #1769ff;

    font-size: 16px;
    font-weight: 700;

    margin-bottom: 25px;
}

.job-info {
    display: flex;
    flex-wrap: wrap;

    gap: 10px;

    margin-bottom: 28px;
}

.info-item {
    background: #f5f7fb;

    border: 1px solid #e7ebf2;

    padding: 8px 12px;

    border-radius: 8px;

    color: #4b5563;

    font-size: 13px;
    font-weight: 600;
}

.divider {
    height: 1px;
    background: #edf0f5;

    margin: 25px 0;
}

.job-note {
    color: #6b7280;

    font-size: 14px;
    line-height: 1.7;
}

/* =========================================================
   UPLOAD CARD
========================================================= */

.upload-card {
    padding: 30px;
}

.upload-card h2 {
    font-size: 21px;

    margin-bottom: 6px;
}

.upload-subtitle {
    color: #6b7280;

    font-size: 14px;

    margin-bottom: 23px;
}

/* =========================================================
   ALERTS
========================================================= */

.alert {
    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 18px;

    font-size: 14px;
    font-weight: 600;
}

.success {
    background: #ecfdf3;
    color: #087443;

    border: 1px solid #bbf7d0;
}

.error {
    background: #fff1f2;
    color: #be123c;

    border: 1px solid #fecdd3;
}

/* =========================================================
   FILE UPLOAD
========================================================= */

.upload-area {
    border: 1.5px dashed #b8c2d1;

    border-radius: 12px;

    padding: 28px 18px;

    text-align: center;

    background: #fafbfd;

    transition: .2s;
}

.upload-area:hover {
    border-color: #1769ff;
    background: #f7faff;
}

.upload-icon {
    width: 46px;
    height: 46px;

    margin: 0 auto 13px;

    border-radius: 12px;

    background: #eaf1ff;

    color: #1769ff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 21px;
    font-weight: 700;
}

.upload-area strong {
    display: block;

    font-size: 14px;

    margin-bottom: 5px;
}

.upload-area span {
    color: #8a94a6;

    font-size: 12px;
}

input[type="file"] {
    width: 100%;

    margin-top: 18px;

    font-size: 13px;

    color: #4b5563;
}

/* =========================================================
   BUTTON
========================================================= */

.submit-btn {
    width: 100%;

    margin-top: 20px;

    padding: 13px 18px;

    border: none;
    border-radius: 9px;

    background: #1769ff;
    color: #ffffff;

    font-size: 15px;
    font-weight: 700;

    cursor: pointer;

    transition: .2s;
}

.submit-btn:hover {
    background: #0d5be0;

    transform: translateY(-1px);
}

.submit-btn:disabled {
    background: #9db8ee;
    cursor: not-allowed;
}

/* =========================================================
   SECURITY NOTE
========================================================= */

.security-note {
    display: flex;

    gap: 9px;

    margin-top: 17px;

    padding: 12px;

    border-radius: 8px;

    background: #f8fafc;

    color: #64748b;

    font-size: 12px;

    line-height: 1.5;
}

/* =========================================================
   BACK BUTTON
========================================================= */

.back-btn {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-top: 22px;

    color: #4b5563;

    text-decoration: none;

    font-size: 14px;
    font-weight: 600;
}

.back-btn:hover {
    color: #1769ff;
}

/* =========================================================
   FOOTER
========================================================= */

.footer {
    text-align: center;

    color: #8a94a6;

    font-size: 12px;

    margin-top: 45px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 850px) {

    .application-layout {
        grid-template-columns: 1fr;
    }

    .upload-card {
        max-width: 650px;
    }
}

@media (max-width: 600px) {

    .navbar {
        height: 64px;
        padding: 0 18px;
    }

    .brand {
        font-size: 18px;
    }

    .brand-mark {
        width: 30px;
        height: 30px;
    }

    .nav-links a {
        padding: 7px 9px;
        font-size: 13px;
    }

    .page {
        padding: 35px 16px 60px;
    }

    .page-header h1 {
        font-size: 28px;
    }

    .job-card,
    .upload-card {
        padding: 23px;
        border-radius: 13px;
    }

    .job-title {
        font-size: 23px;
    }
}

</style>

</head>

<body>

<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <a href="index.html" class="brand">

        <span class="brand-mark">
            J
        </span>

        Job In India

    </a>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="page">

    <div class="page-header">

        <div class="eyebrow">
            Career Application
        </div>

        <h1>
            Apply for this position
        </h1>

        <p>
            Submit your resume and take the next step in your career.
        </p>

    </div>


    <div class="application-layout">


        <!-- =================================================
             JOB INFORMATION
        ================================================== -->

        <section class="job-card">

            <div class="job-label">
                Job Opportunity
            </div>

            <h2 class="job-title">

                <?php
                echo htmlspecialchars($job["title"]);
                ?>

            </h2>

            <div class="company">

                <?php
                echo htmlspecialchars($job["company"]);
                ?>

            </div>


            <div class="job-info">

                <div class="info-item">

                    Location:
                    <?php
                    echo htmlspecialchars($job["location"]);
                    ?>

                </div>

                <?php if (!empty($job["salary"])): ?>

                    <div class="info-item">

                        Salary:
                        <?php
                        echo htmlspecialchars($job["salary"]);
                        ?>

                    </div>

                <?php endif; ?>

            </div>


            <div class="divider"></div>


            <p class="job-note">

                Your resume will be shared with the employer
                associated with this job opportunity. Make sure
                your resume contains your latest education,
                skills, projects and professional experience.

            </p>

        </section>


        <!-- =================================================
             APPLICATION FORM
        ================================================== -->

        <section class="upload-card">

            <h2>
                Submit Application
            </h2>

            <p class="upload-subtitle">
                Upload your latest resume to continue.
            </p>


            <?php if ($message !== ""): ?>

                <div class="alert success">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="alert error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($message === "" && $error !== "You have already applied for this job."): ?>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <div class="upload-area">

                        <div class="upload-icon">
                            ↑
                        </div>

                        <strong>
                            Upload your resume
                        </strong>

                        <span>
                            PDF, DOC or DOCX · Maximum 5 MB
                        </span>

                        <input
                            type="file"
                            name="resume"
                            accept=".pdf,.doc,.docx"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="submit-btn"
                    >

                        Submit Application

                    </button>


                    <div class="security-note">

                        <span>
                            ✓
                        </span>

                        <span>
                            Your resume is securely associated
                            with this application.
                        </span>

                    </div>

                </form>

            <?php elseif ($message !== ""): ?>

                <a
                    href="my-applications.php"
                    class="submit-btn"
                    style="
                        display:block;
                        text-align:center;
                        text-decoration:none;
                    "
                >
                    View My Applications
                </a>

            <?php endif; ?>


            <!-- ALWAYS BACK TO WEBSITE -->

            <a
                href="dashboard.php"
                class="back-btn"
            >
                ← Back to Website
            </a>

        </section>

    </div>


    <div class="footer">

        © <?php echo date("Y"); ?>
        Job In India · Professional Career Platform

    </div>

</main>

</body>

</html>