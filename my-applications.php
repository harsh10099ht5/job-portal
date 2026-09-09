<?php
session_start();

require_once "config/database.php";

// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student's applications
$sql = "
    SELECT
        applications.id AS application_id,
        applications.status,
        applications.applied_at,
        jobs.title AS job_title,
        jobs.company,
        jobs.location,
        jobs.salary
    FROM applications

    INNER JOIN jobs
        ON applications.job_id = jobs.id

    WHERE applications.user_id = ?

    ORDER BY applications.applied_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications - Job In India</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            color: #222;
        }

        .navbar {
            background: #0d6efd;
            color: white;
            padding: 18px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        h1 {
            color: #1464f4;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;

            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
            color: #222;
        }

        .info {
            margin: 10px 0;
            color: #444;
        }

        .status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: bold;
        }

        .applied {
            background: #fff3cd;
            color: #856404;
        }

        .shortlisted {
            background: #cfe2ff;
            color: #084298;
        }

        .selected {
            background: #d1e7dd;
            color: #0f5132;
        }

        .rejected {
            background: #f8d7da;
            color: #842029;
        }

        .empty {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .jobs-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .jobs-btn:hover {
            background: #084298;
        }

    </style>

</head>

<body>

    <div class="navbar">

        <h2>Job In India</h2>

        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="jobs.php">Find Jobs</a>
            <a href="logout.php">Logout</a>
        </div>

    </div>


    <div class="container">

        <h1>My Applications</h1>


        <?php if ($result->num_rows > 0): ?>

            <?php while ($application = $result->fetch_assoc()): ?>

                <?php
                    $status = $application["status"];
                    $status_class = strtolower($status);
                ?>


                <div class="card">

                    <h2>
                        <?php
                        echo htmlspecialchars($application["job_title"]);
                        ?>
                    </h2>


                    <p class="info">

                        <strong>Company:</strong>

                        <?php
                        echo htmlspecialchars($application["company"]);
                        ?>

                    </p>


                    <p class="info">

                        <strong>Location:</strong>

                        <?php
                        echo htmlspecialchars($application["location"]);
                        ?>

                    </p>


                    <p class="info">

                        <strong>Salary:</strong>

                        <?php
                        echo htmlspecialchars($application["salary"]);
                        ?>

                    </p>


                    <p class="info">

                        <strong>Application Status:</strong>

                        <span class="status <?php echo $status_class; ?>">

                            <?php
                            echo htmlspecialchars($status);
                            ?>

                        </span>

                    </p>


                    <p class="info">

                        <strong>Applied On:</strong>

                        <?php
                        echo htmlspecialchars($application["applied_at"]);
                        ?>

                    </p>

                </div>

            <?php endwhile; ?>


        <?php else: ?>

            <div class="empty">

                <h3>You haven't applied for any jobs yet.</h3>

                <p>
                    Browse available jobs and apply for suitable opportunities.
                </p>

                <a href="jobs.php" class="jobs-btn">
                    Find Jobs
                </a>

            </div>

        <?php endif; ?>


    </div>

</body>

</html>