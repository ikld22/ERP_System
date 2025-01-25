<?php
session_start();
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_SESSION['employee_id'];
    $name = $_POST['name'];
    $employee_email = $_POST['employee_email'];
    $employee_number = $_POST['employee_number'];

    // Update employee information in the database
    $stmt = $pdo->prepare("UPDATE employees SET name = :name, employee_email = :employee_email, employee_number = :employee_number WHERE employee_id = :employee_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':employee_email', $employee_email);
    $stmt->bindParam(':employee_number', $employee_number);
    $stmt->bindParam(':employee_id', $employee_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Information updated successfully!";

        // Check the job title of the employee
        $job_stmt = $pdo->prepare("SELECT job_title FROM employees WHERE employee_id = :employee_id");
        $job_stmt->bindParam(':employee_id', $employee_id);
        $job_stmt->execute();
        $job = $job_stmt->fetch(PDO::FETCH_ASSOC);

        if ($job && $job['job_title'] === 'HR') {
            // Redirect HR employees to the HR page
            header('Location: HR.php');
            exit;
        }
        if ($job && $job['job_title'] === 'Employee') {
            // Redirect HR employees to the HR page
            header('Location: employee.php');
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Error updating information.";
    }

    // Redirect to employee page if not HR
    header('Location: employee.php');
    exit;
}
?>


