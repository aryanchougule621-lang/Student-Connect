<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function verificationLabel($value): string
{
    return (int) $value === 1 ? 'Verified' : 'Pending';
}

$allowedYears = ['First Year', 'Second Year', 'Third Year', 'Fourth Year'];
$collegeId = filter_input(INPUT_GET, 'college_id', FILTER_VALIDATE_INT);
$collegeId = ($collegeId !== false && $collegeId !== null && $collegeId > 0) ? $collegeId : null;
$academicYear = isset($_GET['academic_year']) && in_array($_GET['academic_year'], $allowedYears, true)
    ? $_GET['academic_year']
    : '';

$colleges = [];
$students = [];
$totalStudents = 0;
$errorMessage = '';

try {
    $collegeStmt = $pdo->query('SELECT college_id, college_name FROM colleges ORDER BY college_name');
    $colleges = $collegeStmt->fetchAll(PDO::FETCH_ASSOC);

    $where = [];
    $params = [];

    if ($collegeId !== null) {
        $where[] = 's.college_id = :college_id';
        $params[':college_id'] = $collegeId;
    }

    if ($academicYear !== '') {
        $where[] = 's.academic_year = :academic_year';
        $params[':academic_year'] = $academicYear;
    }

    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM students s' . $whereSql);
    $countStmt->execute($params);
    $totalStudents = (int) $countStmt->fetchColumn();

    $sql = '
        SELECT
            s.student_id,
            s.student_name,
            c.college_name,
            s.academic_year,
            s.mobile_verified,
            s.email_verified,
            s.registered_at
        FROM students s
        INNER JOIN colleges c ON c.college_id = s.college_id
    ' . $whereSql . '
        ORDER BY s.registered_at DESC, s.student_id DESC';

    $studentStmt = $pdo->prepare($sql);
    $studentStmt->execute($params);
    $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Student Connect dashboard error: ' . $e->getMessage());
    $errorMessage = 'The dashboard could not load the registrations. Check the database connection and table setup.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Student Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { margin: 0; background: #f4f7fb; color: #1f2937; font-family: Arial, sans-serif; }
        .site-header { background: #173b67; color: #fff; padding: 18px 24px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 10px; background: #fff; color: #173b67; font-weight: 700; }
        .brand-text h1 { margin: 0; font-size: 24px; }
        .brand-text p { margin: 5px 0 0; color: #e5edf8; }
        .dashboard { max-width: 1200px; margin: 28px auto; padding: 0 20px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .dashboard-header h2 { margin: 0 0 8px; }
        .dashboard-header p { margin: 0; color: #64748b; }
        a { color: #1d5da8; }
        .stat-card, .filters, .table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 3px 12px rgba(15, 23, 42, .04); }
        .stat-card { padding: 20px 24px; margin-bottom: 20px; }
        .stat-card h2 { margin: 0 0 10px; font-size: 16px; color: #475569; }
        .stat-number { font-size: 32px; font-weight: 700; color: #173b67; }
        .filters { display: flex; align-items: end; gap: 16px; flex-wrap: wrap; padding: 18px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 7px; min-width: 190px; }
        .form-group label { font-size: 14px; font-weight: 600; }
        .form-group select, .filters button { min-height: 40px; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 10px; font-size: 14px; }
        .filters button { background: #173b67; color: #fff; border: 0; padding: 9px 16px; cursor: pointer; }
        .reset-link { padding-bottom: 10px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; min-width: 850px; border-collapse: collapse; }
        th, td { text-align: left; padding: 13px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f8fafc; color: #475569; font-weight: 700; }
        tbody tr:last-child td { border-bottom: 0; }
        .status { display: inline-block; border-radius: 999px; padding: 5px 9px; font-size: 12px; font-weight: 700; }
        .status.verified { background: #dcfce7; color: #166534; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .empty, .error { padding: 22px; color: #64748b; }
        .error { margin-bottom: 18px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; color: #9f1239; }
        @media (max-width: 600px) {
            .dashboard { padding: 0 12px; margin-top: 20px; }
            .site-header { padding: 16px; }
            .brand-text h1 { font-size: 21px; }
            .filters, .form-group { width: auto; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <div class="brand-icon">SC</div>
            <div class="brand-text">
                <h1>Student Connect</h1>
                <p>Admin Dashboard</p>
            </div>
        </div>
    </header>

    <main class="dashboard">
        <div class="dashboard-header">
            <div>
                <h2>Registration Overview</h2>
                <p>View and filter student registrations.</p>
            </div>
            <a href="../index.php">Open Registration Form</a>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="error"><?= escapeHtml($errorMessage) ?></div>
        <?php else: ?>
            <section class="stat-card">
                <h2>Total Registrations Matching Filters</h2>
                <div class="stat-number"><?= $totalStudents ?></div>
            </section>

            <form class="filters" method="GET" action="dashboard.php">
                <div class="form-group">
                    <label for="college_id">Filter by College</label>
                    <select name="college_id" id="college_id">
                        <option value="">All Colleges</option>
                        <?php foreach ($colleges as $college): ?>
                            <option value="<?= (int) $college['college_id'] ?>"
                                <?= $collegeId === (int) $college['college_id'] ? 'selected' : '' ?>>
                                <?= escapeHtml($college['college_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="academic_year">Filter by Academic Year</label>
                    <select name="academic_year" id="academic_year">
                        <option value="">All Academic Years</option>
                        <?php foreach ($allowedYears as $year): ?>
                            <option value="<?= escapeHtml($year) ?>"
                                <?= $academicYear === $year ? 'selected' : '' ?>>
                                <?= escapeHtml($year) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Apply Filters</button>
                <a class="reset-link" href="dashboard.php">Reset</a>
            </form>

            <div class="table-wrap">
                <?php if (empty($students)): ?>
                    <div class="empty">No student registrations match these filters.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>College</th>
                                <th>Academic Year</th>
                                <th>Mobile Status</th>
                                <th>Email Status</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= (int) $student['student_id'] ?></td>
                                    <td><?= escapeHtml($student['student_name']) ?></td>
                                    <td><?= escapeHtml($student['college_name']) ?></td>
                                    <td><?= escapeHtml($student['academic_year']) ?></td>
                                    <td><span class="status <?= (int) $student['mobile_verified'] === 1 ? 'verified' : 'pending' ?>"><?= verificationLabel($student['mobile_verified']) ?></span></td>
                                    <td><span class="status <?= (int) $student['email_verified'] === 1 ? 'verified' : 'pending' ?>"><?= verificationLabel($student['email_verified']) ?></span></td>
                                    <td><?= escapeHtml($student['registered_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
