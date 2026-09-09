<?php

session_start();

/*
|--------------------------------------------------------------------------
| Login Protection
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

$name  = $_SESSION["name"] ?? "User";
$email = $_SESSION["email"] ?? "";
$role  = strtolower($_SESSION["role"] ?? "student");


/*
|--------------------------------------------------------------------------
| Normalize Role
|--------------------------------------------------------------------------
*/

if ($role === "user") {
    $role = "student";
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

    <title>Dashboard | Job In India</title>


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

            letter-spacing: -0.5px;
        }


        .brand span {
            color: #111827;
        }


        .nav-right {
            display: flex;

            align-items: center;

            gap: 12px;
        }


        .role-badge {
            padding: 7px 13px;

            border-radius: 20px;

            background: #eff6ff;

            color: #0d6efd;

            font-size: 13px;

            font-weight: 700;

            text-transform: capitalize;
        }


        .profile-btn,
        .logout-btn {
            padding: 9px 16px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.2s;
        }


        .profile-btn {
            color: #374151;

            background: #f3f4f6;
        }


        .profile-btn:hover {
            background: #e5e7eb;
        }


        .logout-btn {
            color: #ffffff;

            background: #0d6efd;
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

            margin: 0 auto;

            padding: 40px 25px 30px;
        }


        /*
        |--------------------------------------------------------------------------
        | WELCOME SECTION
        |--------------------------------------------------------------------------
        */

        .welcome {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #084298
            );

            color: white;

            border-radius: 16px;

            padding: 35px;

            margin-bottom: 30px;

            box-shadow:
                0 8px 25px rgba(13, 110, 253, 0.18);
        }


        .welcome h1 {
            font-size: 30px;

            margin-bottom: 10px;

            font-weight: 750;
        }


        .welcome p {
            margin-top: 7px;

            color: rgba(255,255,255,0.85);

            font-size: 15px;
        }


        .account-type {
            display: inline-block;

            margin-top: 18px;

            padding: 7px 13px;

            background: rgba(255,255,255,0.16);

            border: 1px solid rgba(255,255,255,0.25);

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

            text-transform: capitalize;
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION HEADER
        |--------------------------------------------------------------------------
        */

        .section-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;
        }


        .section-header h2 {
            font-size: 22px;

            color: #111827;
        }


        .section-header p {
            color: #6b7280;

            font-size: 14px;
        }


        /*
        |--------------------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------------------
        */

        .cards {
            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(250px, 1fr));

            gap: 20px;
        }


        .card {
            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 25px;

            min-height: 220px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }


        .card:hover {
            transform: translateY(-4px);

            border-color: #cbd5e1;

            box-shadow:
                0 10px 25px rgba(0,0,0,0.07);
        }


        .card-icon {
            width: 48px;

            height: 48px;

            border-radius: 10px;

            background: #eff6ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 21px;

            font-weight: bold;

            margin-bottom: 18px;
        }


        .card h3 {
            font-size: 18px;

            color: #111827;

            margin-bottom: 9px;
        }


        .card p {
            color: #6b7280;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 20px;
        }


        .card-btn {
            display: inline-block;

            width: fit-content;

            background: #0d6efd;

            color: white;

            padding: 10px 17px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 650;

            transition: 0.2s;
        }


        .card-btn:hover {
            background: #0b5ed7;
        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE SUMMARY
        |--------------------------------------------------------------------------
        */

        .profile-section {
            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            margin-top: 30px;

            padding: 28px;
        }


        .profile-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 22px;
        }


        .profile-header h2 {
            font-size: 21px;

            color: #111827;
        }


        .profile-edit {
            color: #0d6efd;

            font-size: 14px;

            font-weight: 650;
        }


        .profile-edit:hover {
            text-decoration: underline;
        }


        .profile-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;
        }


        .profile-item {
            background: #f8fafc;

            border: 1px solid #eef0f3;

            border-radius: 9px;

            padding: 16px;
        }


        .profile-item small {
            display: block;

            color: #6b7280;

            font-size: 12px;

            margin-bottom: 6px;

            font-weight: 600;

            text-transform: uppercase;
        }


        .profile-item strong {
            color: #111827;

            font-size: 15px;

            word-break: break-word;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            text-align: center;

            padding: 30px 10px 10px;

            color: #9ca3af;

            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .navbar {
                height: auto;

                padding: 15px 20px;

                flex-direction: column;

                gap: 13px;

                align-items: stretch;
            }


            .brand {
                text-align: center;
            }


            .nav-right {
                justify-content: center;
            }


            .container {
                padding: 25px 15px;
            }


            .welcome {
                padding: 25px;

                text-align: center;
            }


            .welcome h1 {
                font-size: 25px;
            }


            .section-header {
                display: block;
            }


            .section-header p {
                margin-top: 5px;
            }


            .profile-grid {
                grid-template-columns: 1fr;
            }


            .profile-header {
                align-items: flex-start;
            }

        }


        @media (max-width: 450px) {

            .nav-right {
                flex-wrap: wrap;
            }


            .role-badge {
                display: none;
            }


            .profile-btn,
            .logout-btn {
                flex: 1;

                text-align: center;
            }


            .welcome {
                padding: 22px 18px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">


    <a href="dashboard.php" class="brand">
        Job <span>In India</span>
    </a>


    <div class="nav-right">

        <span class="role-badge">
            <?php echo htmlspecialchars($role); ?>
        </span>


        <a
            href="profile.php"
            class="profile-btn"
        >
            Profile
        </a>


        <a
            href="logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container">


    <!-- =====================================================
         WELCOME
    ====================================================== -->

    <section class="welcome">

        <h1>
            Welcome,
            <?php echo htmlspecialchars($name); ?>!
        </h1>


        <p>
            Manage your jobs, applications and profile
            from your Job In India dashboard.
        </p>


        <div class="account-type">

            Account Type:
            <?php echo htmlspecialchars(ucfirst($role)); ?>

        </div>

    </section>


    <!-- =====================================================
         QUICK ACTIONS
    ====================================================== -->

    <div class="section-header">

        <div>

            <h2>
                Quick Actions
            </h2>

            <p>
                Access the most important features of your account.
            </p>

        </div>

    </div>


    <section class="cards">


        <!-- =================================================
             FIND JOBS
        ================================================== -->

        <div class="card">

            <div>

                <div class="card-icon">
                    J
                </div>


                <h3>
                    Find Jobs
                </h3>


                <p>
                    Browse available job opportunities,
                    explore companies and find positions
                    that match your career goals.
                </p>

            </div>


            <a
                href="jobs.php"
                class="card-btn"
            >
                Browse Jobs →
            </a>

        </div>


        <!-- =================================================
             STUDENT APPLICATIONS
        ================================================== -->

        <?php if ($role === "student"): ?>

        <div class="card">

            <div>

                <div class="card-icon">
                    A
                </div>


                <h3>
                    My Applications
                </h3>


                <p>
                    Track your submitted applications
                    and check whether you have been
                    shortlisted, selected or rejected.
                </p>

            </div>


            <a
                href="my-applications.php"
                class="card-btn"
            >
                View Applications →
            </a>

        </div>

        <?php endif; ?>


        <!-- =================================================
             ADMIN ONLY - POST JOB
        ================================================== -->

        <?php if ($role === "admin"): ?>

        <div class="card">

            <div>

                <div class="card-icon">
                    +
                </div>


                <h3>
                    Post a Job
                </h3>


                <p>
                    Create and publish a new job opportunity
                    for students and candidates on the platform.
                </p>

            </div>


            <a
                href="post-job.php"
                class="card-btn"
            >
                Post New Job →
            </a>

        </div>

        <?php endif; ?>


        <!-- =================================================
             EMPLOYER + ADMIN - MY JOBS
        ================================================== -->

        <?php if ($role === "employer" || $role === "admin"): ?>

        <div class="card">

            <div>

                <div class="card-icon">
                    M
                </div>


                <h3>
                    My Posted Jobs
                </h3>


                <p>
                    View, edit and manage the jobs
                    associated with your account.
                </p>

            </div>


            <a
                href="my-jobs.php"
                class="card-btn"
            >
                Manage Jobs →
            </a>

        </div>


        <!-- =================================================
             APPLICATIONS
        ================================================== -->

        <div class="card">

            <div>

                <div class="card-icon">
                    C
                </div>


                <h3>
                    Job Applications
                </h3>


                <p>
                    Review candidates, view resumes
                    and update application statuses.
                </p>

            </div>


            <a
                href="applications.php"
                class="card-btn"
            >
                View Applications →
            </a>

        </div>

        <?php endif; ?>


        <!-- =================================================
             PROFILE
        ================================================== -->

        <div class="card">

            <div>

                <div class="card-icon">
                    P
                </div>


                <h3>
                    My Profile
                </h3>


                <p>
                    View your account information
                    and update your personal details.
                </p>

            </div>


            <a
                href="profile.php"
                class="card-btn"
            >
                View Profile →
            </a>

        </div>


    </section>


    <!-- =====================================================
         ACCOUNT INFORMATION
    ====================================================== -->

    <section class="profile-section">


        <div class="profile-header">

            <h2>
                Account Information
            </h2>


            <a
                href="profile.php"
                class="profile-edit"
            >
                Edit Profile →
            </a>

        </div>


        <div class="profile-grid">


            <div class="profile-item">

                <small>
                    Full Name
                </small>

                <strong>
                    <?php echo htmlspecialchars($name); ?>
                </strong>

            </div>


            <div class="profile-item">

                <small>
                    Email Address
                </small>

                <strong>
                    <?php echo htmlspecialchars($email); ?>
                </strong>

            </div>


            <div class="profile-item">

                <small>
                    Account Type
                </small>

                <strong>
                    <?php echo htmlspecialchars(ucfirst($role)); ?>
                </strong>

            </div>


        </div>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="footer">

        © <?php echo date("Y"); ?>
        Job In India. All rights reserved.

    </footer>


</main>


</body>

</html>