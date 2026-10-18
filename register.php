
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
    $errors[] = 'Enter a valid mobile number.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = 'Enter a valid email address.';
}

if ($collegeId === false || $collegeId === null || $collegeId < 1 || $collegeId > 5) {
    $errors[] = 'Select a valid college.';
}

if (!in_array($academicYear, $allowedYears, true)) {
    $errors[] = 'Select a valid academic year.';
}

if (mb_strlen($programDetails) > 5000) {
    $errors[] = 'Program details must be under 5000 characters.';
}

if ($errors) {
    http_response_code(400);
    echo '<h2>Please correct these errors</h2><ul>';

    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    }

    echo '</ul><p><a href="index.php">Return to registration</a></p>';
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

    echo '<h1>Registration successful!</h1>';
    echo '<p>Thank you for registering with Student Connect.</p>';
    echo '<p><a href="index.php">Return to registration</a></p>';

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo '<h2>Registration could not be completed</h2>';
        echo '<p>This mobile number or email address may already be registered.</p>';
        echo '<p><a href="index.php">Return to registration</a></p>';
    } else {
        error_log('Student registration failed: ' . $e->getMessage());
        http_response_code(500);
        echo '<h2>Something went wrong</h2>';
        echo '<p>Please try again later.</p>';
    }
}
