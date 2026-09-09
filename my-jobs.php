<?php

session_start();

require_once "config/database.php";


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION["user_id"];
$role = strtolower($_SESSION["role"] ?? "");


/*
|--------------------------------------------------------------------------
| ACCESS CONTROL
|--------------------------------------------------------------------------
*/

if ($role !== "employer" && $role !== "admin") {
    header("Location: dashboard.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DELETE JOB
|--------------------------------------------------------------------------
*/

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_job"])) {

    $job_id = (int) ($_POST["job_id"] ?? 0);

    if ($job_id <= 0) {

        $error = "Invalid job.";

    } else {

        /*
        Only delete jobs belonging to logged-in user.
        */

        $delete_sql = "
            DELETE FROM jobs
            WHERE id = ?
            AND employer_id = ?
        ";

        $delete_stmt = $conn->prepare($delete_sql);

        if ($delete_stmt) {

            $delete_stmt->bind_param(
                "ii",
                $job_id,
                $user_id
            );

            if ($delete_stmt->execute()) {

                if ($delete_stmt->affected_rows > 0) {
                    $success = "Job deleted successfully.";
                } else {
                    $error = "You are not authorized to delete this job.";
                }

            } else {

                $error = "Unable to delete the job.";
            }

            $delete_stmt->close();

        } else {

            $error = "Database error.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET POSTED JOBS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        jobs.id,
        jobs.title,
        jobs.company,
        jobs.location,
        jobs.salary,
        jobs.description,
        jobs.created_at,

        COUNT(applications.id) AS application_count

    FROM jobs

    LEFT JOIN applications
        ON jobs.id = applications.job_id

    WHERE jobs.employer_id = ?

    GROUP BY
        jobs.id,
        jobs.title,
        jobs.company,
        jobs.location,
        jobs.salary,
        jobs.description,
        jobs.created_at

    ORDER BY jobs.created_at DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}


$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Posted Jobs | Job In India</title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | GLOBAL
        |--------------------------------------------------------------------------
        */

        body {

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            background: #f5f7fb;

            color: #1f2937;

            min-height: 100vh;
        }


        a {
            text-decoration: none;
        }


        /*
        |--------------------------------------------------------------------------
        | NAVBAR
        |--------------------------------------------------------------------------
        */

        .navbar {

            height: 70px;

            background: #ffffff;

            border-bottom: 1px solid #e5e7eb;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 45px;

            position: sticky;

            top: 0;

            z-index: 100;
        }


        .brand {

            font-size: 23px;

            font-weight: 800;

            color: #0d6efd;
        }


        .brand span {
            color: #111827;
        }


        .nav-right {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .nav-btn {

            padding: 9px 15px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.2s;
        }


        .dashboard-btn {

            background: #f3f4f6;

            color: #374151;
        }


        .dashboard-btn:hover {
            background: #e5e7eb;
        }


        .profile-btn {

            background: #eff6ff;

            color: #0d6efd;
        }


        .profile-btn:hover {
            background: #dbeafe;
        }


        .logout-btn {

            background: #0d6efd;

            color: white;
        }


        .logout-btn:hover {
            background: #0b5ed7;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTAINER
        |--------------------------------------------------------------------------
        */

        .container {

            max-width: 1150px;

            margin: auto;

            padding: 40px 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE HEADER
        |--------------------------------------------------------------------------
        */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;
        }


        .page-header h1 {

            font-size: 30px;

            color: #111827;

            margin-bottom: 6px;
        }


        .page-header p {

            color: #6b7280;

            font-size: 14px;
        }


        .post-btn {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            background: #0d6efd;

            color: white;

            padding: 11px 18px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.2s;

            white-space: nowrap;
        }


        .post-btn:hover {
            background: #0b5ed7;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERTS
        |--------------------------------------------------------------------------
        */

        .alert {

            padding: 14px 17px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 14px;

            font-weight: 600;
        }


        .success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;
        }


        .error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;
        }


        /*
        |--------------------------------------------------------------------------
        | JOB CARD
        |--------------------------------------------------------------------------
        */

        .job-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 27px;

            margin-bottom: 20px;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .job-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 10px 25px rgba(0,0,0,0.07);
        }


        /*
        |--------------------------------------------------------------------------
        | JOB TOP
        |--------------------------------------------------------------------------
        */

        .job-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 20px;
        }


        .job-title {

            color: #111827;

            font-size: 22px;

            margin-bottom: 7px;
        }


        .company {

            color: #0d6efd;

            font-size: 14px;

            font-weight: 650;
        }


        .application-badge {

            background: #eff6ff;

            color: #0d6efd;

            padding: 8px 13px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | JOB DETAILS
        |--------------------------------------------------------------------------
        */

        .details {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;

            margin-bottom: 20px;
        }


        .detail {

            background: #f8fafc;

            border: 1px solid #eef0f3;

            border-radius: 9px;

            padding: 13px 15px;
        }


        .detail-label {

            display: block;

            color: #6b7280;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            margin-bottom: 5px;
        }


        .detail-value {

            color: #111827;

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;
        }


        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION
        |--------------------------------------------------------------------------
        */

        .description {

            border-top: 1px solid #e5e7eb;

            padding-top: 18px;

            margin-bottom: 20px;
        }


        .description-title {

            font-size: 14px;

            font-weight: 700;

            margin-bottom: 8px;

            color: #111827;
        }


        .description-text {

            color: #6b7280;

            font-size: 14px;

            line-height: 1.7;

            white-space: normal;
        }


        /*
        |--------------------------------------------------------------------------
        | ACTIONS
        |--------------------------------------------------------------------------
        */

        .actions {

            display: flex;

            align-items: center;

            gap: 10px;

            flex-wrap: wrap;

            padding-top: 5px;
        }


        .action-btn {

            border: none;

            padding: 10px 16px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;

            display: inline-block;
        }


        .view-btn {

            background: #0d6efd;

            color: white;
        }


        .view-btn:hover {
            background: #0b5ed7;
        }


        .edit-btn {

            background: #fff3cd;

            color: #856404;
        }


        .edit-btn:hover {
            background: #ffe69c;
        }


        .delete-btn {

            background: #fee2e2;

            color: #b91c1c;
        }


        .delete-btn:hover {
            background: #fecaca;
        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */

        .empty {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 65px 30px;

            text-align: center;
        }


        .empty-icon {

            width: 65px;

            height: 65px;

            background: #eff6ff;

            color: #0d6efd;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 18px;

            font-size: 27px;

            font-weight: bold;
        }


        .empty h2 {

            color: #111827;

            font-size: 22px;

            margin-bottom: 8px;
        }


        .empty p {

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 22px;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {

            text-align: center;

            color: #9ca3af;

            font-size: 13px;

            padding: 25px 0 5px;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 750px) {

            .navbar {

                height: auto;

                padding: 15px 20px;

                flex-direction: column;

                gap: 14px;
            }


            .nav-right {

                flex-wrap: wrap;

                justify-content: center;
            }


            .container {

                padding: 25px 15px;
            }


            .page-header {

                flex-direction: column;

                align-items: flex-start;
            }


            .page-header h1 {

                font-size: 26px;
            }


            .post-btn {

                width: 100%;

                justify-content: center;
            }


            .details {

                grid-template-columns: 1fr;
            }


            .job-top {

                flex-direction: column;
            }


            .application-badge {

                align-self: flex-start;
            }

        }


        @media (max-width: 450px) {

            .nav-btn {

                font-size: 13px;

                padding: 8px 11px;
            }


            .job-card {

                padding: 20px;
            }


            .job-title {

                font-size: 19px;
            }


            .actions {

                flex-direction: column;

                align-items: stretch;
            }


            .action-btn {

                width: 100%;

                text-align: center;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">


    <a
        href="dashboard.php"
        class="brand"
    >
        Job <span>In India</span>
    </a>


    <div class="nav-right">


        <a
            href="dashboard.php"
            class="nav-btn dashboard-btn"
        >
            ← Dashboard
        </a>


        <a
            href="profile.php"
            class="nav-btn profile-btn"
        >
            Profile
        </a>


        <a
            href="logout.php"
            class="nav-btn logout-btn"
        >
            Logout
        </a>


    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">


        <div>

            <h1>
                My Posted Jobs
            </h1>

            <p>
                Manage the jobs you have published on Job In India.
            </p>

        </div>


        <?php if ($role === "admin"): ?>

            <a
                href="post-job.php"
                class="post-btn"
            >
                + Post New Job
            </a>

        <?php endif; ?>


    </div>


    <!-- =====================================================
         ALERTS
    ====================================================== -->

    <?php if ($success !== ""): ?>

        <div class="alert success">

            <?php
            echo htmlspecialchars($success);
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


    <!-- =====================================================
         JOB LIST
    ====================================================== -->

    <?php if ($result->num_rows > 0): ?>


        <?php while ($job = $result->fetch_assoc()): ?>


            <article class="job-card">


                <!-- JOB HEADER -->

                <div class="job-top">


                    <div>

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

                    </div>


                    <div class="application-badge">

                        <?php
                        echo (int) $job["application_count"];
                        ?>

                        Application<?php echo ((int) $job["application_count"] === 1) ? "" : "s"; ?>

                    </div>


                </div>


                <!-- JOB DETAILS -->

                <div class="details">


                    <div class="detail">

                        <span class="detail-label">
                            Location
                        </span>

                        <span class="detail-value">

                            <?php
                            echo htmlspecialchars($job["location"]);
                            ?>

                        </span>

                    </div>


                    <div class="detail">

                        <span class="detail-label">
                            Salary
                        </span>

                        <span class="detail-value">

                            <?php

                            if (!empty($job["salary"])) {

                                echo htmlspecialchars($job["salary"]);

                            } else {

                                echo "Not specified";

                            }

                            ?>

                        </span>

                    </div>


                    <div class="detail">

                        <span class="detail-label">
                            Posted On
                        </span>

                        <span class="detail-value">

                            <?php

                            echo htmlspecialchars(
                                date(
                                    "d M Y",
                                    strtotime($job["created_at"])
                                )
                            );

                            ?>

                        </span>

                    </div>


                </div>


                <!-- DESCRIPTION -->

                <div class="description">


                    <div class="description-title">
                        Job Description
                    </div>


                    <div class="description-text">

                        <?php

                        echo nl2br(
                            htmlspecialchars($job["description"])
                        );

                        ?>

                    </div>


                </div>


                <!-- ACTIONS -->

                <div class="actions">


                    <!-- VIEW APPLICATIONS -->

                    <a
                        href="applications.php"
                        class="action-btn view-btn"
                    >
                        View Applications
                    </a>


                    <!-- EDIT -->

                    <a
                        href="edit-job.php?id=<?php echo (int) $job["id"]; ?>"
                        class="action-btn edit-btn"
                    >
                        Edit Job
                    </a>


                    <!-- DELETE -->

                    <form
                        method="POST"
                        style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.');"
                    >

                        <input
                            type="hidden"
                            name="job_id"
                            value="<?php echo (int) $job["id"]; ?>"
                        >


                        <button
                            type="submit"
                            name="delete_job"
                            class="action-btn delete-btn"
                        >
                            Delete Job
                        </button>

                    </form>


                </div>


            </article>


        <?php endwhile; ?>


    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
        ================================================== -->

        <div class="empty">


            <div class="empty-icon">
                +
            </div>


            <h2>
                No Jobs Posted Yet
            </h2>


            <p>
                You have not published any job opportunities yet.
            </p>


            <?php if ($role === "admin"): ?>

                <a
                    href="post-job.php"
                    class="post-btn"
                >
                    + Post Your First Job
                </a>

            <?php else: ?>

                <p>
                    Jobs created for your account will appear here.
                </p>

            <?php endif; ?>


        </div>


    <?php endif; ?>


    <!-- FOOTER -->

    <footer class="footer">

        © <?php echo date("Y"); ?>
        Job In India. All rights reserved.

    </footer>


</main>


</body>

</html>

<?php

$stmt->close();

$conn->close();

?>