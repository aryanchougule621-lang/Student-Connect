<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Connect | Student Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <div class="brand-icon">SC</div>
            <div class="brand-text">
                <h1>Student Connect</h1>
                <p>Your journey starts here</p>
            </div>
        </div>
    </header>

    <main class="page-container">
        <section class="welcome-section">
            <span class="eyebrow">WELCOME TO STUDENT CONNECT</span>
            <h2>Let's get you registered.</h2>
            <p>
                Fill in your details below to complete your student
                registration. Please provide accurate information.
            </p>

            <div class="program-description">
                <h3>About Student Connect</h3>
                <p>
                    Student Connect is a student registration platform that collects
                    student information and organizes registrations by college and
                    academic year. It helps administrators view registration totals,
                    manage student records, and monitor registration statuses.
                </p>
            </div>
        </section>

        <section class="registration-card">
            <div class="card-heading">
                <div>
                    <h3>Student Registration</h3>
                    <p>Enter your information in the form below.</p>
                </div>
                <span class="required-note">* Required fields</span>
            </div>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="student_name">Full Name *</label>
                    <input type="text" id="student_name" name="student_name"
                           placeholder="Enter your full name" maxlength="100"
                           autocomplete="name" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number *</label>
                    <input type="tel" id="mobile" name="mobile"
                           placeholder="Enter your mobile number" maxlength="15"
                           pattern="[+]?[0-9]{10,14}"
                           title="Enter 10 to 14 digits, optionally starting with +"
                           autocomplete="tel" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                           placeholder="Enter your email address" maxlength="254"
                           autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label for="college_id">Select College *</label>
                    <select id="college_id" name="college_id" required>
                        <option value="">Choose your college</option>
                        <option value="1">College 1</option>
                        <option value="2">College 2</option>
                        <option value="3">College 3</option>
                        <option value="4">College 4</option>
                        <option value="5">College 5</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="academic_year">Current Academic Year *</label>
                    <select id="academic_year" name="academic_year" required>
                        <option value="">Choose your academic year</option>
                        <option value="First Year">First Year</option>
                        <option value="Second Year">Second Year</option>
                        <option value="Third Year">Third Year</option>
                        <option value="Fourth Year">Fourth Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="program_details">Program / Assignment Details</label>
                    <textarea id="program_details" name="program_details" rows="4"
                              maxlength="5000"
                              placeholder="Describe your program or assignment (optional)"></textarea>
                </div>

                <button type="submit" class="submit-button">
                    Submit Registration
                    <span aria-hidden="true">&rarr;</span>
                </button>

                <p class="privacy-note">
                    Your mobile and email verification statuses will remain
                    pending until the respective verification processes
                    are completed.
                </p>
            </form>
        </section>

        <footer class="page-footer">
            <p>&copy; 2026 Student Connect. All rights reserved.</p>
        </footer>
    </main>
</body>
</html>
