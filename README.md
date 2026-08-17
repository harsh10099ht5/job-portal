# Job Portal

A full-stack job portal designed to connect students and job seekers with employers through a simple and structured recruitment platform.

## Overview

The Job Portal provides separate user roles for **students/job seekers** and **employers**. Users can create accounts, authenticate securely, and interact with job listings.

The backend is built using **Node.js, Express.js, MongoDB, and Mongoose**, with password hashing and JWT-based authentication.

## Features

* User registration and login
* Student and employer roles
* Secure password hashing using bcrypt
* JWT-based authentication
* Employer job posting
* Job listing and retrieval
* MongoDB database integration
* REST API based backend
* CORS support
* Environment-based configuration

## Tech Stack

### Backend

* Node.js
* Express.js
* MongoDB
* Mongoose
* JWT
* bcrypt.js
* CORS
* dotenv

### Frontend

* JavaScript
* HTML/CSS
* JavaScript-based frontend components

## Project Architecture

```text
Job Portal
│
├── Frontend
│   ├── Login
│   ├── Job Listings
│   └── User Interface
│
└── Backend
    ├── Authentication
    ├── User Management
    ├── Job Management
    └── MongoDB Database
```

## API Endpoints

| Method | Endpoint    | Description                   |
| ------ | ----------- | ----------------------------- |
| POST   | `/signup`   | Register a new user           |
| POST   | `/login`    | Authenticate an existing user |
| POST   | `/post-job` | Create a new job listing      |
| GET    | `/jobs`     | Retrieve available jobs       |

## User Roles

### Student / Job Seeker

* Create an account
* Log in securely
* Maintain skills information
* Browse available job listings

### Employer

* Create an employer account
* Log in securely
* Post job opportunities
* Manage job-related data

## Database Models

### User

The User model contains information such as:

* Name
* Email
* Password
* Role
* Skills
* Company

### Job

The Job model contains:

* Job title
* Company
* Location
* Description
* Required skills
* Posting user reference

## Authentication

Passwords are hashed using **bcrypt.js** before being stored in the database.

JWT tokens are generated after successful authentication and contain the authenticated user's ID and role.

## Environment Variables

Create a `.env` file in the backend directory:

```env
MONGO_URI=your_mongodb_connection_string
JWT_SECRET=your_jwt_secret
PORT=10000
```

Never commit your actual `.env` file to GitHub.

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/harsh10099ht5/job-portal.git
cd job-portal
```

### 2. Install dependencies

```bash
npm install
```

### 3. Configure environment variables

Create a `.env` file and add your MongoDB connection string and JWT secret.

### 4. Start the server

```bash
node server.js
```

The server will run on the configured port.

## Future Improvements

* Advanced job search and filtering
* Application management
* Role-based route protection
* Employer dashboard
* Student profile dashboard
* Resume upload
* Email notifications
* Improved API validation
* Production deployment
* Automated testing

## Project Status

**Status:** In Development

This project is being developed as a full-stack web application to demonstrate practical implementation of authentication, REST APIs, database integration, and job management.

## Author

**Harshit Tripathi**

Computer Science & Engineering — AI & ML

GitHub: [@harsh10099ht5](https://github.com/harsh10099ht5)
