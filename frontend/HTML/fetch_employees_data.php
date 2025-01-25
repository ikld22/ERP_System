<?php
require 'conn.php';

$query = "SELECT * FROM employees";
$stmt = $pdo->query($query);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$employees_data = [];
foreach ($employees as $employee) {
    $employee_data = [
        'employee_id' => $employee['user_id'],
        'name' => $employee['education'],
        'experience' => $employee['experience'],
        'skills' => $employee['skills'],
        
    ];
    $employees_data[] = $employee_data;
}

$json_data = json_encode($employees_data);
file_put_contents('employees_data.json', $json_data);
?>

