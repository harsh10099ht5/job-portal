<?php

session_start();

require_once "config/database.php";


// --------------------------------------------------
// LOGIN CHECK
// --------------------------------------------------

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// FILTER VALUES
// --------------------------------------------------

$search = trim($_GET["search"] ?? "");
$location = trim($_GET["location"] ?? "");
$salary = trim($_GET["salary"] ?? "");


// --------------------------------------------------
// BASE QUERY
// --------------------------------------------------

$sql = "
    SELECT
        id,
        title,
        company,
        location,
        salary,
        description,
        created_at
    FROM jobs
    WHERE 1=1
";

$params = [];
$types = "";


// --------------------------------------------------
// SEARCH FILTER
// --------------------------------------------------

if ($search !== "") {

    $sql .= "
        AND (
            title LIKE ?
            OR company LIKE ?
            OR description LIKE ?
        )
    ";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= "sss";
}


// --------------------------------------------------
// LOCATION FILTER
// --------------------------------------------------

if ($location !== "") {

    $sql .= "
        AND location LIKE ?
    ";

    $locationTerm = "%" . $location . "%";

    $params[] = $locationTerm;

    $types .= "s";
}


// --------------------------------------------------
// SALARY FILTER
// --------------------------------------------------

if ($salary !== "") {

    $sql .= "
        AND salary LIKE ?
    ";

    $salaryTerm = "%" . $salary . "%";

    $params[] = $salaryTerm;

    $types .= "s";
}


// --------------------------------------------------
// LATEST JOBS FIRST
// --------------------------------------------------

$sql .= "
    ORDER BY created_at DESC
";


// --------------------------------------------------
// PREPARE
// --------------------------------------------------

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


// --------------------------------------------------
// BIND FILTERS
// --------------------------------------------------

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


// --------------------------------------------------
// EXECUTE
// --------------------------------------------------

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

    <title>Find Jobs - Job In India</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }


        body {
            background: #f4f7fb;
            color: #222;
        }


        /* ================= NAVBAR ================= */

        .navbar {
            background: #0d6efd;
            color: white;

            padding: 17px 6%;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .brand {
            font-size: 23px;
            font-weight: 700;
        }


        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }


        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }


        .nav-btn {
            background: white;
            color: #0d6efd !important;

            padding: 9px 16px;

            border-radius: 6px;
        }


        /* ================= CONTAINER ================= */

        .container {
            max-width: 1150px;

            margin: 40px auto;

            padding: 0 20px;
        }


        /* ================= HEADER ================= */

        .page-header {
            margin-bottom: 25px;
        }


        .page-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }


        .page-header p {
            color: #6c757d;
            font-size: 15px;
        }


        /* ================= SEARCH ================= */

        .search-box {
            background: white;

            padding: 22px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.07);

            margin-bottom: 30px;
        }


        .filters {
            display: grid;

            grid-template-columns:
                2fr 1fr 1fr auto auto;

            gap: 10px;
        }


        .filters input {
            width: 100%;

            height: 45px;

            padding: 0 13px;

            border: 1px solid #d5dbe1;

            border-radius: 7px;

            font-size: 14px;

            outline: none;
        }


        .filters input:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px rgba(13,110,253,0.08);
        }


        .search-btn,
        .clear-btn {
            height: 45px;

            padding: 0 18px;

            border-radius: 7px;

            font-weight: 600;

            text-decoration: none;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .search-btn {
            border: none;

            background: #0d6efd;

            color: white;

            cursor: pointer;
        }


        .search-btn:hover {
            background: #0b5ed7;
        }


        .clear-btn {
            background: #6c757d;

            color: white;
        }


        .clear-btn:hover {
            background: #5c636a;
        }


        /* ================= RESULTS ================= */

        .results-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;
        }


        .results-header h2 {
            font-size: 21px;
        }


        .result-count {
            color: #6c757d;

            font-size: 14px;
        }


        /* ================= JOB CARD ================= */

        .job-card {
            background: white;

            padding: 25px;

            margin-bottom: 18px;

            border-radius: 12px;

            border: 1px solid #edf0f3;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);

            transition: 0.2s;
        }


        .job-card:hover {
            transform: translateY(-2px);

            box-shadow:
                0 7px 22px rgba(0,0,0,0.09);
        }


        .job-top {
            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 15px;

            margin-bottom: 15px;
        }


        .job-title {
            color: #0d6efd;

            font-size: 21px;

            margin-bottom: 7px;
        }


        .company {
            color: #555;

            font-size: 14px;
        }


        .job-badge {
            background: #e7f1ff;

            color: #0d6efd;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        /* ================= JOB INFO ================= */

        .job-details {
            display: flex;

            flex-wrap: wrap;

            gap: 10px;

            margin: 15px 0;
        }


        .detail {
            background: #f5f7fa;

            padding: 8px 12px;

            border-radius: 6px;

            color: #555;

            font-size: 13px;
        }


        .description {
            color: #666;

            line-height: 1.6;

            font-size: 14px;

            margin-top: 12px;

            max-height: 70px;

            overflow: hidden;
        }


        /* ================= FOOTER ROW ================= */

        .job-footer {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 20px;

            padding-top: 17px;

            border-top: 1px solid #eee;
        }


        .posted {
            color: #888;

            font-size: 12px;
        }


        .apply-btn {
            display: inline-block;

            background: #0d6efd;

            color: white;

            text-decoration: none;

            padding: 10px 20px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;
        }


        .apply-btn:hover {
            background: #0b5ed7;
        }


        /* ================= NO JOBS ================= */

        .no-jobs {
            background: white;

            padding: 50px 25px;

            text-align: center;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);
        }


        .no-jobs h3 {
            margin-bottom: 8px;
        }


        .no-jobs p {
            color: #777;

            font-size: 14px;
        }


        /* ================= MOBILE ================= */

        @media (max-width: 850px) {

            .filters {
                grid-template-columns: 1fr 1fr;
            }

            .filters input:first-child {
                grid-column: 1 / -1;
            }

        }


        @media (max-width: 600px) {

            .navbar {
                padding: 15px 20px;
            }

            .nav-links {
                gap: 8px;
            }

            .nav-links a:not(.nav-btn) {
                display: none;
            }

            .container {
                margin: 25px auto;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .filters input:first-child {
                grid-column: auto;
            }

            .job-top {
                flex-direction: column;
            }

            .job-footer {
                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }

            .apply-btn {
                width: 100%;

                text-align: center;
            }

            .results-header {
                flex-direction: column;

                align-items: flex-start;

                gap: 7px;
            }

        }

    </style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="brand">
        Job In India
    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a
            href="profile.php"
            class="nav-btn"
        >
            My Profile
        </a>

    </div>

</nav>


<!-- ================= MAIN ================= -->

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Find Your Next Job
        </h1>

        <p>
            Discover jobs and career opportunities
            that match your skills.
        </p>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <form method="GET">

            <div class="filters">


                <input
                    type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Job title, company or keyword"
                >


                <input
                    type="text"
                    name="location"
                    value="<?php echo htmlspecialchars($location); ?>"
                    placeholder="Location"
                >


                <input
                    type="text"
                    name="salary"
                    value="<?php echo htmlspecialchars($salary); ?>"
                    placeholder="Salary"
                >


                <button
                    type="submit"
                    class="search-btn"
                >
                    Search
                </button>


                <a
                    href="jobs.php"
                    class="clear-btn"
                >
                    Clear
                </a>

            </div>

        </form>

    </div>


    <!-- RESULTS HEADER -->

    <div class="results-header">

        <h2>
            Available Jobs
        </h2>

        <span class="result-count">

            <?php echo $result->num_rows; ?>

            job(s) found

        </span>

    </div>


    <!-- JOBS -->

    <?php if ($result->num_rows > 0): ?>


        <?php while ($job = $result->fetch_assoc()): ?>


            <div class="job-card">


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


                    <span class="job-badge">
                        Open Position
                    </span>

                </div>


                <!-- JOB DETAILS -->

                <div class="job-details">


                    <span class="detail">

                        Location:
                        <?php
                        echo htmlspecialchars(
                            $job["location"]
                        );
                        ?>

                    </span>


                    <span class="detail">

                        Salary:
                        <?php
                        echo htmlspecialchars(
                            $job["salary"] ?: "Not specified"
                        );
                        ?>

                    </span>

                </div>


                <!-- DESCRIPTION -->

                <div class="description">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $job["description"]
                        )
                    );
                    ?>

                </div>


                <!-- FOOTER -->

                <div class="job-footer">


                    <span class="posted">

                        Posted:

                        <?php
                        echo date(
                            "d M Y",
                            strtotime($job["created_at"])
                        );
                        ?>

                    </span>


                    <!-- IMPORTANT:
                         apply.php is assumed to be
                         in the project root.
                    -->

                    <a
                        href="apply.php?job_id=<?php echo (int)$job["id"]; ?>"
                        class="apply-btn"
                    >
                        Apply Now
                    </a>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="no-jobs">

            <h3>
                No Jobs Found
            </h3>

            <p>
                Try changing your search keywords,
                location or salary filter.
            </p>

        </div>


    <?php endif; ?>


</div>


</body>

</html>