<?php
require __DIR__ . '/../includes/auth.php';
require_login('student');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$studentId = (int) ($_SESSION['user_id'] ?? 0);

$grades = $conn->prepare(
    "SELECT c.course_code, c.course_name, a.assessment_name, a.total_marks, a.weight_percent,
            g.marks, g.grade
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     JOIN assessments a ON a.course_id = c.id
     LEFT JOIN grades g ON g.assessment_id = a.id AND g.student_id = e.student_id
     WHERE e.student_id = ?
     ORDER BY c.course_code, a.assessment_name"
);
$grades->bind_param('i', $studentId);
$grades->execute();
$gradeRows = $grades->get_result();

$summary = $conn->prepare(
    "SELECT c.course_code, COUNT(g.id) AS completed,
            COUNT(a.id) AS total_assessments,
            ROUND(AVG((g.marks / a.total_marks) * 100)) AS average_percent
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     JOIN assessments a ON a.course_id = c.id
     LEFT JOIN grades g ON g.assessment_id = a.id AND g.student_id = e.student_id
     WHERE e.student_id = ?
     GROUP BY c.id, c.course_code
     ORDER BY c.course_code"
);
$summary->bind_param('i', $studentId);
$summary->execute();
$summaryRows = $summary->get_result();

$pageTitle = 'My Grades | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Student';
$activeRole = 'student_grades';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="hero">
                    <div class="hero-card">
                        <p class="eyebrow">My grades</p>
                        <h1>Assessment results.</h1>
                        <p>Review each enrolled unit, its assignments, marks, grade letters, and current average.</p>
                    </div>
                </section>

                <section class="stats-grid">
                    <?php if ($summaryRows && $summaryRows->num_rows): ?>
                        <?php while ($row = $summaryRows->fetch_assoc()): ?>
                            <div class="stat-card">
                                <span><?php echo esc($row['course_code']); ?> average</span>
                                <strong><?php echo esc($row['average_percent'] ?? 0); ?>%</strong>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </section>

                <section class="panel">
                    <div class="panel-title"><h2>Assessment grades</h2><span class="badge">Enrolled units</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Unit</th><th>Assessment</th><th>Weight</th><th>Marks</th><th>Grade</th></tr></thead>
                            <tbody>
                                <?php if ($gradeRows && $gradeRows->num_rows): ?>
                                    <?php while ($grade = $gradeRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($grade['course_code'] . ' - ' . $grade['course_name']); ?></td>
                                            <td><?php echo esc($grade['assessment_name']); ?></td>
                                            <td><?php echo esc($grade['weight_percent'] !== null ? $grade['weight_percent'] . '%' : ''); ?></td>
                                            <td><?php echo $grade['marks'] !== null ? esc($grade['marks'] . '/' . $grade['total_marks']) : 'Pending'; ?></td>
                                            <td><?php echo $grade['grade'] ? '<span class="badge">' . esc($grade['grade']) . '</span>' : '<span class="badge">Pending</span>'; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No grades available yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
