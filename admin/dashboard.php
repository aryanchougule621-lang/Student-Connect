
<?php
require_once __DIR__ . '/../config/db.php';

// Read and validate filters.
$collegeId = filter_input(INPUT_GET, 'college_id', FILTER_VALIDATE_INT);
$academicYear = trim($_GET['academic_year'] ?? '');

$allowedYears = [
    'First Year',
    'Second Year',
    'Third Year',
    'Fourth Year'
];

// Validate college filter.
if (
    $collegeId !== null &&
    $collegeId !== false &&
    ($collegeId < 1 || $collegeId > 5)
) {
    $collegeId = null;
}

// Validate academic year filter.
if (!in_array($academicYear, $allowedYears, true)) {
    $academicYear = '';
}

// Build filter conditions and parameters once.
$conditions = [];
$params = [];

if ($collegeId !== null && $collegeId !== false) {
    $conditions[] = 'college_id = :college_id';
    $params[':college_id'] = $collegeId;
}

if ($academicYear !== '') {
    $conditions[] = 'academic_year = :academic_year';
    $params[':academic_year'] = $academicYear;
}

$whereSql = '';

if (!empty($conditions)) {
    $whereSql = ' WHERE ' . implode(' AND ', $conditions);
}

// Get the total number of registrations matching filters.
$countStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM students' . $whereSql
);
$countStmt->execute($params);

$totalStudents = (int) $countStmt->fetchColumn();

// Get student records and college names.
$sql = "SELECT
            s.student_id,
            s.student_name,
            s.mobile,
            s.email,
            c.college_name,
            s.academic_year,
            s.mobile_verified,
            s.email_verified,
            s.registered_at
        FROM students s
        INNER JOIN colleges c
            ON c.college_id = s.college_id";

$studentConditions = [];
$studentParams = [];

if ($collegeId !== null && $collegeId !== false) {
    $studentConditions[] = 's.college_id = :college_id';
    $studentParams[':college_id'] = $collegeId;
}

if ($academicYear !== '') {
    $studentConditions[] = 's.academic_year = :academic_year';
    $studentParams[':academic_year'] = $academicYear;
}

if (!empty($studentConditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $studentConditions);
}

$sql .= ' ORDER BY s.registered_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($studentParams);

$students = $stmt->fetchAll();

// Get college options for the filter.
$colleges = $pdo->query(
    "SELECT college_id, college_name
     FROM colleges
     ORDER BY college_id"
)->fetchAll();

// Safely escape output before displaying it in HTML.
function escapeHtml($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function verificationLabel($value) {
    return (int) $value === 1 ? 'Verified' : 'Pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Connect | Admin Dashboard</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .dashboard {
            max-width: 1400px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e5eaf2;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .stat-card h2 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #69758b;
        }

        .stat-number {
            font-size: 34px;
            font-weight: 700;
            color: #2458d3;
        }

        .filters {
            display: flex;
            gap: 14px;
            align-items: end;
            flex-wrap: wrap;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .filters .form-group {
            margin: 0;
            flex: 1;
            min-width: 180px;
        }

        .filters button,
        .reset-link {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            font: inherit;
        }

        .reset-link {
            border: 1px solid #d8dfeb;
            color: #172033;
        }

        .table-wrap {
            overflow-x: auto;
            background: #fff;
            border: 1px solid #e5eaf2;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
            font-size: 13px;
        }

        th,
        td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f5;
        }

        th {
            background: #f8faff;
            color: #46536b;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .verified {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #69758b;
        }

        @media (max-width: 600px) {
            .dashboard {
                padding: 0 12px;
            }
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

        <section class="stat-card">
            <h2>Total Registrations Matching Filters</h2>

            <div class="stat-number">
                <?= $totalStudents ?>
            </div>
        </section>

        <form class="filters" method="GET">
            <div class="form-group">
                <label for="college_id">Filter by College</label>

                <select name="college_id" id="college_id">
                    <option value="">All Colleges</option>

                    <?php foreach ($colleges as $college): ?>
                        <option
                            value="<?= (int) $college['college_id'] ?>"
                            <?= (string) $collegeId === (string) $college['college_id'] ? 'selected' : '' ?>
                        >
                            <?= escapeHtml($college['college_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="academic_year">
                    Filter by Academic Year
                </label>

                <select name="academic_year" id="academic_year">
                    <option value="">All Academic Years</option>

                    <?php foreach ($allowedYears as $year): ?>
                        <option
                            value="<?= escapeHtml($year) ?>"
                            <?= $academicYear === $year ? 'selected' : '' ?>
                        >
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
                <div class="empty">
                    No student registrations match these filters.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
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
                                <td>
                                    <?= (int) $student['student_id'] ?>
                                </td>

                                <td>
                                    <?= escapeHtml($student['student_name']) ?>
                                </td>

                                <td>
                                    <?= escapeHtml($student['mobile']) ?>
                                </td>

                                <td>
                                    <?= escapeHtml($student['email']) ?>
                                </td>

                                <td>
                                    <?= escapeHtml($student['college_name']) ?>
                                </td>

                                <td>
                                    <?= escapeHtml($student['academic_year']) ?>
                                </td>

                                <td>
                                    <span class="status <?= (int) $student['mobile_verified'] === 1 ? 'verified' : 'pending' ?>">
                                        <?= verificationLabel($student['mobile_verified']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status <?= (int) $student['email_verified'] === 1 ? 'verified' : 'pending' ?>">
                                        <?= verificationLabel($student['email_verified']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= escapeHtml($student['registered_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
