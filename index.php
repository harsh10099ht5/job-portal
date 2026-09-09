<?php
session_start();

$isLoggedIn = isset($_SESSION["user_id"]);

$name = $_SESSION["name"] ?? "";
$role = strtolower($_SESSION["role"] ?? "");

if ($role === "user") {
    $role = "student";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Job In India is a professional job platform helping students, freshers and professionals discover career opportunities across India.">

    <meta name="keywords"
          content="jobs in India, fresher jobs, student jobs, internships, career opportunities">

    <title>Job In India | Find Your Next Career Opportunity</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #202124;

            background: #ffffff;

            line-height: 1.6;
        }


        a {
            text-decoration: none;
            color: inherit;
        }


        button,
        input {
            font-family: inherit;
        }


        /* =========================================
           NAVBAR
        ========================================= */

        .navbar {

            position: sticky;

            top: 0;

            z-index: 1000;

            background: rgba(255,255,255,0.96);

            border-bottom: 1px solid #e8eaed;

            backdrop-filter: blur(10px);
        }


        .nav-container {

            max-width: 1240px;

            margin: auto;

            padding: 16px 28px;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .logo {

            display: flex;

            align-items: center;

            gap: 10px;

            font-size: 22px;

            font-weight: 700;

            color: #1a73e8;
        }


        .logo-mark {

            width: 36px;

            height: 36px;

            border-radius: 9px;

            background: #1a73e8;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 17px;

            font-weight: 700;
        }


        .logo-text span {

            color: #202124;
        }


        .nav-links {

            display: flex;

            align-items: center;

            gap: 30px;
        }


        .nav-links a {

            font-size: 14px;

            font-weight: 600;

            color: #5f6368;

            transition: 0.2s;
        }


        .nav-links a:hover {

            color: #1a73e8;
        }


        .nav-actions {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 42px;

            padding: 0 19px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;

            border: 1px solid transparent;

            transition: 0.2s;

            cursor: pointer;
        }


        .btn-outline {

            border-color: #dadce0;

            background: white;

            color: #1a73e8;
        }


        .btn-outline:hover {

            background: #f8faff;

            border-color: #1a73e8;
        }


        .btn-primary {

            background: #1a73e8;

            color: white;
        }


        .btn-primary:hover {

            background: #1558b0;
        }


        .btn-dark {

            background: #202124;

            color: white;
        }


        .btn-dark:hover {

            background: #000000;
        }


        /* =========================================
           HERO
        ========================================= */

        .hero {

            background:

                radial-gradient(
                    circle at 85% 15%,
                    #e8f0fe 0,
                    transparent 28%
                ),

                linear-gradient(
                    180deg,
                    #ffffff 0%,
                    #f8fbff 100%
                );

            padding: 90px 25px 100px;
        }


        .hero-container {

            max-width: 1150px;

            margin: auto;

            text-align: center;
        }


        .hero-label {

            display: inline-block;

            padding: 7px 14px;

            border-radius: 20px;

            background: #e8f0fe;

            color: #185abc;

            font-size: 13px;

            font-weight: 700;

            margin-bottom: 22px;
        }


        .hero h1 {

            max-width: 850px;

            margin: auto;

            font-size: 58px;

            line-height: 1.08;

            letter-spacing: -1.8px;

            color: #202124;
        }


        .hero h1 span {

            color: #1a73e8;
        }


        .hero-description {

            max-width: 680px;

            margin: 25px auto 38px;

            color: #5f6368;

            font-size: 18px;

            line-height: 1.7;
        }


        /* =========================================
           SEARCH
        ========================================= */

        .search-wrapper {

            max-width: 930px;

            margin: auto;

            background: white;

            padding: 9px;

            border: 1px solid #dadce0;

            border-radius: 12px;

            box-shadow:
                0 8px 30px rgba(60,64,67,0.12);
        }


        .search-box {

            display: grid;

            grid-template-columns:
                1fr 1fr auto;

            gap: 8px;
        }


        .search-field {

            display: flex;

            align-items: center;

            padding: 0 15px;

            border: 1px solid #e0e3e7;

            border-radius: 8px;

            background: white;
        }


        .search-icon {

            color: #5f6368;

            margin-right: 10px;

            font-size: 17px;
        }


        .search-field input {

            width: 100%;

            height: 50px;

            border: none;

            outline: none;

            font-size: 14px;

            color: #202124;
        }


        .search-button {

            border: none;

            padding: 0 27px;

            border-radius: 8px;

            background: #1a73e8;

            color: white;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;
        }


        .search-button:hover {

            background: #1558b0;
        }


        .hero-note {

            margin-top: 18px;

            color: #80868b;

            font-size: 13px;
        }


        /* =========================================
           STATS
        ========================================= */

        .stats-section {

            margin-top: -42px;

            position: relative;

            padding: 0 25px;
        }


        .stats {

            max-width: 1050px;

            margin: auto;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            background: white;

            border: 1px solid #e0e3e7;

            border-radius: 12px;

            box-shadow:
                0 8px 28px rgba(60,64,67,0.10);

            overflow: hidden;
        }


        .stat {

            text-align: center;

            padding: 25px;

            border-right: 1px solid #eeeeee;
        }


        .stat:last-child {

            border-right: none;
        }


        .stat h2 {

            font-size: 28px;

            color: #1a73e8;

            margin-bottom: 3px;
        }


        .stat p {

            font-size: 13px;

            color: #6b7280;
        }


        /* =========================================
           GENERAL SECTION
        ========================================= */

        .section {

            max-width: 1180px;

            margin: auto;

            padding: 90px 25px;
        }


        .section-header {

            text-align: center;

            margin-bottom: 48px;
        }


        .section-label {

            color: #1a73e8;

            font-size: 13px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.8px;

            margin-bottom: 10px;
        }


        .section-header h2 {

            font-size: 36px;

            color: #202124;

            letter-spacing: -0.6px;
        }


        .section-header p {

            max-width: 650px;

            margin: 12px auto 0;

            color: #6b7280;

            font-size: 15px;
        }


        /* =========================================
           CATEGORIES
        ========================================= */

        .category-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;
        }


        .category {

            padding: 24px;

            border: 1px solid #e0e3e7;

            border-radius: 10px;

            background: white;

            transition: 0.2s;
        }


        .category:hover {

            border-color: #aecbfa;

            box-shadow:
                0 8px 25px rgba(60,64,67,0.10);

            transform: translateY(-3px);
        }


        .category-icon {

            width: 42px;

            height: 42px;

            border-radius: 8px;

            background: #f1f6ff;

            color: #1a73e8;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 17px;

            font-weight: 700;

            margin-bottom: 17px;
        }


        .category h3 {

            font-size: 16px;

            margin-bottom: 5px;
        }


        .category p {

            font-size: 13px;

            color: #70757a;
        }


        /* =========================================
           HOW IT WORKS
        ========================================= */

        .process-section {

            background: #f8f9fa;

            border-top: 1px solid #f1f3f4;

            border-bottom: 1px solid #f1f3f4;
        }


        .process-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }


        .process-card {

            background: white;

            border: 1px solid #e0e3e7;

            border-radius: 12px;

            padding: 32px;
        }


        .process-number {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            background: #e8f0fe;

            color: #1a73e8;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 14px;

            font-weight: 700;

            margin-bottom: 20px;
        }


        .process-card h3 {

            font-size: 18px;

            margin-bottom: 10px;
        }


        .process-card p {

            color: #6b7280;

            font-size: 14px;

            line-height: 1.7;
        }


        /* =========================================
           TWO COLUMN CAREER SECTION
        ========================================= */

        .career-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 25px;
        }


        .career-card {

            padding: 42px;

            border-radius: 14px;

            border: 1px solid #e0e3e7;

            background: white;
        }


        .career-card.employer {

            background: #f8fbff;

            border-color: #d2e3fc;
        }


        .career-card h3 {

            font-size: 25px;

            margin-bottom: 12px;
        }


        .career-card p {

            color: #6b7280;

            font-size: 15px;

            margin-bottom: 25px;

            line-height: 1.7;
        }


        .career-list {

            list-style: none;

            margin-bottom: 28px;
        }


        .career-list li {

            margin-bottom: 10px;

            font-size: 14px;

            color: #4b5563;
        }


        .career-list li::before {

            content: "✓";

            color: #1a73e8;

            font-weight: bold;

            margin-right: 10px;
        }


        /* =========================================
           TRUST SECTION
        ========================================= */

        .trust {

            background: #202124;

            color: white;

            border-radius: 16px;

            padding: 55px;

            display: grid;

            grid-template-columns: 1.2fr 1fr;

            gap: 50px;

            align-items: center;
        }


        .trust h2 {

            font-size: 34px;

            margin-bottom: 15px;
        }


        .trust p {

            color: #bdc1c6;

            font-size: 15px;

            line-height: 1.7;
        }


        .trust-items {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;
        }


        .trust-item {

            border: 1px solid #3c4043;

            border-radius: 9px;

            padding: 20px;
        }


        .trust-item strong {

            display: block;

            font-size: 15px;

            margin-bottom: 5px;
        }


        .trust-item span {

            font-size: 12px;

            color: #9aa0a6;
        }


        /* =========================================
           CTA
        ========================================= */

        .cta {

            text-align: center;

            padding: 95px 25px;
        }


        .cta h2 {

            font-size: 38px;

            margin-bottom: 13px;
        }


        .cta p {

            color: #6b7280;

            max-width: 600px;

            margin: auto auto 28px;

            font-size: 15px;
        }


        .cta-buttons {

            display: flex;

            justify-content: center;

            gap: 10px;
        }


        /* =========================================
           FOOTER
        ========================================= */

        footer {

            background: #f8f9fa;

            border-top: 1px solid #e8eaed;

            padding: 55px 25px 25px;
        }


        .footer-container {

            max-width: 1180px;

            margin: auto;

            display: grid;

            grid-template-columns:
                2fr 1fr 1fr 1fr;

            gap: 50px;
        }


        .footer-brand {

            font-size: 19px;

            font-weight: 700;

            color: #1a73e8;

            margin-bottom: 12px;
        }


        .footer-description {

            max-width: 360px;

            color: #70757a;

            font-size: 13px;

            line-height: 1.7;
        }


        footer h4 {

            font-size: 14px;

            margin-bottom: 15px;

            color: #202124;
        }


        footer a {

            display: block;

            color: #70757a;

            font-size: 13px;

            margin-bottom: 10px;
        }


        footer a:hover {

            color: #1a73e8;
        }


        .copyright {

            max-width: 1180px;

            margin: 40px auto 0;

            padding-top: 20px;

            border-top: 1px solid #dadce0;

            text-align: center;

            color: #80868b;

            font-size: 12px;
        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 900px) {

            .nav-links {
                display: none;
            }


            .hero h1 {
                font-size: 44px;
            }


            .category-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .stat:nth-child(2) {
                border-right: none;
            }


            .stat:nth-child(-n+2) {
                border-bottom: 1px solid #eeeeee;
            }


            .process-grid {
                grid-template-columns: 1fr;
            }


            .footer-container {
                grid-template-columns:
                    1fr 1fr;
            }


            .trust {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 650px) {

            .nav-container {
                padding: 13px 18px;
            }


            .logo {
                font-size: 18px;
            }


            .logo-mark {
                width: 32px;
                height: 32px;
            }


            .nav-actions .btn {
                min-height: 38px;
                padding: 0 12px;
                font-size: 12px;
            }


            .hero {
                padding: 65px 18px 80px;
            }


            .hero h1 {
                font-size: 37px;
                letter-spacing: -1px;
            }


            .hero-description {
                font-size: 16px;
            }


            .search-box {
                grid-template-columns: 1fr;
            }


            .search-button {
                height: 50px;
            }


            .stats-section {
                padding: 0 18px;
            }


            .section {
                padding: 65px 18px;
            }


            .category-grid {
                grid-template-columns: 1fr;
            }


            .career-grid {
                grid-template-columns: 1fr;
            }


            .career-card {
                padding: 28px;
            }


            .trust {
                padding: 32px 24px;
            }


            .trust h2 {
                font-size: 28px;
            }


            .trust-items {
                grid-template-columns: 1fr;
            }


            .cta {
                padding: 70px 20px;
            }


            .cta h2 {
                font-size: 30px;
            }


            .cta-buttons {
                flex-direction: column;
            }


            .cta-buttons .btn {
                width: 100%;
            }


            .footer-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

    </style>

</head>


<body>


<!-- ==================================================
     NAVBAR
================================================== -->

<header class="navbar">

    <div class="nav-container">


        <a href="index.php" class="logo">

            <div class="logo-mark">
                J
            </div>

            <div class="logo-text">
                Job<span>InIndia</span>
            </div>

        </a>


        <nav class="nav-links">

            <a href="index.php">
                Home
            </a>

            <a href="jobs.php">
                Find Jobs
            </a>

            <a href="#categories">
                Categories
            </a>

            <a href="#how-it-works">
                How It Works
            </a>

        </nav>


        <div class="nav-actions">

            <?php if ($isLoggedIn): ?>

                <a href="dashboard.php"
                   class="btn btn-outline">
                    Dashboard
                </a>

                <?php if ($role === "admin"): ?>

                    <a href="post-job.php"
                       class="btn btn-primary">
                        Post a Job
                    </a>

                <?php endif; ?>

                <a href="logout.php"
                   class="btn btn-dark">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php"
                   class="btn btn-outline">
                    Sign in
                </a>

                <a href="register.php"
                   class="btn btn-primary">
                    Create account
                </a>

            <?php endif; ?>

        </div>

    </div>

</header>



<!-- ==================================================
     HERO
================================================== -->

<section class="hero">

    <div class="hero-container">

        <div class="hero-label">
            India's Career & Job Discovery Platform
        </div>


        <h1>

            Find work that

            <span>
                moves you forward.
            </span>

        </h1>


        <p class="hero-description">

            Discover verified career opportunities, internships
            and jobs from companies across India. Built for
            students, freshers and professionals.

        </p>


        <div class="search-wrapper">

            <form
                action="jobs.php"
                method="GET"
                class="search-box"
            >

                <div class="search-field">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Job title, skills or keywords"
                    >

                </div>


                <div class="search-field">

                    <span class="search-icon">
                        ◉
                    </span>

                    <input
                        type="text"
                        name="location"
                        placeholder="Location"
                    >

                </div>


                <button
                    type="submit"
                    class="search-button"
                >
                    Search Jobs
                </button>

            </form>

        </div>


        <p class="hero-note">

            Search opportunities by role, skills, company or location.

        </p>

    </div>

</section>



<!-- ==================================================
     STATISTICS
================================================== -->

<section class="stats-section">

    <div class="stats">

        <div class="stat">

            <h2>100+</h2>

            <p>
                Job Opportunities
            </p>

        </div>


        <div class="stat">

            <h2>50+</h2>

            <p>
                Companies
            </p>

        </div>


        <div class="stat">

            <h2>1,000+</h2>

            <p>
                Job Seekers
            </p>

        </div>


        <div class="stat">

            <h2>Pan India</h2>

            <p>
                Opportunities
            </p>

        </div>

    </div>

</section>



<!-- ==================================================
     CATEGORIES
================================================== -->

<section class="section"
         id="categories">

    <div class="section-header">

        <div class="section-label">
            Explore opportunities
        </div>

        <h2>
            Find jobs by category
        </h2>

        <p>
            Explore career opportunities across the most
            in-demand professional fields.
        </p>

    </div>


    <div class="category-grid">


        <a href="jobs.php?search=Software"
           class="category">

            <div class="category-icon">
                IT
            </div>

            <h3>
                Software & IT
            </h3>

            <p>
                Software development, engineering and technology roles.
            </p>

        </a>


        <a href="jobs.php?search=Data"
           class="category">

            <div class="category-icon">
                DS
            </div>

            <h3>
                Data & AI
            </h3>

            <p>
                Data science, analytics, AI and machine learning roles.
            </p>

        </a>


        <a href="jobs.php?search=Marketing"
           class="category">

            <div class="category-icon">
                MK
            </div>

            <h3>
                Marketing
            </h3>

            <p>
                Digital marketing, sales and growth opportunities.
            </p>

        </a>


        <a href="jobs.php?search=Finance"
           class="category">

            <div class="category-icon">
                FN
            </div>

            <h3>
                Finance
            </h3>

            <p>
                Finance, accounting and business opportunities.
            </p>

        </a>


        <a href="jobs.php?search=Design"
           class="category">

            <div class="category-icon">
                UX
            </div>

            <h3>
                Design
            </h3>

            <p>
                UI/UX, graphic and product design careers.
            </p>

        </a>


        <a href="jobs.php?search=Internship"
           class="category">

            <div class="category-icon">
                IN
            </div>

            <h3>
                Internships
            </h3>

            <p>
                Start your professional journey with internships.
            </p>

        </a>


        <a href="jobs.php?search=Management"
           class="category">

            <div class="category-icon">
                MG
            </div>

            <h3>
                Management
            </h3>

            <p>
                Business, operations and management positions.
            </p>

        </a>


        <a href="jobs.php"
           class="category">

            <div class="category-icon">
                +
            </div>

            <h3>
                View All Jobs
            </h3>

            <p>
                Explore all available opportunities.
            </p>

        </a>


    </div>

</section>



<!-- ==================================================
     HOW IT WORKS
================================================== -->

<section class="process-section"
         id="how-it-works">

    <div class="section">

        <div class="section-header">

            <div class="section-label">
                Simple process
            </div>

            <h2>
                Your next opportunity starts here
            </h2>

            <p>
                A straightforward way to discover and apply
                for the right career opportunities.
            </p>

        </div>


        <div class="process-grid">


            <div class="process-card">

                <div class="process-number">
                    01
                </div>

                <h3>
                    Create your profile
                </h3>

                <p>
                    Build a professional profile with your
                    education, skills and career information.
                </p>

            </div>


            <div class="process-card">

                <div class="process-number">
                    02
                </div>

                <h3>
                    Discover opportunities
                </h3>

                <p>
                    Search jobs using keywords, skills,
                    categories and preferred locations.
                </p>

            </div>


            <div class="process-card">

                <div class="process-number">
                    03
                </div>

                <h3>
                    Apply and track
                </h3>

                <p>
                    Apply for suitable positions and keep
                    track of your applications from your dashboard.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     STUDENT / EMPLOYER
================================================== -->

<section class="section">

    <div class="section-header">

        <div class="section-label">
            Built for both sides
        </div>

        <h2>
            One platform. Two possibilities.
        </h2>

    </div>


    <div class="career-grid">


        <div class="career-card">

            <h3>
                For Job Seekers
            </h3>

            <p>
                Whether you are a student, fresher or
                experienced professional, find opportunities
                aligned with your career goals.
            </p>


            <ul class="career-list">

                <li>
                    Search jobs by skills and location
                </li>

                <li>
                    Create your professional profile
                </li>

                <li>
                    Apply for relevant positions
                </li>

                <li>
                    Track application status
                </li>

            </ul>


            <a href="jobs.php"
               class="btn btn-primary">

                Explore Jobs

            </a>

        </div>



        <div class="career-card employer">

            <h3>
                For Employers
            </h3>

            <p>
                Reach potential candidates and manage
                opportunities through a centralized platform.
            </p>


            <ul class="career-list">

                <li>
                    Publish job opportunities
                </li>

                <li>
                    Reach qualified candidates
                </li>

                <li>
                    Review applications
                </li>

                <li>
                    Manage hiring opportunities
                </li>

            </ul>


            <?php if ($role === "admin"): ?>

                <a href="post-job.php"
                   class="btn btn-primary">

                    Post a Job

                </a>

            <?php else: ?>

                <a href="register.php"
                   class="btn btn-outline">

                    Create Account

                </a>

            <?php endif; ?>

        </div>


    </div>

</section>



<!-- ==================================================
     TRUST
================================================== -->

<section class="section">

    <div class="trust">


        <div>

            <div class="section-label">
                Built with purpose
            </div>

            <h2>
                A simpler way to navigate your career.
            </h2>

            <p>
                Job In India brings job discovery, profiles
                and applications together in one focused
                platform designed for the modern job seeker.
            </p>

        </div>


        <div class="trust-items">


            <div class="trust-item">

                <strong>
                    Secure Accounts
                </strong>

                <span>
                    Protected user authentication
                </span>

            </div>


            <div class="trust-item">

                <strong>
                    Professional Profiles
                </strong>

                <span>
                    Present your skills and education
                </span>

            </div>


            <div class="trust-item">

                <strong>
                    Application Tracking
                </strong>

                <span>
                    Keep track of your applications
                </span>

            </div>


            <div class="trust-item">

                <strong>
                    Career Focused
                </strong>

                <span>
                    Designed for real opportunities
                </span>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     CTA
================================================== -->

<section class="cta">

    <h2>
        Ready for your next opportunity?
    </h2>


    <p>
        Create your profile and start exploring
        career opportunities across India.
    </p>


    <div class="cta-buttons">


        <?php if ($isLoggedIn): ?>

            <a href="jobs.php"
               class="btn btn-primary">

                Explore Jobs

            </a>

            <a href="profile.php"
               class="btn btn-outline">

                View Profile

            </a>

        <?php else: ?>

            <a href="register.php"
               class="btn btn-primary">

                Create Free Account

            </a>

            <a href="login.php"
               class="btn btn-outline">

                Sign In

            </a>

        <?php endif; ?>


    </div>

</section>



<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    <div class="footer-container">


        <div>

            <div class="footer-brand">
                Job In India
            </div>


            <p class="footer-description">

                A professional job discovery platform
                connecting students, freshers and job
                seekers with career opportunities across India.

            </p>

        </div>


        <div>

            <h4>
                Platform
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="jobs.php">
                Find Jobs
            </a>

            <a href="#categories">
                Categories
            </a>

            <a href="#how-it-works">
                How It Works
            </a>

        </div>


        <div>

            <h4>
                Account
            </h4>

            <?php if ($isLoggedIn): ?>

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="profile.php">
                    Profile
                </a>

                <a href="logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php">
                    Sign In
                </a>

                <a href="register.php">
                    Register
                </a>

            <?php endif; ?>

        </div>


        <div>

            <h4>
                For Employers
            </h4>

            <?php if ($role === "admin"): ?>

                <a href="post-job.php">
                    Post a Job
                </a>

                <a href="my-jobs.php">
                    Manage Jobs
                </a>

                <a href="applications.php">
                    Applications
                </a>

            <?php else: ?>

                <a href="register.php">
                    Create Account
                </a>

            <?php endif; ?>

        </div>


    </div>


    <div class="copyright">

        © <?php echo date("Y"); ?>

        Job In India. All rights reserved.

    </div>

</footer>


</body>

</html>