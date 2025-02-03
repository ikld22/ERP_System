
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Personal Information</div>
            <div class="card-body">
                <ul class="list-group">
                <?php
              include("C:/wamp64/www/ERP_System/backend/data.php");

               ?>
                    <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($employee['name']) ?></li>
                    <li class="list-group-item"><strong>Employee ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></li>
                    <li class="list-group-item"><strong>Position:</strong> <?= htmlspecialchars($employee['job_title']) ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($employee['employee_email']) ?></li>
                </ul>
            </div>
        </div>

        <!-- New Job Applications -->
        <div class="card mb-4">
    <div class="card-header bg-primary text-white">New Job Applications</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>job_title</th>                 
                    <th>Date Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                 
               <?php
              include("C:/wamp64/www/ERP_System/backend/data.php");
              ?>
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['name']) ?></td>
                            <td><?= htmlspecialchars($app['job_title']) ?></td> <!-- Corrected Position Field -->
                            <td><?= htmlspecialchars($app['applied_date']) ?></td>
                            <td>
<a href="view_application.php?application_id=<?= $app['application_id'] ?>&job_id=<?= $app['job_id'] ?>&user_id=<?= $app['user_id'] ?>" class="btn btn-info btn-sm">View</a>
<a href="approve_application.php?application_id=<?= $app['application_id'] ?>&job_id=<?= $app['job_id'] ?>&user_id=<?= $app['user_id'] ?>" class="btn btn-success btn-sm">Approve</a>
<a href="reject_application.php?application_id=<?= $app['application_id'] ?>&user_id=<?= $app['user_id'] ?>" class="btn btn-danger btn-sm">Reject</a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No new applications</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


        <!-- Add Job -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Add Job</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="skills_required" class="form-label">Skills Required</label>
                        <input type="text" class="form-control" id="skills_required" name="skills_required" required>
                    </div>
                    <div class="mb-3">
                        <label for="experience_required" class="form-label">Experience Required</label>
                        <input type="text" class="form-control" id="experience_required" name="experience_required" required>
                    </div>
                    <div class="mb-3">
                        <label for="education_required" class="form-label">Education Required</label>
                        <input type="text" class="form-control" id="education_required" name="education_required" required>
                    </div>
                    <div class="mb-3">
                        <label for="job_description" class="form-label">Job Description</label>
                        <textarea class="form-control" id="job_description" name="job_description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="created_at" class="form-label">Job Date</label>
                        <input type="date" class="form-control" id="created_at" name="created_at" required>
                    </div>
                    <button type="submit" name="add_job" class="btn btn-success">Add Job</button>
                </form>
            </div>
        </div>

        <!-- Jobs Table -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Jobs</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Job Title</th>
                            <th>Skills Required</th>
                            <th>Experience Required</th>
                            <th>Education Required</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
              include("C:/wamp64/www/ERP_System/backend/data.php");
              ?>
                        <?php if (!empty($jobs)): ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?= htmlspecialchars($job['job_id']) ?></td>
                                    <td><?= htmlspecialchars($job['job_title']) ?></td>
                                    <td><?= htmlspecialchars($job['skills_required']) ?></td>
                                    <td><?= htmlspecialchars($job['experience_required']) ?></td>
                                    <td><?= htmlspecialchars($job['education_required']) ?></td>
                                    <td><?= htmlspecialchars($job['job_description']) ?></td>
                                    <td><?= htmlspecialchars($job['created_at']) ?></td>
                                    <td>
                                        <a href="?delete_id=<?= $job['job_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No jobs available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
