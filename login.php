<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $sql = "
            SELECT id, name, email, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error. Please try again.";

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {

                    // Create session
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["name"] = $user["name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];

                    // Login successful
                    header("Location: dashboard.php");
                    exit();

                } else {

                    $error = "Invalid email or password.";
                }

            } else {

                $error = "Invalid email or password.";
            }

            $stmt->close();
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

    <title>Login - Job In India</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            min-height: 100vh;

            display: flex;
            flex-direction: column;
        }

        /* ================= NAVBAR ================= */

        .navbar {
            height: 70px;

            background: #0d6efd;
            color: white;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 6%;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
        }

        .register-link {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .register-link span {
            background: white;
            color: #0d6efd;

            padding: 9px 18px;

            border-radius: 6px;

            font-weight: 600;

            margin-left: 8px;
        }

        /* ================= MAIN ================= */

        .main {
            flex: 1;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;

            display: grid;

            grid-template-columns: 1fr 1fr;

            background: white;

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.10);
        }

        /* ================= LEFT SECTION ================= */

        .info-section {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #084298
            );

            color: white;

            padding: 55px 45px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }

        .info-section h1 {
            font-size: 38px;

            line-height: 1.15;

            margin-bottom: 18px;
        }

        .info-section > p {
            font-size: 16px;

            line-height: 1.7;

            opacity: 0.9;

            margin-bottom: 30px;
        }

        .feature {
            display: flex;

            align-items: flex-start;

            gap: 14px;

            margin-bottom: 20px;
        }

        .feature-icon {
            width: 38px;
            height: 38px;

            background: rgba(255,255,255,0.18);

            border-radius: 8px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 18px;

            flex-shrink: 0;
        }

        .feature h3 {
            font-size: 15px;

            margin-bottom: 4px;
        }

        .feature p {
            font-size: 13px;

            opacity: 0.8;

            line-height: 1.4;
        }

        /* ================= FORM SECTION ================= */

        .form-section {
            padding: 55px 45px;
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h2 {
            font-size: 28px;

            color: #222;

            margin-bottom: 8px;
        }

        .form-header p {
            color: #777;

            font-size: 14px;
        }

        /* ================= ERROR ================= */

        .error {
            background: #f8d7da;

            color: #842029;

            border: 1px solid #f5c2c7;

            padding: 13px 15px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.4;
        }

        /* ================= FORM GROUP ================= */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            font-size: 14px;

            font-weight: 600;

            color: #333;

            margin-bottom: 8px;
        }

        /* ================= INPUT ================= */

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #888;

            font-size: 15px;
        }

        .form-control {
            width: 100%;

            height: 50px;

            border: 1px solid #d7dce2;

            border-radius: 7px;

            padding: 0 14px 0 42px;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px rgba(13,110,253,0.10);
        }

        /* ================= PASSWORD ================= */

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 65px;
        }

        .show-password {
            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #0d6efd;

            cursor: pointer;

            font-size: 12px;

            font-weight: 600;
        }

        /* ================= OPTIONS ================= */

        .options {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: -5px;

            margin-bottom: 22px;

            font-size: 13px;
        }

        .remember {
            display: flex;

            align-items: center;

            gap: 7px;

            color: #666;
        }

        .remember input {
            width: 14px;
            height: 14px;

            cursor: pointer;
        }

        .forgot {
            color: #0d6efd;

            text-decoration: none;

            font-weight: 600;
        }

        .forgot:hover {
            text-decoration: underline;
        }

        /* ================= LOGIN BUTTON ================= */

        .login-btn {
            width: 100%;

            height: 50px;

            border: none;

            border-radius: 7px;

            background: #0d6efd;

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;
        }

        .login-btn:hover {
            background: #0b5ed7;

            transform: translateY(-1px);
        }

        /* ================= REGISTER ================= */

        .bottom-text {
            text-align: center;

            margin-top: 22px;

            font-size: 14px;

            color: #666;
        }

        .bottom-text a {
            color: #0d6efd;

            text-decoration: none;

            font-weight: 600;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        /* ================= FOOTER ================= */

        .footer {
            text-align: center;

            padding: 20px;

            color: #777;

            font-size: 13px;
        }

        /* ================= MOBILE ================= */

        @media (max-width: 800px) {

            .login-wrapper {
                grid-template-columns: 1fr;

                max-width: 520px;
            }

            .info-section {
                padding: 35px;
            }

            .info-section h1 {
                font-size: 30px;
            }

            .form-section {
                padding: 35px;
            }
        }

        @media (max-width: 500px) {

            .navbar {
                padding: 0 20px;
            }

            .logo {
                font-size: 20px;
            }

            .register-link {
                font-size: 13px;
            }

            .register-link span {
                padding: 8px 12px;
            }

            .main {
                padding: 25px 15px;
            }

            .info-section {
                padding: 30px 25px;
            }

            .form-section {
                padding: 30px 25px;
            }

            .info-section h1 {
                font-size: 27px;
            }

            .options {
                flex-direction: column;

                align-items: flex-start;

                gap: 10px;
            }
        }

    </style>

</head>

<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="logo">
        Job In India
    </div>

    <a
        href="register.php"
        class="register-link"
    >
        New to Job In India?
        <span>Register</span>
    </a>

</nav>


<!-- ================= MAIN ================= -->

<main class="main">

    <div class="login-wrapper">


        <!-- ================= LEFT ================= -->

        <section class="info-section">

            <h1>
                Welcome Back
            </h1>

            <p>
                Sign in to your Job In India account
                and continue exploring career opportunities.
            </p>


            <div class="feature">

                <div class="feature-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Find Your Next Job
                    </h3>

                    <p>
                        Explore opportunities that match
                        your skills and career goals.
                    </p>

                </div>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Track Applications
                    </h3>

                    <p>
                        Keep track of your applications
                        and their current status.
                    </p>

                </div>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Connect With Employers
                    </h3>

                    <p>
                        Discover companies and opportunities
                        across India.
                    </p>

                </div>

            </div>

        </section>


        <!-- ================= LOGIN FORM ================= -->

        <section class="form-section">

            <div class="form-header">

                <h2>
                    Login to Your Account
                </h2>

                <p>
                    Enter your credentials to continue.
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ✉
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email address"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword()"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <!-- OPTIONS -->

                <div class="options">

                    <label class="remember">

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        Remember me

                    </label>

                    <a
                        href="#"
                        class="forgot"
                    >
                        Forgot Password?
                    </a>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-btn"
                >
                    Login
                </button>


            </form>


            <div class="bottom-text">

                Don't have an account?

                <a href="register.php">
                    Create an account
                </a>

            </div>

        </section>

    </div>

</main>


<!-- ================= FOOTER ================= -->

<footer class="footer">

    © <?php echo date("Y"); ?>
    Job In India. All rights reserved.

</footer>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.querySelector(".show-password");

    if (password.type === "password") {

        password.type = "text";

        button.textContent = "Hide";

    } else {

        password.type = "password";

        button.textContent = "Show";
    }
}

</script>


</body>

</html>