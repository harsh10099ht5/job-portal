from pathlib import Path
import re

src = Path("/mnt/data/jobs.php")
text = src.read_text(encoding="utf-8")

# Replace the existing PHP/filter section up to the HTML document with an upgraded query layer.
html_start = text.index("<!DOCTYPE html>")
php = r'''<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$search   = trim($_GET["search"] ?? "");
$location = trim($_GET["location"] ?? "");
$salary   = trim($_GET["salary"] ?? "");
$sort     = $_GET["sort"] ?? "latest";

$allowedSorts = [
    "latest" => "created_at DESC",
    "oldest" => "created_at ASC"
];

$orderBy = $allowedSorts[$sort] ?? $allowedSorts["latest"];

$sql = "
    SELECT id, title, company, location, salary, description, created_at
    FROM jobs
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR company LIKE ? OR description LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "sss";
}

if ($location !== "") {
    $sql .= " AND location LIKE ?";
    $locationTerm = "%" . $location . "%";
    $params[] = $locationTerm;
    $types .= "s";
}

if ($salary !== "") {
    $sql .= " AND salary LIKE ?";
    $salaryTerm = "%" . $salary . "%";
    $params[] = $salaryTerm;
    $types .= "s";
}

$sql .= " ORDER BY " . $orderBy;

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Unable to load jobs. Please try again later.");
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$totalJobs = $result->num_rows;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function jobDescription($value) {
    $text = trim(strip_tags((string)$value));
    if (mb_strlen($text) > 220) {
        return mb_substr($text, 0, 220) . "...";
    }
    return $text;
}
?>
'''
# Modern complete UI. It intentionally remains a single file.
ui = r'''<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Jobs | Job In India</title>
<meta name="description" content="Search and discover career opportunities on Job In India.">
<style>
:root{
    --primary:#2563eb;
    --primary-dark:#1d4ed8;
    --text:#0f172a;
    --muted:#64748b;
    --soft:#f8fafc;
    --border:#e2e8f0;
    --white:#ffffff;
    --success:#047857;
}
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    background:var(--soft);
    color:var(--text);
    line-height:1.5;
}
a{text-decoration:none}
.navbar{
    position:sticky;top:0;z-index:50;
    height:72px;padding:0 6%;
    background:rgba(255,255,255,.94);
    backdrop-filter:blur(14px);
    border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
}
.brand{
    display:flex;align-items:center;gap:10px;
    color:var(--text);font-size:21px;font-weight:800;
    letter-spacing:-.5px;
}
.brand-mark{
    width:35px;height:35px;border-radius:10px;
    display:grid;place-items:center;
    background:var(--primary);color:white;
    box-shadow:0 7px 18px rgba(37,99,235,.22);
}
.nav-links{display:flex;align-items:center;gap:7px}
.nav-links a{
    padding:9px 12px;border-radius:9px;
    color:#475569;font-size:14px;font-weight:650;
}
.nav-links a:hover{background:#f1f5f9;color:var(--text)}
.nav-btn{border:1px solid var(--border)}
.container{max-width:1180px;margin:auto;padding:48px 22px 70px}
.hero{
    display:flex;align-items:end;justify-content:space-between;
    gap:25px;margin-bottom:28px;
}
.eyebrow{
    display:inline-flex;align-items:center;gap:7px;
    padding:6px 11px;border:1px solid #dbeafe;
    border-radius:999px;background:#eff6ff;
    color:var(--primary);font-size:12px;font-weight:750;
    margin-bottom:14px;
}
.eyebrow-dot{width:6px;height:6px;border-radius:50%;background:#22c55e}
h1{
    font-size:clamp(32px,4vw,48px);
    line-height:1.05;letter-spacing:-1.8px;
    margin-bottom:11px;
}
.hero p{max-width:650px;color:var(--muted);font-size:16px}
.hero-side{text-align:right;color:var(--muted);font-size:13px}
.back-link{
    display:inline-block;margin-top:14px;
    color:var(--primary);font-size:13px;font-weight:700;
}
.search-panel{
    padding:18px;background:white;border:1px solid var(--border);
    border-radius:17px;box-shadow:0 12px 32px rgba(15,23,42,.05);
    margin-bottom:30px;
}
.search-label{
    display:block;font-size:12px;font-weight:750;
    color:#475569;margin:0 0 8px 2px;
}
.filters{
    display:grid;
    grid-template-columns:minmax(250px,2fr) 1.05fr 1fr auto auto;
    gap:10px;
}
.field{min-width:0}
.filters input,.filters select{
    width:100%;height:48px;padding:0 13px;
    border:1px solid #cbd5e1;border-radius:10px;
    background:white;color:var(--text);font-size:14px;
    outline:none;transition:.18s;
}
.filters input:focus,.filters select:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 4px #eff6ff;
}
.search-btn,.clear-btn{
    height:48px;padding:0 18px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:750;white-space:nowrap;
}
.search-btn{border:0;background:var(--primary);color:#fff;cursor:pointer}
.search-btn:hover{background:var(--primary-dark)}
.clear-btn{border:1px solid var(--border);background:#fff;color:#475569}
.clear-btn:hover{background:#f8fafc;color:var(--text)}
.active-filters{
    display:flex;flex-wrap:wrap;gap:7px;margin-top:13px;
}
.filter-chip{
    padding:5px 9px;border-radius:999px;
    background:#f1f5f9;color:#475569;font-size:11px;font-weight:650;
}
.results-header{
    display:flex;align-items:center;justify-content:space-between;
    gap:15px;margin-bottom:14px;
}
.results-title{font-size:20px;letter-spacing:-.3px}
.result-count{
    color:var(--muted);font-size:13px;
    border:1px solid var(--border);background:#fff;
    padding:6px 11px;border-radius:999px;
}
.job-card{
    background:white;border:1px solid var(--border);
    border-radius:16px;padding:23px 24px;margin-bottom:14px;
    box-shadow:0 4px 18px rgba(15,23,42,.035);
    transition:.2s;
}
.job-card:hover{
    border-color:#bfdbfe;
    box-shadow:0 13px 32px rgba(15,23,42,.08);
    transform:translateY(-1px);
}
.job-top{
    display:flex;justify-content:space-between;
    align-items:flex-start;gap:18px;
}
.job-title{
    font-size:20px;line-height:1.3;
    letter-spacing:-.35px;margin-bottom:5px;
}
.company{font-size:14px;color:#475569;font-weight:650}
.open-badge{
    padding:6px 10px;border-radius:999px;
    background:#ecfdf5;color:var(--success);
    font-size:11px;font-weight:750;white-space:nowrap;
}
.job-details{
    display:flex;flex-wrap:wrap;gap:8px;margin:17px 0 13px;
}
.detail{
    padding:7px 10px;border-radius:8px;
    background:#f8fafc;border:1px solid #eef2f7;
    color:#475569;font-size:12px;font-weight:650;
}
.description{
    color:var(--muted);font-size:14px;
    line-height:1.65;max-width:900px;
}
.job-footer{
    display:flex;align-items:center;justify-content:space-between;
    gap:15px;margin-top:19px;padding-top:17px;
    border-top:1px solid #f1f5f9;
}
.posted{color:#94a3b8;font-size:12px}
.apply-btn{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:115px;padding:10px 17px;border-radius:9px;
    background:var(--primary);color:#fff;
    font-size:13px;font-weight:750;transition:.18s;
}
.apply-btn:hover{background:var(--primary-dark);transform:translateY(-1px)}
.no-jobs{
    background:#fff;border:1px solid var(--border);
    border-radius:16px;padding:68px 25px;text-align:center;
}
.empty-icon{
    width:52px;height:52px;margin:0 auto 15px;
    display:grid;place-items:center;border-radius:15px;
    background:#eff6ff;color:var(--primary);font-size:22px;
}
.no-jobs h3{font-size:18px;margin-bottom:6px}
.no-jobs p{color:var(--muted);font-size:14px}
footer{
    border-top:1px solid var(--border);
    background:white;color:#94a3b8;text-align:center;
    padding:22px;font-size:12px;
}
@media(max-width:920px){
    .filters{grid-template-columns:1fr 1fr}
    .field:first-child{grid-column:1/-1}
}
@media(max-width:650px){
    .navbar{height:68px;padding:0 17px}
    .nav-links a:not(.nav-btn){display:none}
    .container{padding:32px 16px 50px}
    .hero{display:block}
    .hero-side{text-align:left;margin-top:12px}
    .filters{grid-template-columns:1fr}
    .field:first-child{grid-column:auto}
    .search-btn,.clear-btn{width:100%}
    .job-top,.job-footer{flex-direction:column;align-items:flex-start}
    .apply-btn{width:100%}
}
</style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">
        <span class="brand-mark">J</span>
        <span>Job In India</span>
    </a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="my-applications.php">Applications</a>
        <a href="profile.php" class="nav-btn">My Profile</a>
    </div>
</nav>

<main class="container">
    <section class="hero">
        <div>
            <div class="eyebrow"><span class="eyebrow-dot"></span> Career opportunities</div>
            <h1>Find your next opportunity.</h1>
            <p>Search jobs by role, company, location or salary and discover opportunities that match your career goals.</p>
            <a href="index.php" class="back-link">← Back to Website</a>
        </div>
        <div class="hero-side">
            <strong><?php echo $totalJobs; ?></strong> matching job<?php echo $totalJobs == 1 ? "" : "s"; ?>
        </div>
    </section>

    <section class="search-panel">
        <form method="GET" action="jobs.php">
            <div class="filters">
                <div class="field">
                    <label class="search-label">Keyword</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Job title, company or keyword">
                </div>
                <div class="field">
                    <label class="search-label">Location</label>
                    <input type="text" name="location" value="<?php echo e($location); ?>" placeholder="City or location">
                </div>
                <div class="field">
                    <label class="search-label">Salary</label>
                    <input type="text" name="salary" value="<?php echo e($salary); ?>" placeholder="e.g. 5 LPA">
                </div>
                <div class="field">
                    <label class="search-label">Sort</label>
                    <select name="sort">
                        <option value="latest" <?php echo $sort === "latest" ? "selected" : ""; ?>>Latest</option>
                        <option value="oldest" <?php echo $sort === "oldest" ? "selected" : ""; ?>>Oldest</option>
                    </select>
                </div>
                <button type="submit" class="search-btn">Search Jobs</button>
                <a href="jobs.php" class="clear-btn">Clear</a>
            </div>

            <?php if ($search !== "" || $location !== "" || $salary !== ""): ?>
                <div class="active-filters">
                    <?php if ($search !== ""): ?><span class="filter-chip">Keyword: <?php echo e($search); ?></span><?php endif; ?>
                    <?php if ($location !== ""): ?><span class="filter-chip">Location: <?php echo e($location); ?></span><?php endif; ?>
                    <?php if ($salary !== ""): ?><span class="filter-chip">Salary: <?php echo e($salary); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
    </section>

    <div class="results-header">
        <h2 class="results-title">Available Jobs</h2>
        <span class="result-count"><?php echo $totalJobs; ?> result<?php echo $totalJobs == 1 ? "" : "s"; ?></span>
    </div>

    <?php if ($totalJobs > 0): ?>
        <?php while ($job = $result->fetch_assoc()): ?>
            <article class="job-card">
                <div class="job-top">
                    <div>
                        <h2 class="job-title"><?php echo e($job["title"]); ?></h2>
                        <div class="company"><?php echo e($job["company"]); ?></div>
                    </div>
                    <span class="open-badge">Open Position</span>
                </div>

                <div class="job-details">
                    <span class="detail">Location · <?php echo e($job["location"]); ?></span>
                    <span class="detail">Salary · <?php echo e($job["salary"] ?: "Not specified"); ?></span>
                </div>

                <p class="description"><?php echo e(jobDescription($job["description"])); ?></p>

                <div class="job-footer">
                    <span class="posted">
                        Posted <?php echo date("d M Y", strtotime($job["created_at"])); ?>
                    </span>
                    <a class="apply-btn" href="upload.php?job_id=<?php echo (int)$job["id"]; ?>">
                        Apply Now →
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-jobs">
            <div class="empty-icon">⌕</div>
            <h3>No jobs found</h3>
            <p>Try a different keyword, location or salary filter.</p>
            <a href="jobs.php" class="back-link">Clear all filters</a>
        </div>
    <?php endif; ?>
</main>

<footer>
    © <?php echo date("Y"); ?> Job In India · Connecting Talent With Opportunity
</footer>

</body>
</html>
'''

out = Path("/mnt/data/jobs.php")
out.write_text(php + ui, encoding="utf-8")
print(f"Updated: {out}")
print("Major single-file update completed.")
