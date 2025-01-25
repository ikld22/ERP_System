<?php
// بدء الجلسة
session_start();
require 'conn.php';

// التحقق من أن employee_id موجود في الجلسة
if (!isset($_SESSION['employee_id'])) {
    echo "No employee ID found in session.";
    exit;
}

// الحصول على employee_id من الجلسة
$employee_id = $_SESSION['employee_id'];

// إعداد اتصال PDO
$pdo = new PDO('mysql:host=localhost;dbname=erps', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// جلب بيانات الموظف
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = :employee_id");
$stmt->bindParam(':employee_id', $employee_id);
$stmt->execute();
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب المهام
$tasksStmt = $pdo->prepare("SELECT * FROM tasks WHERE employee_id = :employee_id");
$tasksStmt->bindParam(':employee_id', $employee_id);
$tasksStmt->execute();
$tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب التقييمات الشهرية
$reviewsStmt = $pdo->prepare("SELECT * FROM reviews WHERE employee_id = :employee_id ORDER BY review_date DESC");
$reviewsStmt->bindParam(':employee_id', $employee_id);
$reviewsStmt->execute();
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب الترقيات الوظيفية
$promotionsStmt = $pdo->prepare("SELECT * FROM promotions WHERE employee_id = :employee_id ORDER BY promotion_date DESC");
$promotionsStmt->bindParam(':employee_id', $employee_id);
$promotionsStmt->execute();
$promotions = $promotionsStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب تاريخ الحضور والانصراف
try {
    $attendanceStmt = $pdo->prepare("SELECT date, check_in, check_out, status FROM attendance WHERE employee_id = :employee_id ORDER BY date DESC");
    $attendanceStmt->bindParam(':employee_id', $employee_id, PDO::PARAM_STR);
    $attendanceStmt->execute();
    $attendanceHistory = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['attendance_message'] = "Database error: Unable to fetch attendance history.";
    error_log("Database error: " . $e->getMessage());
    header("Location: employee.php");
    exit;
}

// جلب سجل التواصل بناءً على الـ employee_id
try {
    $stmt = $pdo->prepare("SELECT * FROM communication WHERE employee_id = :employee_id ORDER BY id DESC");
    $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
    $stmt->execute();
    $communicationHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message_status'] = "Error: " . $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}

// عرض حالة الرسالة أو الرد
if (isset($_SESSION['hr_message_status'])) {
    echo $_SESSION['hr_message_status'];
    unset($_SESSION['hr_message_status']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Employee page for viewing and managing personal information, tasks, and performance reviews.">
    <title>Employee Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Employee Page</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#daily-tasks">Daily Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="#monthly-tasks">Monthly Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Monthly Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="#promotions">Promotions</a></li>
                    <li class="nav-item"><a class="nav-link" href="#hr-communication">HR Communication</a></li>
                    <li class="nav-item"><a class="nav-link" href="#manager-communication">Manager Communication</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Personal Information -->
        <div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user"></i> Personal Information</span>
        <div>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPersonalInfoModal">Edit</button>
            <a href="logout.php" class="btn btn-danger btn-sm ms-2">Logout</a>
        </div>
    </div>


            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($employee['name']); ?></li>
                    <li class="list-group-item"><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></li>
                    <li class="list-group-item"><strong>Position:</strong> <?php echo htmlspecialchars($employee['job_title']); ?></li>
                    <li class="list-group-item"><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($employee['employee_email']); ?></li>
                    <li class="list-group-item"><strong>phone number:</strong> <?php echo htmlspecialchars($employee['employee_number']); ?></li>
                </ul>
            </div>
        </div>

        <!-- Modal for Editing Personal Information -->
        <div class="modal fade" id="editPersonalInfoModal" tabindex="-1" aria-labelledby="editPersonalInfoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="update_employee.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPersonalInfoLabel">Edit Personal Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="employee_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="employee_email" name="employee_email" value="<?php echo htmlspecialchars($employee['employee_email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="employee_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="employee_number" name="employee_number" value="<?php echo htmlspecialchars($employee['employee_number']); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


        <!-- Attendance -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-calendar-check"></i> Attendance
            </div>
            <div class="card-body">
                <form method="POST" action="attendance.php">
                    <button type="submit" name="action" value="check_in" class="btn btn-primary">Check In</button>
                    <button type="submit" name="action" value="check_out" class="btn btn-danger">Check Out</button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#historyModal">History</button>
                </form>
                <?php if (isset($_SESSION['attendance_message'])): ?>
                    <div class="alert alert-success mt-3">
                        <?php echo htmlspecialchars($_SESSION['attendance_message']); ?>
                    </div>
                    <?php unset($_SESSION['attendance_message']); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal for Attendance History -->
        <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="historyModalLabel">Attendance History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($attendanceHistory)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendanceHistory as $entry): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($entry['date']) ?></td>
                                                <td><?= htmlspecialchars($entry['check_in']) ?></td>
                                                <td><?= htmlspecialchars($entry['check_out']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" role="alert">
                                No attendance history available.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Tasks -->
        <div id="daily-tasks" class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-tasks"></i> Daily Tasks
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <?php if ($task['task_type'] == 'daily'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td><input type="checkbox" <?php echo $task['status'] == 'completed' ? 'checked' : ''; ?> disabled></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Tasks -->
        <div id="monthly-tasks" class="card mb-4">
            <div class="card-header bg-warning text-white">
                <i class="fas fa-calendar-alt"></i> Monthly Tasks
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <?php if ($task['task_type'] == 'monthly'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td><input type="checkbox" <?php echo $task['status'] == 'completed' ? 'checked' : ''; ?> disabled></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Reviews -->
        <div id="reviews" class="card mb-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-line"></i> Monthly Reviews
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Review Date</th>
                            <th>Comments</th>
                            <th>score</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['review_date']); ?></td>
                                <td><?php echo htmlspecialchars($review['comments']); ?></td>
                                <td><?php echo htmlspecialchars($review['score']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Promotions -->
        <div id="promotions" class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <i class="fas fa-arrow-up"></i> Promotions
    </div>
    <div class="card-body">
        <?php if (!empty($promotions)): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($promotions as $promotion): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($promotion['promotion_date']); ?>:</strong> 
                        <?php echo htmlspecialchars($promotion['new_position']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No upcoming promotions for you.</p>
        <?php endif; ?>
    </div>
</div>


        <!-- Form for sending a message -->
        <form method="POST" action="communication.php">
            <div class="mb-3">
                <label for="to" class="form-label">Send To</label>
                <select class="form-select" id="to" name="to" required>
                    <option value="HR">HR</option>
                    <option value="Manager">Manager</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>

        <?php
        // عرض حالة الرسالة أو الرد
        if (isset($_SESSION['message_status'])):
        ?>
            <div class="alert alert-info mt-3">
                <?php echo $_SESSION['message_status']; ?>
            </div>
        <?php
            unset($_SESSION['message_status']);
        endif;
        ?>

        <!-- Communication History -->
        <div id="hr-communication" class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-comments"></i> HR and Manager Communication History
            </div>
            <div class="card-body">
                <?php if (!empty($communicationHistory)): ?>
                    <ul class="list-group">
                        <?php foreach ($communicationHistory as $message): ?>
                            <li class="list-group-item">

                                
                                <strong>from:<?php echo htmlspecialchars($message['to']); ?></strong><br>
                                <strong>respond:<?php echo htmlspecialchars($message['respond']); ?></strong><br>
                                <strong> Message:<?php echo htmlspecialchars($message['Message']); ?></strong><br>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No communication history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
