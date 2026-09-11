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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Jobs | Job In India</title>
<meta name="description" content="Discover career opportunities on Job In India.">
<style>
:root{--p:#2563eb;--pd:#1d4ed8;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--bg:#f8fafc}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
a{text-decoration:none}
.navbar{position:sticky;top:0;z-index:20;height:72px;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 6%}
.brand{display:flex;align-items:center;gap:10px;color:var(--text);font-weight:800;font-size:21px;letter-spacing:-.4px}
.brand-mark{width:34px;height:34px;border-radius:10px;background:var(--p);color:#fff;display:grid;place-items:center;font-weight:800;box-shadow:0 6px 18px rgba(37,99,235,.22)}
.nav-links{display:flex;align-items:center;gap:10px}.nav-links a{color:#475569;font-size:14px;font-weight:650;padding:9px 12px;border-radius:9px}.nav-links a:hover{background:#f1f5f9;color:var(--text)}
.nav-btn{border:1px solid var(--border);background:#fff!important}
.container{max-width:1180px;margin:auto;padding:48px 22px 70px}
.hero{display:flex;justify-content:space-between;align-items:end;gap:25px;margin-bottom:26px}
.eyebrow{display:inline-flex;align-items:center;gap:7px;color:var(--p);background:#eff6ff;border:1px solid #dbeafe;border-radius:999px;padding:6px 11px;font-size:12px;font-weight:750;margin-bottom:14px}.eyebrow span{width:6px;height:6px;border-radius:50%;background:#22c55e}
h1{font-size:clamp(31px,4vw,46px);line-height:1.08;letter-spacing:-1.5px;margin-bottom:10px}.hero p{color:var(--muted);font-size:16px;max-width:610px}.hero-note{color:var(--muted);font-size:13px;text-align:right}
.search-box{background:#fff;border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.05);margin-bottom:30px}
.filters{display:grid;grid-template-columns:minmax(260px,2fr) 1fr 1fr auto auto;gap:10px}.filters input{width:100%;height:48px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:var(--text);font-size:14px;outline:none;transition:.18s}.filters input:focus{border-color:#93c5fd;box-shadow:0 0 0 4px #eff6ff}
.search-btn,.clear-btn{height:48px;padding:0 19px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;white-space:nowrap}
.search-btn{border:0;background:var(--p);color:#fff;cursor:pointer}.search-btn:hover{background:var(--pd)}
.clear-btn{background:#fff;border:1px solid var(--border);color:#475569}.clear-btn:hover{background:#f8fafc}
.results-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}.results-header h2{font-size:20px}.result-count{color:var(--muted);font-size:13px;background:#fff;border:1px solid var(--border);padding:6px 10px;border-radius:999px}
.job-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:23px 24px;margin-bottom:14px;transition:.2s;box-shadow:0 4px 18px rgba(15,23,42,.035)}.job-card:hover{border-color:#bfdbfe;box-shadow:0 12px 30px rgba(15,23,42,.08);transform:translateY(-1px)}
.job-top{display:flex;justify-content:space-between;align-items:flex-start;gap:18px}.job-title{font-size:20px;line-height:1.3;letter-spacing:-.35px;margin-bottom:5px}.company{color:#475569;font-size:14px;font-weight:600}.job-badge{background:#ecfdf5;color:#047857;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:750;white-space:nowrap}
.job-details{display:flex;flex-wrap:wrap;gap:8px;margin:17px 0 13px}.detail{background:#f8fafc;border:1px solid #eef2f7;color:#475569;padding:7px 10px;border-radius:8px;font-size:12px;font-weight:600}
.description{color:var(--muted);font-size:14px;line-height:1.65;max-width:900px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.job-footer{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-top:19px;padding-top:17px;border-top:1px solid #f1f5f9}.posted{color:#94a3b8;font-size:12px}
.apply-btn{display:inline-flex;align-items:center;justify-content:center;background:var(--p);color:#fff;padding:10px 17px;border-radius:9px;font-size:13px;font-weight:750;transition:.18s}.apply-btn:hover{background:var(--pd);transform:translateY(-1px)}
.no-jobs{background:#fff;border:1px solid var(--border);border-radius:16px;padding:65px 25px;text-align:center}.empty-icon{width:50px;height:50px;border-radius:14px;background:#eff6ff;color:var(--p);display:grid;place-items:center;margin:0 auto 15px;font-size:22px}.no-jobs h3{font-size:18px;margin-bottom:6px}.no-jobs p{color:var(--muted);font-size:14px}
.back-link{display:inline-block;margin-top:18px;color:var(--p);font-size:13px;font-weight:700}
footer{border-top:1px solid var(--border);background:#fff;padding:22px;text-align:center;color:#94a3b8;font-size:12px}
@media(max-width:900px){.filters{grid-template-columns:1fr 1fr}.filters input:first-child{grid-column:1/-1}}
@media(max-width:640px){.navbar{height:auto;min-height:68px;padding:13px 18px}.nav-links a:not(.nav-btn){display:none}.container{padding:32px 16px 50px}.hero{display:block}.hero-note{text-align:left;margin-top:12px}.filters{grid-template-columns:1fr}.filters input:first-child{grid-column:auto}.search-btn,.clear-btn{width:100%}.job-top,.job-footer{flex-direction:column;align-items:flex-start}.apply-btn{width:100%}}
</style>
</head>
<body>
<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <a href="index.php" class="brand"><span class="brand-mark">J</span> Job In India</a>


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

        <p>Discover jobs and career opportunities that match your skills.</p>
        <a href="index.php" class="back-link">← Back to Website</a>

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
                        href="upload.php?job_id=<?php echo (int)$job["id"]; ?>"
                        class="apply-btn"
                    >
                        Apply Now
                    </a>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="no-jobs">
            <div class="empty-icon">⌕</div>
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


<footer>© <?php echo date("Y"); ?> Job In India · Connecting Talent With Opportunity</footer>
</body>

</html>