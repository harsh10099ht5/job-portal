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

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"] ?? "User";

/*
|--------------------------------------------------------------------------
| GET STUDENT APPLICATIONS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        applications.id AS application_id,
        applications.status,
        applications.applied_at,
        applications.resume,

        jobs.id AS job_id,
        jobs.title AS job_title,
        jobs.company,
        jobs.location,
        jobs.salary,
        jobs.description

    FROM applications

    INNER JOIN jobs
        ON applications.job_id = jobs.id

    WHERE applications.user_id = ?

    ORDER BY applications.applied_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| APPLICATION STATISTICS
|--------------------------------------------------------------------------
*/

$total = count($applications);

$applied_count = 0;
$shortlisted_count = 0;
$selected_count = 0;
$rejected_count = 0;

foreach ($applications as $application) {

    $status = strtolower($application["status"]);

    if ($status === "applied") {
        $applied_count++;
    }

    if ($status === "shortlisted") {
        $shortlisted_count++;
    }

    if ($status === "selected") {
        $selected_count++;
    }

    if ($status === "rejected") {
        $rejected_count++;
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

<title>My Applications - Job In India</title>

<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}


/* =========================================================
   BODY
========================================================= */

body {
    background: #f4f7fb;
    color: #1f2937;
    min-height: 100vh;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar {
    background: linear-gradient(
        135deg,
        #0d6efd,
        #084298
    );

    color: white;

    padding: 17px 45px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.12);
}

.brand {
    font-size: 23px;
    font-weight: bold;
    letter-spacing: 0.3px;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-btn {
    text-decoration: none;
    padding: 9px 15px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s;
}

.dashboard-btn {
    background: rgba(255,255,255,0.15);
    color: white;
}

.profile-btn {
    background: rgba(255,255,255,0.15);
    color: white;
}

.logout-btn {
    background: white;
    color: #0d6efd;
}

.nav-btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {
    max-width: 1150px;
    margin: auto;
    padding: 40px 20px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 32px;
    margin-bottom: 8px;
    color: #111827;
}

.page-header p {
    color: #6b7280;
    font-size: 15px;
}


/* =========================================================
   BACK WEBSITE
========================================================= */

.back-website {
    display: inline-block;

    margin-bottom: 25px;

    color: #0d6efd;

    text-decoration: none;

    font-weight: bold;

    font-size: 14px;
}

.back-website:hover {
    text-decoration: underline;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}

.stat-card {
    background: white;

    padding: 22px;

    border-radius: 12px;

    box-shadow:
        0 3px 14px rgba(0,0,0,0.07);

    border: 1px solid #edf0f5;
}

.stat-card h3 {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #111827;
}

.stat-blue {
    border-left: 4px solid #0d6efd;
}

.stat-yellow {
    border-left: 4px solid #ffc107;
}

.stat-green {
    border-left: 4px solid #198754;
}

.stat-red {
    border-left: 4px solid #dc3545;
}


/* =========================================================
   TOOLBAR
========================================================= */

.toolbar {
    background: white;

    padding: 18px;

    border-radius: 12px;

    margin-bottom: 25px;

    display: flex;

    gap: 12px;

    flex-wrap: wrap;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);
}

.search-box {
    flex: 1;
    min-width: 220px;
}

.search-box input,
.filter select {
    width: 100%;

    padding: 12px 14px;

    border: 1px solid #d1d5db;

    border-radius: 7px;

    font-size: 14px;

    outline: none;
}

.search-box input:focus,
.filter select:focus {
    border-color: #0d6efd;

    box-shadow:
        0 0 0 3px rgba(13,110,253,0.1);
}

.filter {
    width: 180px;
}


/* =========================================================
   APPLICATION CARD
========================================================= */

.application-card {
    background: white;

    border-radius: 14px;

    margin-bottom: 20px;

    padding: 25px;

    box-shadow:
        0 4px 16px rgba(0,0,0,0.07);

    border: 1px solid #edf0f5;

    transition: 0.2s;
}

.application-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 7px 22px rgba(0,0,0,0.10);
}


/* =========================================================
   CARD HEADER
========================================================= */

.card-header {
    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 20px;
}

.job-title {
    font-size: 21px;

    color: #111827;

    margin-bottom: 7px;
}

.company {
    color: #0d6efd;

    font-weight: bold;

    font-size: 15px;
}


/* =========================================================
   STATUS
========================================================= */

.status {
    display: inline-block;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

    white-space: nowrap;
}

.status-applied {
    background: #fff3cd;
    color: #856404;
}

.status-shortlisted {
    background: #cfe2ff;
    color: #084298;
}

.status-selected {
    background: #d1e7dd;
    color: #0f5132;
}

.status-rejected {
    background: #f8d7da;
    color: #842029;
}


/* =========================================================
   JOB INFORMATION
========================================================= */

.job-info {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-bottom: 20px;
}

.info-box {
    background: #f8fafc;

    padding: 13px;

    border-radius: 8px;

    border: 1px solid #edf0f5;
}

.info-label {
    display: block;

    color: #6b7280;

    font-size: 12px;

    margin-bottom: 5px;

    text-transform: uppercase;
}

.info-value {
    font-size: 14px;

    font-weight: bold;

    color: #374151;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.description {
    background: #fafafa;

    padding: 16px;

    border-radius: 8px;

    margin-bottom: 20px;

    line-height: 1.6;

    color: #555;

    font-size: 14px;
}

.description-title {
    font-weight: bold;

    color: #222;

    margin-bottom: 6px;
}


/* =========================================================
   CARD FOOTER
========================================================= */

.card-footer {
    border-top: 1px solid #edf0f5;

    padding-top: 17px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;
}

.applied-date {
    color: #6b7280;

    font-size: 13px;
}

.actions {
    display: flex;

    gap: 10px;

    flex-wrap: wrap;
}

.action-btn {
    display: inline-block;

    padding: 9px 15px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;
}

.resume-btn {
    background: #0d6efd;

    color: white;
}

.job-btn {
    background: #eef4ff;

    color: #0d6efd;
}

.action-btn:hover {
    opacity: 0.85;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty {
    background: white;

    padding: 55px 25px;

    text-align: center;

    border-radius: 14px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.06);
}

.empty-icon {
    font-size: 45px;

    margin-bottom: 15px;
}

.empty h2 {
    margin-bottom: 10px;

    color: #222;
}

.empty p {
    color: #6b7280;

    margin-bottom: 20px;
}

.find-jobs {
    display: inline-block;

    background: #0d6efd;

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 7px;

    font-weight: bold;
}

.find-jobs:hover {
    background: #0b5ed7;
}


/* =========================================================
   NO SEARCH RESULTS
========================================================= */

.no-results {
    display: none;

    background: white;

    padding: 30px;

    text-align: center;

    border-radius: 12px;

    color: #777;
}


/* =========================================================
   FOOTER
========================================================= */

.footer {
    text-align: center;

    color: #777;

    padding: 30px 20px;

    margin-top: 20px;

    font-size: 13px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 800px) {

    .navbar {
        padding: 15px 20px;
    }

    .nav-right {
        gap: 6px;
    }

    .nav-btn {
        padding: 8px 10px;
        font-size: 12px;
    }

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .job-info {
        grid-template-columns:
            1fr;
    }

    .card-header {
        flex-direction: column;
    }

}


@media (max-width: 550px) {

    .brand {
        font-size: 19px;
    }

    .profile-btn {
        display: none;
    }

    .container {
        padding: 25px 15px;
    }

    .page-header h1 {
        font-size: 26px;
    }

    .stats {
        grid-template-columns:
            1fr 1fr;
    }

    .stat-card {
        padding: 17px;
    }

    .stat-number {
        font-size: 23px;
    }

    .application-card {
        padding: 20px;
    }

    .job-title {
        font-size: 19px;
    }

    .card-footer {
        align-items: flex-start;
        flex-direction: column;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="brand">
        Job In India
    </div>

    <div class="nav-right">

        <a
            href="dashboard.php"
            class="nav-btn dashboard-btn">

            Dashboard

        </a>

        <a
            href="profile.php"
            class="nav-btn profile-btn">

            Profile

        </a>

        <a
            href="logout.php"
            class="nav-btn logout-btn">

            Logout

        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="container">


    <!-- BACK TO WEBSITE -->

    <a
        href="index.php"
        class="back-website">

        ← Back to Website

    </a>


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            My Applications
        </h1>

        <p>
            Track and manage all the jobs you have applied for.
        </p>

    </div>


    <!-- =================================================
         STATISTICS
    ================================================= -->

    <div class="stats">


        <div class="stat-card stat-blue">

            <h3>
                Total Applications
            </h3>

            <div class="stat-number">
                <?php echo $total; ?>
            </div>

        </div>


        <div class="stat-card stat-yellow">

            <h3>
                Applied
            </h3>

            <div class="stat-number">
                <?php echo $applied_count; ?>
            </div>

        </div>


        <div class="stat-card stat-green">

            <h3>
                Shortlisted / Selected
            </h3>

            <div class="stat-number">
                <?php echo $shortlisted_count + $selected_count; ?>
            </div>

        </div>


        <div class="stat-card stat-red">

            <h3>
                Rejected
            </h3>

            <div class="stat-number">
                <?php echo $rejected_count; ?>
            </div>

        </div>


    </div>


    <?php if ($total > 0): ?>


    <!-- =================================================
         SEARCH & FILTER
    ================================================= -->

    <div class="toolbar">


        <div class="search-box">

            <input
                type="text"
                id="searchInput"
                placeholder="Search by job title or company..."
                onkeyup="filterApplications()"
            >

        </div>


        <div class="filter">

            <select
                id="statusFilter"
                onchange="filterApplications()">

                <option value="all">
                    All Status
                </option>

                <option value="Applied">
                    Applied
                </option>

                <option value="Shortlisted">
                    Shortlisted
                </option>

                <option value="Selected">
                    Selected
                </option>

                <option value="Rejected">
                    Rejected
                </option>

            </select>

        </div>


    </div>


    <!-- =================================================
         APPLICATIONS
    ================================================= -->

    <div id="applicationsContainer">


    <?php foreach ($applications as $application): ?>


        <?php

        $status = $application["status"];

        $status_lower = strtolower($status);

        $status_class = "status-" . $status_lower;

        ?>


        <div
            class="application-card"

            data-title="<?php
                echo htmlspecialchars(
                    strtolower($application["job_title"])
                );
            ?>"

            data-company="<?php
                echo htmlspecialchars(
                    strtolower($application["company"])
                );
            ?>"

            data-status="<?php
                echo htmlspecialchars($status);
            ?>"
        >


            <!-- CARD HEADER -->

            <div class="card-header">


                <div>

                    <h2 class="job-title">

                        <?php
                        echo htmlspecialchars(
                            $application["job_title"]
                        );
                        ?>

                    </h2>

                    <div class="company">

                        <?php
                        echo htmlspecialchars(
                            $application["company"]
                        );
                        ?>

                    </div>

                </div>


                <span
                    class="status <?php
                        echo $status_class;
                    ?>">

                    <?php
                    echo htmlspecialchars($status);
                    ?>

                </span>


            </div>


            <!-- JOB INFO -->

            <div class="job-info">


                <div class="info-box">

                    <span class="info-label">
                        Location
                    </span>

                    <span class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $application["location"]
                        );
                        ?>

                    </span>

                </div>


                <div class="info-box">

                    <span class="info-label">
                        Salary
                    </span>

                    <span class="info-value">

                        <?php

                        echo !empty(
                            $application["salary"]
                        )
                            ? htmlspecialchars(
                                $application["salary"]
                            )
                            : "Not specified";

                        ?>

                    </span>

                </div>


                <div class="info-box">

                    <span class="info-label">
                        Applied On
                    </span>

                    <span class="info-value">

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $application["applied_at"]
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

                <?php

                $description =
                    $application["description"];

                if (strlen($description) > 300) {

                    echo nl2br(
                        htmlspecialchars(
                            substr(
                                $description,
                                0,
                                300
                            )
                        )
                    );

                    echo "...";

                } else {

                    echo nl2br(
                        htmlspecialchars(
                            $description
                        )
                    );

                }

                ?>

            </div>


            <!-- FOOTER -->

            <div class="card-footer">


                <div class="applied-date">

                    Application ID:
                    #<?php
                    echo $application["application_id"];
                    ?>

                </div>


                <div class="actions">


                    <!-- VIEW JOB -->

                    <a
                        href="job-details.php?id=<?php
                            echo $application["job_id"];
                        ?>"
                        class="action-btn job-btn">

                        View Job

                    </a>


                    <!-- RESUME -->

                    <?php if (!empty($application["resume"])): ?>

                        <a
                            href="<?php
                                echo htmlspecialchars(
                                    $application["resume"]
                                );
                            ?>"
                            target="_blank"
                            class="action-btn resume-btn">

                            View Resume

                        </a>

                    <?php endif; ?>


                </div>


            </div>


        </div>


    <?php endforeach; ?>


    </div>


    <!-- NO SEARCH RESULT -->

    <div
        id="noResults"
        class="no-results">

        <h3>
            No applications found
        </h3>

        <p>
            Try changing your search or status filter.
        </p>

    </div>


    <?php else: ?>


    <!-- =================================================
         EMPTY STATE
    ================================================= -->

    <div class="empty">

        <div class="empty-icon">
            📄
        </div>

        <h2>
            No Applications Yet
        </h2>

        <p>
            You haven't applied for any jobs yet.
            Start exploring opportunities and submit your first application.
        </p>

        <a
            href="jobs.php"
            class="find-jobs">

            Find Jobs

        </a>

    </div>


    <?php endif; ?>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    © <?php echo date("Y"); ?>
    Job In India.

    All rights reserved.

</footer>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

function filterApplications() {

    const searchInput =
        document
        .getElementById("searchInput")
        .value
        .toLowerCase()
        .trim();

    const statusFilter =
        document
        .getElementById("statusFilter")
        .value;

    const cards =
        document.querySelectorAll(
            ".application-card"
        );

    let visibleCount = 0;


    cards.forEach(function(card) {

        const title =
            card.dataset.title;

        const company =
            card.dataset.company;

        const status =
            card.dataset.status;


        const searchMatch =
            title.includes(searchInput) ||
            company.includes(searchInput);


        const statusMatch =
            statusFilter === "all" ||
            status === statusFilter;


        if (searchMatch && statusMatch) {

            card.style.display = "block";

            visibleCount++;

        } else {

            card.style.display = "none";

        }

    });


    const noResults =
        document.getElementById(
            "noResults"
        );


    if (visibleCount === 0) {

        noResults.style.display = "block";

    } else {

        noResults.style.display = "none";

    }

}

</script>


</body>

</html>