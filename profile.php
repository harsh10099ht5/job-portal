<?php

session_start();
require_once "config/database.php";

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$message = "";
$error = "";


/* =========================================================
   AUTO-CREATE PROFILE COLUMNS
   This fixes "Unknown column phone" type errors.
========================================================= */

$required_columns = [
    "phone" => "VARCHAR(20) NULL",
    "skills" => "TEXT NULL",
    "education" => "TEXT NULL"
];

foreach ($required_columns as $column => $definition) {

    $check_column = $conn->query(
        "SHOW COLUMNS FROM users LIKE '$column'"
    );

    if ($check_column && $check_column->num_rows === 0) {

        $conn->query(
            "ALTER TABLE users ADD COLUMN `$column` $definition"
        );
    }
}


/* =========================================================
   GET CURRENT USER
========================================================= */

$sql = "
    SELECT
        id,
        name,
        email,
        role,
        phone,
        skills,
        education
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User account not found.");
}

$user = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   UPDATE PROFILE
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $education = trim($_POST["education"] ?? "");


    /* Validation */

    if ($name === "") {

        $error = "Full name is required.";

    } elseif (strlen($name) < 2) {

        $error = "Name must contain at least 2 characters.";

    } elseif ($phone !== "" && !preg_match("/^[0-9+\-\s()]{7,20}$/", $phone)) {

        $error = "Please enter a valid phone number.";

    } else {

        $update_sql = "
            UPDATE users
            SET
                name = ?,
                phone = ?,
                skills = ?,
                education = ?
            WHERE id = ?
        ";

        $update_stmt = $conn->prepare($update_sql);

        if (!$update_stmt) {

            $error = "Unable to prepare profile update.";

        } else {

            $update_stmt->bind_param(
                "ssssi",
                $name,
                $phone,
                $skills,
                $education,
                $user_id
            );

            if ($update_stmt->execute()) {

                $message = "Profile updated successfully.";

                /* Update displayed information */
                $user["name"] = $name;
                $user["phone"] = $phone;
                $user["skills"] = $skills;
                $user["education"] = $education;

                /* Update session */
                $_SESSION["name"] = $name;

            } else {

                $error = "Unable to update profile. Please try again.";
            }

            $update_stmt->close();
        }
    }
}


/* =========================================================
   PROFILE COMPLETION
========================================================= */

$completion = 0;

if (!empty($user["name"])) {
    $completion += 25;
}

if (!empty($user["phone"])) {
    $completion += 25;
}

if (!empty($user["skills"])) {
    $completion += 25;
}

if (!empty($user["education"])) {
    $completion += 25;
}


/* =========================================================
   INITIAL LETTER
========================================================= */

$initial = strtoupper(substr($user["name"], 0, 1));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Profile - Job In India</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f7fb;

            color: #172033;

            min-height: 100vh;
        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {

            background: #ffffff;

            border-bottom: 1px solid #e5e7eb;

            padding: 16px 5%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            position: sticky;

            top: 0;

            z-index: 1000;
        }


        .brand {

            display: flex;

            align-items: center;

            gap: 10px;

            text-decoration: none;

            color: #1464f4;

            font-size: 23px;

            font-weight: 800;
        }


        .brand-icon {

            width: 38px;

            height: 38px;

            border-radius: 10px;

            background: #1464f4;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: bold;

            font-size: 18px;
        }


        .nav-links {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .nav-btn {

            text-decoration: none;

            padding: 9px 15px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #344054;

            transition: 0.2s;
        }


        .nav-btn:hover {

            background: #f1f5f9;
        }


        .nav-primary {

            background: #1464f4;

            color: white;
        }


        .nav-primary:hover {

            background: #0d52d6;
        }


        .nav-danger {

            color: #dc3545;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .container {

            max-width: 1050px;

            margin: 40px auto;

            padding: 0 20px;
        }


        .page-heading {

            margin-bottom: 25px;
        }


        .page-heading h1 {

            font-size: 32px;

            margin-bottom: 7px;

            color: #111827;
        }


        .page-heading p {

            color: #667085;

            font-size: 15px;
        }


        /* =====================================================
           LAYOUT
        ===================================================== */

        .profile-layout {

            display: grid;

            grid-template-columns: 300px 1fr;

            gap: 25px;

            align-items: start;
        }


        /* =====================================================
           SIDEBAR CARD
        ===================================================== */

        .profile-sidebar {

            background: white;

            border: 1px solid #e6eaf0;

            border-radius: 16px;

            padding: 28px;

            box-shadow:
                0 5px 20px rgba(16, 24, 40, 0.05);
        }


        .avatar {

            width: 90px;

            height: 90px;

            margin: 0 auto 17px;

            border-radius: 50%;

            background: linear-gradient(
                135deg,
                #1464f4,
                #4f8cff
            );

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 34px;

            font-weight: 800;
        }


        .profile-name {

            text-align: center;

            font-size: 21px;

            font-weight: 700;

            margin-bottom: 6px;
        }


        .profile-email {

            text-align: center;

            color: #667085;

            font-size: 13px;

            word-break: break-word;

            margin-bottom: 15px;
        }


        .role-badge {

            display: table;

            margin: 0 auto;

            background: #eef4ff;

            color: #1464f4;

            padding: 6px 13px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            text-transform: capitalize;
        }


        /* =====================================================
           COMPLETION
        ===================================================== */

        .completion {

            margin-top: 30px;
        }


        .completion-header {

            display: flex;

            justify-content: space-between;

            margin-bottom: 8px;

            font-size: 13px;

            font-weight: 600;
        }


        .completion-percent {

            color: #1464f4;
        }


        .progress {

            width: 100%;

            height: 8px;

            background: #e9edf3;

            border-radius: 20px;

            overflow: hidden;
        }


        .progress-bar {

            height: 100%;

            background: #1464f4;

            border-radius: 20px;

            transition: 0.3s;

            width: <?php echo $completion; ?>%;
        }


        .completion-text {

            color: #667085;

            font-size: 12px;

            margin-top: 9px;

            line-height: 1.5;
        }


        /* =====================================================
           FORM CARD
        ===================================================== */

        .profile-card {

            background: white;

            border: 1px solid #e6eaf0;

            border-radius: 16px;

            padding: 32px;

            box-shadow:
                0 5px 20px rgba(16, 24, 40, 0.05);
        }


        .card-heading {

            margin-bottom: 25px;

            padding-bottom: 18px;

            border-bottom: 1px solid #edf0f4;
        }


        .card-heading h2 {

            font-size: 21px;

            margin-bottom: 5px;
        }


        .card-heading p {

            color: #667085;

            font-size: 13px;
        }


        /* =====================================================
           ALERTS
        ===================================================== */

        .success {

            background: #ecfdf3;

            border: 1px solid #abefc6;

            color: #067647;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .error {

            background: #fef3f2;

            border: 1px solid #fecdca;

            color: #b42318;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;
        }


        .form-group {

            margin-bottom: 20px;
        }


        .full-width {

            grid-column: 1 / -1;
        }


        label {

            display: block;

            margin-bottom: 7px;

            color: #344054;

            font-size: 14px;

            font-weight: 600;
        }


        .required {

            color: #d92d20;
        }


        input,
        textarea {

            width: 100%;

            border: 1px solid #d0d5dd;

            background: white;

            border-radius: 9px;

            padding: 12px 13px;

            font-size: 14px;

            color: #101828;

            outline: none;

            transition: 0.2s;
        }


        input {

            height: 45px;
        }


        textarea {

            min-height: 120px;

            resize: vertical;

            line-height: 1.5;
        }


        input:focus,
        textarea:focus {

            border-color: #1464f4;

            box-shadow:
                0 0 0 3px rgba(20, 100, 244, 0.10);
        }


        .readonly {

            background: #f8fafc;

            color: #667085;

            cursor: not-allowed;
        }


        .field-help {

            display: block;

            margin-top: 6px;

            color: #98a2b3;

            font-size: 12px;
        }


        /* =====================================================
           BUTTONS
        ===================================================== */

        .actions {

            display: flex;

            gap: 12px;

            margin-top: 5px;

            padding-top: 20px;

            border-top: 1px solid #edf0f4;
        }


        .update-btn {

            border: none;

            background: #1464f4;

            color: white;

            padding: 12px 22px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;
        }


        .update-btn:hover {

            background: #0d52d6;

            transform: translateY(-1px);
        }


        .back-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            color: #344054;

            background: #f2f4f7;

            padding: 12px 20px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 600;
        }


        .back-btn:hover {

            background: #e4e7ec;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {

            text-align: center;

            color: #98a2b3;

            font-size: 13px;

            padding: 35px 0 20px;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 800px) {

            .profile-layout {

                grid-template-columns: 1fr;
            }

            .profile-sidebar {

                max-width: 100%;
            }

            .form-grid {

                grid-template-columns: 1fr;
            }

            .full-width {

                grid-column: auto;
            }

            .navbar {

                padding: 13px 20px;
            }

            .brand {

                font-size: 19px;
            }

            .brand-icon {

                width: 34px;

                height: 34px;
            }

            .nav-btn {

                padding: 8px 9px;

                font-size: 12px;
            }

        }


        @media (max-width: 500px) {

            .nav-links .website-link {

                display: none;
            }

            .container {

                margin-top: 25px;

                padding: 0 14px;
            }

            .profile-card {

                padding: 22px 18px;
            }

            .page-heading h1 {

                font-size: 27px;
            }

            .actions {

                flex-direction: column;
            }

            .update-btn,
            .back-btn {

                width: 100%;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <a href="index.php" class="brand">

        <span class="brand-icon">
            J
        </span>

        Job In India

    </a>


    <div class="nav-links">

        <a
            href="index.php"
            class="nav-btn website-link"
        >
            Website
        </a>

        <a
            href="dashboard.php"
            class="nav-btn nav-primary"
        >
            Dashboard
        </a>

        <a
            href="logout.php"
            class="nav-btn nav-danger"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container">


    <div class="page-heading">

        <h1>
            My Profile
        </h1>

        <p>
            Manage your personal and professional information.
        </p>

    </div>


    <div class="profile-layout">


        <!-- =================================================
             PROFILE SIDEBAR
        ================================================= -->

        <aside class="profile-sidebar">


            <div class="avatar">

                <?php
                echo htmlspecialchars($initial);
                ?>

            </div>


            <div class="profile-name">

                <?php
                echo htmlspecialchars($user["name"]);
                ?>

            </div>


            <div class="profile-email">

                <?php
                echo htmlspecialchars($user["email"]);
                ?>

            </div>


            <div class="role-badge">

                <?php
                echo htmlspecialchars($user["role"]);
                ?>

            </div>


            <!-- Profile Completion -->

            <div class="completion">

                <div class="completion-header">

                    <span>
                        Profile Completion
                    </span>

                    <span class="completion-percent">
                        <?php echo $completion; ?>%
                    </span>

                </div>


                <div class="progress">

                    <div class="progress-bar"></div>

                </div>


                <p class="completion-text">

                    Complete your profile to improve your
                    chances of finding suitable opportunities.

                </p>

            </div>


        </aside>


        <!-- =================================================
             PROFILE FORM
        ================================================= -->

        <section class="profile-card">


            <div class="card-heading">

                <h2>
                    Personal Information
                </h2>

                <p>
                    Keep your information accurate and up to date.
                </p>

            </div>


            <?php if ($message !== ""): ?>

                <div class="success">

                    ✓
                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="error">

                    !
                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="form-grid">


                    <!-- Name -->

                    <div class="form-group">

                        <label>
                            Full Name
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?php
                                echo htmlspecialchars($user["name"]);
                            ?>"
                            placeholder="Enter your full name"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="form-group">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="email"
                            value="<?php
                                echo htmlspecialchars($user["email"]);
                            ?>"
                            class="readonly"
                            readonly
                        >

                        <span class="field-help">
                            Email cannot be changed here.
                        </span>

                    </div>


                    <!-- Role -->

                    <div class="form-group">

                        <label>
                            Account Type
                        </label>

                        <input
                            type="text"
                            value="<?php
                                echo htmlspecialchars(
                                    ucfirst($user["role"])
                                );
                            ?>"
                            class="readonly"
                            readonly
                        >

                    </div>


                    <!-- Phone -->

                    <div class="form-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php
                                echo htmlspecialchars(
                                    $user["phone"] ?? ""
                                );
                            ?>"
                            placeholder="e.g. 9876543210"
                        >

                    </div>


                    <!-- Skills -->

                    <div class="form-group full-width">

                        <label>
                            Skills
                        </label>

                        <textarea
                            name="skills"
                            placeholder="e.g. Python, Java, SQL, Machine Learning, HTML, CSS, JavaScript"
                        ><?php
                            echo htmlspecialchars(
                                $user["skills"] ?? ""
                            );
                        ?></textarea>

                        <span class="field-help">
                            Add your technical and professional skills.
                        </span>

                    </div>


                    <!-- Education -->

                    <div class="form-group full-width">

                        <label>
                            Education
                        </label>

                        <textarea
                            name="education"
                            placeholder="e.g. B.Tech CSE - AI & ML, LNCT University, Bhopal"
                        ><?php
                            echo htmlspecialchars(
                                $user["education"] ?? ""
                            );
                        ?></textarea>

                        <span class="field-help">
                            Mention your degree, college and qualification.
                        </span>

                    </div>


                </div>


                <!-- ACTIONS -->

                <div class="actions">

                    <button
                        type="submit"
                        class="update-btn"
                    >
                        Save Changes
                    </button>


                    <a
                        href="dashboard.php"
                        class="back-btn"
                    >
                        ← Back to Dashboard
                    </a>

                </div>


            </form>


        </section>

    </div>


    <div class="footer">

        © <?php echo date("Y"); ?>
        Job In India. All rights reserved.

    </div>


</main>


</body>

</html>