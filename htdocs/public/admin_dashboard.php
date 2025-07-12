<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();

// Fetch stats
$totalUsers = $conn->query('SELECT COUNT(*) FROM student')->fetchColumn();
$pendingPhotos = $conn->query("SELECT COUNT(*) FROM student WHERE ProfilePhoto IS NOT NULL AND (PhotoConfirmed IS NULL OR PhotoConfirmed = 0)")->fetchColumn();
$pendingLost = $conn->query("SELECT COUNT(*) FROM ReportItem WHERE (StatusConfirmed IS NULL OR StatusConfirmed = 0)")->fetchColumn();
$pendingFound = $conn->query("SELECT COUNT(*) FROM Item WHERE (StatusConfirmed IS NULL OR StatusConfirmed = 0)")->fetchColumn();

// Fetch pending profile photos
$photoRows = $conn->query("SELECT StudentID, StudentName, Email, ProfilePhoto FROM student WHERE ProfilePhoto IS NOT NULL AND (PhotoConfirmed IS NULL OR PhotoConfirmed = 0)")->fetchAll(PDO::FETCH_ASSOC);
// Fetch pending lost reports
$lostRows = $conn->query("SELECT ReportID, ItemName, Description, PhotoURL, StudentNo FROM ReportItem WHERE (StatusConfirmed IS NULL OR StatusConfirmed = 0)")->fetchAll(PDO::FETCH_ASSOC);
// Fetch pending found reports
$foundRows = $conn->query("SELECT ItemID, ItemName, Description, PhotoURL FROM Item WHERE (StatusConfirmed IS NULL OR StatusConfirmed = 0)")->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed (approved/rejected) profile photos
$completedPhotoRows = $conn->query("SELECT StudentID, StudentName, Email, ProfilePhoto, PhotoConfirmed, UpdatedAt FROM student WHERE ProfilePhoto IS NOT NULL AND (PhotoConfirmed = 1 OR PhotoConfirmed = -1)")->fetchAll(PDO::FETCH_ASSOC);
// Fetch completed lost reports
$completedLostRows = $conn->query("SELECT ReportID, ItemName, Description, PhotoURL, StudentNo, StatusConfirmed, UpdatedAt FROM ReportItem WHERE (StatusConfirmed = 1 OR StatusConfirmed = -1)")->fetchAll(PDO::FETCH_ASSOC);
// Fetch completed found reports
$completedFoundRows = $conn->query("SELECT ItemID, ItemName, Description, PhotoURL, StatusConfirmed, UpdatedAt FROM Item WHERE (StatusConfirmed = 1 OR StatusConfirmed = -1)")->fetchAll(PDO::FETCH_ASSOC);
// Fetch all admins for management tab
$adminRows = $conn->query("SELECT AdminID, AdminName, Username, Email FROM Admin ORDER BY AdminName ASC")->fetchAll(PDO::FETCH_ASSOC);

// Section logic
$section = $_GET['section'] ?? 'pending';
function sidebar_active($sec, $current) { return $sec === $current ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - UB Lost & Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/ub.css" rel="stylesheet">
  <link href="../assets/profile.css" rel="stylesheet">
  <style>
    body { background: #faf9f6; }
    .admin-topbar { background: #800000; color: #FFD700; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
    .admin-sidebar { background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 16px rgba(128,0,0,0.08); padding: 2rem 1rem; min-height: 80vh; }
    .admin-sidebar a { color: #800000; font-weight: 600; display: block; margin-bottom: 1.2rem; text-decoration: none; transition: background 0.2s, color 0.2s; }
    .admin-sidebar a.active, .admin-sidebar a:hover { color: #FFD700; background: #800000; border-radius: 0.5rem; padding: 0.3rem 0.7rem; }
    .admin-card { border-radius: 1.25rem; box-shadow: 0 4px 16px rgba(128,0,0,0.08); border: none; }
    .admin-header { background: linear-gradient(120deg, #800000 0%, #FFD700 100%); color: #fff; border-radius: 0 0 1.5rem 1.5rem; padding: 2rem 0 1rem 0; margin-bottom: 2rem; }
    .admin-stat { background: #fff; color: #800000; border-left: 8px solid #FFD700; }
    .admin-table th { background: #FFD700; color: #800000; }
    .admin-table td, .admin-table th { vertical-align: middle; }
    .btn-admin-approve { background: #FFD700; color: #800000; border: none; }
    .btn-admin-approve:hover { background: #800000; color: #FFD700; }
    .btn-admin-reject { background: #fff; color: #800000; border: 2px solid #FFD700; }
    .btn-admin-reject:hover { background: #FFD700; color: #800000; }
    .badge-status { font-size: 0.95em; }
    .admin-management-table th, .admin-management-table td { vertical-align: middle; }
    .admin-management-table th { background: #FFD700; color: #800000; }
    .admin-management-table td { background: #fff; }
    .btn-admin-remove { background: #fff; color: #800000; border: 2px solid #FFD700; }
    .btn-admin-remove:hover { background: #FFD700; color: #800000; }
    .settings-card { max-width: 400px; margin: 0 auto; }
  </style>
</head>
<body>
<div class="admin-topbar">
  <div class="d-flex align-items-center gap-2">
    <span class="ub-logo-3d me-2">UB</span>
    <span class="fw-bold fs-4">UB Lost & Found Admin</span>
  </div>
  <div>
    <span class="me-3">👤 <?php echo htmlspecialchars($_SESSION['admin']['AdminName']); ?></span>
    <a href="admin_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</div>
<div class="container-fluid">
  <div class="row g-4">
    <div class="col-md-2">
      <div class="admin-sidebar">
        <a href="admin_dashboard.php?section=pending" class="<?php echo sidebar_active('pending', $section); ?>">Pending</a>
        <a href="admin_dashboard.php?section=completed" class="<?php echo sidebar_active('completed', $section); ?>">Completed</a>
        <a href="admin_dashboard.php?section=adminmgmt" class="<?php echo sidebar_active('adminmgmt', $section); ?>">Admin Management</a>
        <a href="admin_dashboard.php?section=settings" class="<?php echo sidebar_active('settings', $section); ?>">Settings</a>
      </div>
    </div>
    <div class="col-md-10">
      <div class="admin-header text-center mb-4">
        <h1 class="fw-bold">Admin Dashboard</h1>
        <p class="lead">UB Lost & Found</p>
      </div>
      <?php if (!empty($_SESSION['admin_msg'])): ?>
        <div class="alert alert-info text-center mb-4"><?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?></div>
      <?php endif; ?>
      <?php if ($section === 'pending'): ?>
        <!-- Pending Section -->
        <div class="container mb-5">
          <div class="row g-4 mb-4">
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Profile Photos</h5>
                <div class="display-6 fw-bold"><?php echo $pendingPhotos; ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Lost Reports</h5>
                <div class="display-6 fw-bold"><?php echo $pendingLost; ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Found Reports</h5>
                <div class="display-6 fw-bold"><?php echo $pendingFound; ?></div>
              </div>
            </div>
          </div>
          <div class="row g-4 mb-4">
            <div class="col-md-4">
              <div class="admin-card p-3">
                <h5 class="mb-3">Profile Photo Confirmations</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Name</th><th>Email</th><th>Photo</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($photoRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['StudentName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Email']); ?></td>
                      <td><?php if ($row['ProfilePhoto']): ?><img src="../<?php echo htmlspecialchars($row['ProfilePhoto']); ?>" alt="Photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="photo">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo $row['StudentID']; ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="photo">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo $row['StudentID']; ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-8">
              <div class="admin-card p-3 mb-4">
                <h5 class="mb-3">Lost Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Student No</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($lostRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Description']); ?></td>
                      <td><?php if ($row['PhotoURL']): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td><?php echo htmlspecialchars($row['StudentNo']); ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="lost">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo $row['ReportID']; ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="lost">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo $row['ReportID']; ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="admin-card p-3">
                <h5 class="mb-3">Found Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($foundRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Description']); ?></td>
                      <td><?php if ($row['PhotoURL']): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="found">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo $row['ItemID']; ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="found">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo $row['ItemID']; ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php elseif ($section === 'completed'): ?>
        <!-- Completed Section -->
        <div class="container mb-5">
          <div class="row g-4 mb-4">
            <div class="col-12">
              <div class="admin-card p-3 mb-4">
                <h5 class="mb-3">Completed Profile Photos</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Name</th><th>Email</th><th>Photo</th><th>Status</th><th>Date</th></tr></thead>
                  <tbody>
                  <?php foreach ($completedPhotoRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['StudentName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Email']); ?></td>
                      <td><?php if ($row['ProfilePhoto']): ?><img src="../<?php echo htmlspecialchars($row['ProfilePhoto']); ?>" alt="Photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <?php if ($row['PhotoConfirmed'] == 1): ?>
                          <span class="badge bg-success badge-status">Approved</span>
                        <?php else: ?>
                          <span class="badge bg-danger badge-status">Rejected</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-12">
              <div class="admin-card p-3 mb-4">
                <h5 class="mb-3">Completed Lost Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Student No</th><th>Status</th><th>Date</th></tr></thead>
                  <tbody>
                  <?php foreach ($completedLostRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Description']); ?></td>
                      <td><?php if ($row['PhotoURL']): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td><?php echo htmlspecialchars($row['StudentNo']); ?></td>
                      <td>
                        <?php if ($row['StatusConfirmed'] == 1): ?>
                          <span class="badge bg-success badge-status">Approved</span>
                        <?php else: ?>
                          <span class="badge bg-danger badge-status">Rejected</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-12">
              <div class="admin-card p-3">
                <h5 class="mb-3">Completed Found Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Status</th><th>Date</th></tr></thead>
                  <tbody>
                  <?php foreach ($completedFoundRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Description']); ?></td>
                      <td><?php if ($row['PhotoURL']): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <?php if ($row['StatusConfirmed'] == 1): ?>
                          <span class="badge bg-success badge-status">Approved</span>
                        <?php else: ?>
                          <span class="badge bg-danger badge-status">Rejected</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php elseif ($section === 'adminmgmt'): ?>
        <!-- Admin Management Section -->
        <div class="container mb-5">
          <div class="row g-4 mb-4">
            <div class="col-md-8">
              <div class="admin-card p-3">
                <h5 class="mb-3">All Admins</h5>
                <table class="table admin-management-table table-sm">
                  <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($adminRows as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['AdminName']); ?></td>
                      <td><?php echo htmlspecialchars($row['Username']); ?></td>
                      <td><?php echo htmlspecialchars($row['Email']); ?></td>
                      <td>
                        <?php if ($_SESSION['admin']['AdminID'] != $row['AdminID']): ?>
                          <form method="POST" action="remove_admin.php" style="display:inline" onsubmit="return confirm('Remove this admin?');">
                            <input type="hidden" name="admin_id" value="<?php echo $row['AdminID']; ?>">
                            <button class="btn btn-admin-remove btn-sm" type="submit">Remove</button>
                          </form>
                        <?php else: ?>
                          <span class="badge bg-secondary">You</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-3">
                <h5 class="mb-3">Add New Admin</h5>
                <form method="POST" action="add_admin.php">
                  <div class="mb-2">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="admin_name" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="admin_username" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="admin_email" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="admin_password" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Add Admin</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php elseif ($section === 'settings'): ?>
        <!-- Settings Section -->
        <div class="container mb-5">
          <div class="settings-card admin-card p-4 mt-4">
            <h5 class="mb-3">Change Password</h5>
            <?php if (!empty($_SESSION['admin_settings_msg'])): ?>
              <div class="alert alert-info text-center mb-3"><?php echo $_SESSION['admin_settings_msg']; unset($_SESSION['admin_settings_msg']); ?></div>
            <?php endif; ?>
            <form method="POST" action="change_admin_password.php">
              <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Change Password</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 