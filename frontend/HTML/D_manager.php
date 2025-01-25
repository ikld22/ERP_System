
<?php
// بدء الجلسة (إذا لزم الأمر)
session_start();
require 'conn.php';

if (!isset($_SESSION['employee_id'])) {
    echo "No employee ID found in session.";
    exit;
}

// الحصول على employee_id من الجلسة
$employee_id = $_SESSION['employee_id'];



// استعلام لجلب بيانات الموظف باستخدام employee_id
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = :employee_id");
$stmt->bindParam(':employee_id', $employee_id);
$stmt->execute();

// جلب البيانات
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 16px;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .section h3 {
            margin-bottom: 15px;
            color: #007BFF;
        }
        .section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .section table, .section th, .section td {
            border: 1px solid #ccc;
        }
        .section th, .section td {
            padding: 10px;
            text-align: left;
        }
        .section th {
            background-color: #f9f9f9;
        }
        .section button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .section button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Manager Page</h1>
        <div class="nav-links">
            <a href="#daily-tasks">Daily Tasks</a>
            <a href="#monthly-tasks">Monthly Tasks</a>
            <a href="#employee-performance">Employee Performance</a>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
    <div class="section">
            <h3>Personal Information</h3>
            <ul>
                <li><strong>Name:</strong> <?php echo htmlspecialchars($employee['name']); ?></li>
                <li><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></li>
                <li><strong>Position:</strong> <?php echo htmlspecialchars($employee['job_title']); ?></li>
                <li><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($employee['employee_email']); ?></li>
                <li><strong>Extension:</strong> <?php echo htmlspecialchars($employee['employee_number']); ?></li>
            </ul>
        </div>

        <!-- Manager's Daily Tasks -->
        <div id="daily-tasks" class="section">
            <h3>Daily Tasks</h3>
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Review reports</td>
                        <td><input type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>Team meeting</td>
                        <td><input type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>Approve requests</td>
                        <td><input type="checkbox"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Manager's Monthly Tasks -->
        <div id="monthly-tasks" class="section">
            <h3>Monthly Tasks</h3>
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Submit department budget</td>
                        <td>15/01/2025</td>
                        <td><input type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>Performance review</td>
                        <td>20/01/2025</td>
                        <td><input type="checkbox"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Assign Tasks to Employees -->
        <div id="assign-tasks" class="section">
            <h3>Assign Tasks to Employees</h3>
            <form>
                <label for="employee-name">Employee Name:</label>
                <input type="text" id="employee-name" style="width: 100%; margin-bottom: 10px;">
                <label for="task-type">Task Type:</label>
                <select id="task-type" style="width: 100%; margin-bottom: 10px;">
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                </select>
                <label for="task-details">Task Details:</label>
                <textarea id="task-details" style="width: 100%; height: 100px; margin-bottom: 10px;"></textarea>
                <button type="submit">Assign Task</button>
            </form>
        </div>

        <!-- Employee Performance -->
        <div id="employee-performance" class="section">
            <h3>Employee Performance</h3>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Task</th>
                        <th>Completion Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jane Doe</td>
                        <td>Update database</td>
                        <td>02/01/2025</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>Mark Lee</td>
                        <td>Prepare presentation</td>
                        <td>03/01/2025</td>
                        <td>In Progress</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
