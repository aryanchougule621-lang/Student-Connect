
<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['student_name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$collegeId = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
$academicYear = trim($_POST['academic_year'] ?? '');
$programDetails = trim($_POST['program_details'] ?? '');

$allowedYears = [
    'First Year',
    'Second Year',
    'Third Year',
    'Fourth Year'
];

$errors = [];

if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Enter a valid student name.';
}

if (!preg_match('/^\+?[0-9]{10,14}$/', $mobile)) {
    $errors[] = 'Enter a valid mobile number (10–14 digits, optionally starting with +).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = 'Enter a valid email address.';
}

if (
    $collegeId === false ||
    $collegeId === null ||
    $collegeId < 1 ||
    $collegeId > 5
) {
    $errors[] = 'Select a valid college.';
}

if (!in_array($academicYear, $allowedYears, true)) {
    $errors[] = 'Select a valid academic year.';
}

if (mb_strlen($programDetails) > 5000) {
    $errors[] = 'Program details must be under 5000 characters.';
}

function renderPage(string $title, string $message, string $type = 'success', array $errors = []): void
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $class = $type === 'success' ? 'success' : 'error';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $safeTitle ?> | Student Connect</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            .result-wrapper {
                min-height: 80vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 30px 16px;
            }
            .result-card {
                width: 100%;
                max-width: 560px;
                padding: 36px;
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.09);
                text-align: center;
            }
            .result-icon {
                font-size: 48px;
                margin-bottom: 12px;
            }
            .result-card h1 {
                margin-bottom: 12px;
            }
            .result-card p {
                line-height: 1.6;
            }
            .result-card ul {
                text-align: left;
                margin: 16px 0;
            }
            .result-link {
                display: inline-block;
                margin-top: 18px;
                padding: 12px 22px;
                border-radius: 8px;
                background: #2457d6;
                color: #fff;
                text-decoration: none;
                font-weight: 600;
            }
            .result-link:hover {
                background: #1945b3;
            }
            .result-card.error h1 {
                color: #b42318;
            }
            @media (max-width: 480px) {
                .result-card {
                    padding: 24px 18px;
                }
            }
        </style>
    </head>
    <body>
        <main class="result-wrapper">
            <section class="result-card <?= $class ?>">
                <div class="result-icon" aria-hidden="true">
                    <?= $type === 'success' ? '✓' : '!' ?>
                </div>
                <h1><?= $safeTitle ?></h1>
                <p><?= $safeMessage ?></p>

                <?php if ($errors): ?>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <a class="result-link" href="index.php">Return to registration</a>
            </section>
        </main>
    </body>
    </html>
    <?php
}

if ($errors) {
    http_response_code(400);
    renderPage(
        'Please correct these errors',
        'Your registration was not submitted. Review the following details:',
        'error',
        $errors
    );
    exit;
}

try {
    $sql = "INSERT INTO students
        (student_name, mobile, email, college_id, academic_year, program_details)
        VALUES
        (:name, :mobile, :email, :college_id, :academic_year, :program_details)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':mobile' => $mobile,
        ':email' => $email,
        ':college_id' => $collegeId,
        ':academic_year' => $academicYear,
        ':program_details' => $programDetails !== '' ? $programDetails : null
    ]);

    renderPage(
        'Registration Successful!',
        'Thank you for registering with Student Connect. Your registration has been received.'
    );

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        renderPage(
            'Registration Could Not Be Completed',
            'This mobile number or email address may already be registered.',
            'error'
        );
    } else {
        error_log('Student registration failed: ' . $e->getMessage());
        http_response_code(500);
        renderPage(
            'Something Went Wrong',
            'Your registration could not be completed. Please try again later.',
            'error'
        );
    }
}
