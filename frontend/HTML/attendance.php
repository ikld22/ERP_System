<?php
session_start();
require 'conn.php'; // اتصال قاعدة البيانات

// التحقق من وجود employee_id في الجلسة
if (!isset($_SESSION['employee_id'])) {
    $_SESSION['attendance_message'] = "No employee ID found in session.";
    header("Location: employee_page.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];

// جلب سجل الحضور للموظف
try {
    $attendanceStmt = $pdo->prepare("
        SELECT date, check_in, check_out, status 
        FROM attendance 
        WHERE employee_id = :employee_id 
        ORDER BY date DESC
    ");
    $attendanceStmt->bindParam(':employee_id', $employee_id, PDO::PARAM_STR);
    $attendanceStmt->execute();
    $attendanceHistory = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['attendance_message'] = "Database error: Unable to fetch attendance history.";
    error_log("Database error: " . $e->getMessage());
    header("Location: employee.php");
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        $current_time = date('H:i:s');
        $current_date = date('Y-m-d');

        // تنفيذ Check-In
        if ($action == 'check_in') {
            $check_in_start = strtotime('08:00:00');
            $check_in_end = strtotime('08:15:00');
            $current_time_ts = strtotime($current_time);
            $status = ($current_time_ts >= $check_in_start && $current_time_ts <= $check_in_end) ? 'On Time' : 'Late';

            $stmt = $pdo->prepare("
                INSERT INTO attendance (employee_id, date, check_in, status) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE check_in = VALUES(check_in), status = VALUES(status)
            ");
            $stmt->execute([$employee_id, $current_date, $current_time, $status]);

            $_SESSION['attendance_message'] = "Check-In recorded. Status: $status.";
        }

        // تنفيذ Check-Out
        elseif ($action == 'check_out') {
            $check_out_start = strtotime('15:00:00');
            $current_time_ts = strtotime($current_time);

            if ($current_time_ts >= $check_out_start) {
                $stmt = $pdo->prepare("UPDATE attendance SET check_out = ? WHERE employee_id = ? AND date = ?");
                $stmt->execute([$current_time, $employee_id, $current_date]);

                $_SESSION['attendance_message'] = "Check-Out recorded successfully.";
            } else {
                $_SESSION['attendance_message'] = "Check-Out not allowed before 3:00 PM.";
            }
        }
    }
} catch (PDOException $e) {
    $_SESSION['attendance_message'] = "Database error: " . $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}

// إعادة التوجيه إلى صفحة الموظف
header("Location: employee.php");
exit;
?>
