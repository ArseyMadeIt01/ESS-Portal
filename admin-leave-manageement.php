<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "ess_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process Leave Request (Accept/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $leaveId = $_POST['leave_id'];
    $action = $_POST['action'];
    $reason = $conn->real_escape_string($_POST['reason']);

    if ($action === 'accept') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        die("Invalid action.");
    }

    $sql = "UPDATE leave_requests SET status = '$status', admin_reason = '$reason' WHERE id = $leaveId";

    if ($conn->query($sql) === TRUE) {
        echo "Leave request successfully $status.";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch Leave Requests
$leaveRequests = $conn->query("SELECT lr.id, lr.employee_name, lr.leave_type, lr.start_date, lr.end_date, lr.status, hr.name AS hr_name 
FROM leave_requests lr
LEFT JOIN hr_managers hr ON lr.hr_id = hr.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Admin Leave Management</h1>

    <h2>Leave Requests</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>HR Manager</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $leaveRequests->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['employee_name'] ?></td>
                    <td><?= $row['leave_type'] ?></td>
                    <td><?= $row['start_date'] ?></td>
                    <td><?= $row['end_date'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['hr_name'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                <textarea name="reason" placeholder="Reason" required></textarea><br>
                                <button type="submit" name="action" value="accept">Accept</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        <?php else: ?>
                            <?= $row['status'] ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Employees on Leave</h2>
    <table>
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $employeesOnLeave = $conn->query("SELECT employee_name, leave_type, start_date, end_date, status FROM leave_requests WHERE status = 'Approved'");
            while ($row = $employeesOnLeave->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['employee_name'] ?></td>
                    <td><?= $row['leave_type'] ?></td>
                    <td><?= $row['start_date'] ?></td>
                    <td><?= $row['end_date'] ?></td>
                    <td><?= $row['status'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
