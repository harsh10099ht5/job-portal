<?php

require_once "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($role)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    } elseif (!in_array($role, ["student", "employer"])) {

        $message = "Please select a valid account type.";
        $message_type = "error";

    } else {

        // Check if email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "This email is already registered.";
            $message_type = "error";

        } else {

            // Hash password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $role
            );

            if ($stmt->execute()) {

                $message = "Account created successfully! You can now login.";
                $message_type = "success";

                // Clear form values
                $name = "";
                $email = "";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
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

    <title>Create Account - Job In India</title>

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
            letter-spacing: -0.5px;
        }

        .login-link {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .login-link span {
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

        .register-wrapper {
            width: 100%;
            max-width: 1050px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            background: white;
            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.10);
        }

        /* ================= LEFT ================= */

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

        /* ================= FORM ================= */

        .form-section {
            padding: 45px;
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

        /* ================= MESSAGE ================= */

        .message {
            padding: 13px 15px;
            border-radius: 7px;
            margin-bottom: 20px;

            font-size: 14px;
            line-height: 1.4;
        }

        .message.success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .message.error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        /* ================= INPUT ================= */

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

            height: 48px;

            border: 1px solid #d7dce2;
            border-radius: 7px;

            padding: 0 14px 0 42px;

            font-size: 14px;

            outline: none;

            transition: 0.2s;

            background: #fff;
        }

        .form-control:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px rgba(13,110,253,0.10);
        }

        select.form-control {
            cursor: pointer;
        }

        /* ================= PASSWORD ================= */

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 70px;
        }

        .show-password {
            position: absolute;

            right: 12px;
            top: 50%;

            transform: translateY(-50%);

            border: none;
            background: none;

            color: #0d6efd;

            cursor: pointer;

            font-size: 12px;
            font-weight: 600;
        }

        /* ================= BUTTON ================= */

        .register-btn {
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

            margin-top: 5px;
        }

        .register-btn:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
        }

        /* ================= LOGIN ================= */

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

            .register-wrapper {
                grid-template-columns: 1fr;
                max-width: 550px;
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

            .login-link {
                font-size: 13px;
            }

            .login-link span {
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
        }

    </style>

</head>

<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="logo">
        Job In India
    </div>

    <a href="login.php" class="login-link">
        Already have an account?
        <span>Login</span>
    </a>

</nav>


<!-- ================= MAIN ================= -->

<main class="main">

    <div class="register-wrapper">


        <!-- ================= LEFT INFO ================= -->

        <section class="info-section">

            <h1>
                Build Your Future
                With Job In India
            </h1>

            <p>
                Create your account and connect with
                job opportunities, employers and career
                possibilities across India.
            </p>


            <div class="feature">

                <div class="feature-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Discover Opportunities
                    </h3>

                    <p>
                        Find jobs that match your skills
                        and career goals.
                    </p>

                </div>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Apply Easily
                    </h3>

                    <p>
                        Apply for jobs and track your
                        applications from one place.
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
                        Employers can discover talented
                        candidates and post opportunities.
                    </p>

                </div>

            </div>

        </section>


        <!-- ================= FORM ================= -->

        <section class="form-section">

            <div class="form-header">

                <h2>
                    Create Your Account
                </h2>

                <p>
                    Join Job In India today.
                </p>

            </div>


            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- NAME -->

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($name ?? ''); ?>"
                            required
                        >

                    </div>

                </div>


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
                            placeholder="Create a password"
                            minlength="6"
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


                <!-- ROLE -->

                <div class="form-group">

                    <label for="role">
                        Account Type
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ◉
                        </span>

                        <select
                            id="role"
                            name="role"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select account type
                            </option>

                            <option value="student">
                                Student / Job Seeker
                            </option>

                            <option value="employer">
                                Employer / Recruiter
                            </option>

                        </select>

                    </div>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="register-btn"
                >
                    Create Account
                </button>


            </form>


            <div class="bottom-text">

                Already registered?

                <a href="login.php">
                    Login to your account
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