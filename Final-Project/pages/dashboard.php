<?php
/**
 * Dashboard Page - User's appointment management
 */
$page_title = 'Dashboard';
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your dashboard.');
    redirect('auth/login.php');
}

require_once '../includes/header.php';

$db = getDBConnection();

// Get user's appointments
try {
    $stmt = $db->prepare("SELECT a.*, s.service_name, s.base_price, v.brand, v.model, v.plate_number, 
                                 u.full_name as washer_name, p.payment_status, p.payment_method
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN vehicles v ON a.vehicle_id = v.vehicle_id
                          JOIN washers w ON a.washer_id = w.washer_id
                          JOIN users u ON w.user_id = u.user_id
                          LEFT JOIN payments p ON a.appointment_id = p.appointment_id
                          WHERE a.user_id = ?
                          ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $appointments = [];
    error_log('Error fetching appointments: ' . $e->getMessage());
}

// Get statistics
try {
    $stmt = $db->prepare("SELECT 
                            COUNT(*) as total_appointments,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                          FROM appointments WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    $stats = ['total_appointments' => 0, 'completed' => 0, 'pending' => 0, 'cancelled' => 0];
    error_log('Error fetching statistics: ' . $e->getMessage());
}

// Handle appointment cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    try {
        $stmt = $db->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        setFlashMessage('success', 'Appointment cancelled successfully.');
        redirect('pages/dashboard.php');
    } catch (PDOException $e) {
        error_log('Error cancelling appointment: ' . $e->getMessage());
        setFlashMessage('danger', 'Failed to cancel appointment.');
    }
}
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p class="lead text-muted">Manage your appointments and track your car wash history</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total</h6>
                            <h2 class="mb-0"><?php echo $stats['total_appointments']; ?></h2>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Completed</h6>
                            <h2 class="mb-0"><?php echo $stats['completed']; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending</h6>
                            <h2 class="mb-0"><?php echo $stats['pending']; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Cancelled</h6>
                            <h2 class="mb-0"><?php echo $stats['cancelled']; ?></h2>
                        </div>
                        <i class="fas fa-times-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Appointment
                        </a>
                        <a href="<?php echo BASE_URL; ?>pages/services.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View Services
                        </a>
                        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> My Appointments</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($appointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Vehicle</th>
                                        <th>Washer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>#<?php echo $appointment['appointment_id']; ?></td>
                                            <td>
                                                <?php echo formatDate($appointment['appointment_date']); ?><br>
                                                <small class="text-muted"><?php echo formatTime($appointment['appointment_time']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($appointment['brand'] . ' ' . $appointment['model']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($appointment['plate_number']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['washer_name']); ?></td>
                                            <td><?php echo formatCurrency($appointment['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'in_progress' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $class = $statusClass[$appointment['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $class; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($appointment['payment_status']): ?>
                                                    <span class="badge bg-<?php echo $appointment['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($appointment['payment_status']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Unpaid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>pages/appointment-details.php?id=<?php echo $appointment['appointment_id']; ?>" 
                                                       class="btn btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($appointment['status'] === 'pending' && !$appointment['payment_status']): ?>
                                                        <a href="<?php echo BASE_URL; ?>pages/payment.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" 
                                                           class="btn btn-success" title="Pay Now">
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                            <button type="submit" name="cancel_appointment" class="btn btn-danger" title="Cancel">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h4>No Appointments Yet</h4>
                            <p class="text-muted">Book your first car wash appointment now!</p>
                            <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Book Now
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
