<?php
/**
 * Admin Dashboard - Wellness Center
 */
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('index.php');
}

require_once __DIR__ . '/../includes/functions/appointments.php';
require_once __DIR__ . '/../includes/functions/services.php';
require_once __DIR__ . '/../includes/functions/payments.php';
require_once __DIR__ . '/../includes/functions/users.php';
require_once __DIR__ . '/../includes/functions/availability.php';
require_once __DIR__ . '/../config/config.php';

$db = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_appointment_status'])) {
        $result = update_appointment_status((int)$_POST['appointment_id'], sanitize($_POST['status']));
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        redirect('admin.php');
    }
    
    if (isset($_POST['add_service'])) {
        $result = create_service(
            sanitize($_POST['service_name']),
            sanitize($_POST['description']),
            (int)$_POST['duration'],
            (float)$_POST['price']
        );
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        redirect('admin.php#services');
    }
    
    if (isset($_POST['add_availability'])) {
        $result = add_therapist_availability(
            (int)$_POST['therapist_id'],
            sanitize($_POST['date']),
            sanitize($_POST['start_time']),
            sanitize($_POST['end_time'])
        );
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        redirect('admin.php#availability');
    }
}

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM appointments");
$total_bookings = $stmt->fetch()['total'];

$stmt = $db->query("SELECT SUM(amount) as revenue FROM payments WHERE payment_status = 'paid'");
$revenue = $stmt->fetch()['revenue'] ?? 0;

$stmt = $db->query("SELECT COUNT(DISTINCT user_id) as customers FROM users WHERE role = 'customer'");
$active_customers = $stmt->fetch()['customers'];

// Get data
$all_appointments = get_all_appointments();
$services = get_all_services();
$therapists = get_all_therapists();
$all_payments = get_all_payments();
?>

<div class="container py-5">
    <h2 class="mb-4">Admin Dashboard</h2>

    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Bookings</h5>
                    <h2><?php echo $total_bookings; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <h2><?php echo formatCurrency($revenue); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Active Customers</h5>
                    <h2><?php echo $active_customers; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Management -->
    <div class="card mb-4" id="bookings">
        <div class="card-header">
            <h5 class="mb-0">Booking Management</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Therapist</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_appointments as $apt): ?>
                            <tr>
                                <td><?php echo $apt['appointment_id']; ?></td>
                                <td><?php echo htmlspecialchars($apt['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($apt['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($apt['therapist_name']); ?></td>
                                <td><?php echo formatDate($apt['appointment_date']); ?></td>
                                <td><?php echo formatTime($apt['start_time']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $apt['status']; ?>">
                                        <?php echo ucfirst($apt['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="appointment_id" value="<?php echo $apt['appointment_id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline" style="width: auto;" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $apt['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $apt['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $apt['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="canceled" <?php echo $apt['status'] === 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                                        </select>
                                        <input type="hidden" name="update_appointment_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Service Management -->
    <div class="card mb-4" id="services">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Service Management</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus"></i> Add Service
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo $service['service_id']; ?></td>
                                <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($service['description'], 0, 50)); ?>...</td>
                                <td><?php echo $service['duration']; ?> min</td>
                                <td><?php echo formatCurrency($service['price']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editService(<?php echo $service['service_id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Availability Management -->
    <div class="card mb-4" id="availability">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Therapist Schedule Management</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAvailabilityModal">
                <i class="fas fa-plus"></i> Add Availability
            </button>
        </div>
        <div class="card-body">
            <?php foreach ($therapists as $therapist): 
                $availability = get_therapist_availability($therapist['user_id']);
            ?>
                <h6><?php echo htmlspecialchars($therapist['full_name']); ?></h6>
                <?php if (!empty($availability)): ?>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availability as $avail): ?>
                                    <tr>
                                        <td><?php echo formatDate($avail['date']); ?></td>
                                        <td><?php echo formatTime($avail['start_time']); ?></td>
                                        <td><?php echo formatTime($avail['end_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No availability set.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Payment Management -->
    <div class="card mb-4" id="payments">
        <div class="card-header">
            <h5 class="mb-0">Payment Management</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Appointment</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td><?php echo htmlspecialchars($payment['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $payment['payment_status'] === 'paid' ? 'success' : ($payment['payment_status'] === 'refunded' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $payment['payment_date'] ? formatDate($payment['payment_date']) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (minutes) *</label>
                            <input type="number" class="form-control" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Availability Modal -->
<div class="modal fade" id="addAvailabilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Therapist Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Therapist *</label>
                        <select class="form-select" name="therapist_id" required>
                            <option value="">Select therapist...</option>
                            <?php foreach ($therapists as $therapist): ?>
                                <option value="<?php echo $therapist['user_id']; ?>">
                                    <?php echo htmlspecialchars($therapist['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_availability" class="btn btn-primary">Add Availability</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

