<?php
/**
 * Booking Page - Create new appointment
 */
$page_title = 'Book Appointment';
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to book an appointment.');
    redirect('auth/login.php');
}

require_once '../includes/header.php';

$db = getDBConnection();
$error = '';
$success = '';

// Get user's vehicles
try {
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    $vehicles = [];
    error_log('Error fetching vehicles: ' . $e->getMessage());
}

// Get services
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE status = 'active' ORDER BY base_price ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    error_log('Error fetching services: ' . $e->getMessage());
}

// Get washers
try {
    $stmt = $db->prepare("SELECT w.*, u.full_name FROM washers w 
                         JOIN users u ON w.user_id = u.user_id 
                         WHERE w.status = 'available' ORDER BY w.rating DESC");
    $stmt->execute();
    $washers = $stmt->fetchAll();
} catch (PDOException $e) {
    $washers = [];
    error_log('Error fetching washers: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $washer_id = $_POST['washer_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($vehicle_id) || empty($service_id) || empty($washer_id) || 
        empty($appointment_date) || empty($appointment_time)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $db->beginTransaction();
            
            // Get service price
            $stmt = $db->prepare("SELECT base_price FROM services WHERE service_id = ?");
            $stmt->execute([$service_id]);
            $service = $stmt->fetch();
            
            if (!$service) {
                throw new Exception('Service not found.');
            }
            
            // Find or create schedule
            $stmt = $db->prepare("SELECT schedule_id FROM schedules 
                                 WHERE washer_id = ? AND schedule_date = ? AND start_time = ? AND status = 'available'");
            $stmt->execute([$washer_id, $appointment_date, $appointment_time]);
            $schedule = $stmt->fetch();
            
            if (!$schedule) {
                // Create new schedule
                $end_time = date('H:i:s', strtotime($appointment_time) + 3600);
                $stmt = $db->prepare("INSERT INTO schedules (washer_id, schedule_date, start_time, end_time, status) 
                                     VALUES (?, ?, ?, ?, 'available')");
                $stmt->execute([$washer_id, $appointment_date, $appointment_time, $end_time]);
                $schedule_id = $db->lastInsertId();
            } else {
                $schedule_id = $schedule['schedule_id'];
            }
            
            // Create appointment
            $stmt = $db->prepare("INSERT INTO appointments 
                                 (user_id, vehicle_id, service_id, washer_id, schedule_id, 
                                  appointment_date, appointment_time, total_amount, status, notes) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([
                $_SESSION['user_id'], $vehicle_id, $service_id, $washer_id, $schedule_id,
                $appointment_date, $appointment_time, $service['base_price'], $notes
            ]);
            
            $appointment_id = $db->lastInsertId();
            
            // Update schedule status
            $stmt = $db->prepare("UPDATE schedules SET status = 'booked' WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);
            
            $db->commit();
            
            setFlashMessage('success', 'Appointment booked successfully! Proceed to payment.');
            redirect('pages/payment.php?appointment_id=' . $appointment_id);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Booking error: ' . $e->getMessage());
            $error = 'Failed to book appointment: ' . $e->getMessage();
        }
    }
}

$selected_service = isset($_GET['service_id']) ? $_GET['service_id'] : '';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-calendar-plus"></i> Book Appointment</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (empty($vehicles)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> You need to add a vehicle first.
                            <a href="<?php echo BASE_URL; ?>pages/profile.php#vehicles" class="btn btn-sm btn-warning ms-2">
                                Add Vehicle
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="bookingForm">
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Select Vehicle *</label>
                                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Choose your vehicle...</option>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <option value="<?php echo $vehicle['vehicle_id']; ?>">
                                            <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . 
                                                      ' (' . $vehicle['plate_number'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="service_id" class="form-label">Select Service *</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">Choose a service...</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['service_id']; ?>" 
                                                data-price="<?php echo $service['base_price']; ?>"
                                                data-duration="<?php echo $service['duration_minutes']; ?>"
                                                <?php echo ($selected_service == $service['service_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service['service_name']) . ' - ' . 
                                                      formatCurrency($service['base_price']) . 
                                                      ' (' . $service['duration_minutes'] . ' min)'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="washer_id" class="form-label">Select Washer *</label>
                                <select class="form-select" id="washer_id" name="washer_id" required>
                                    <option value="">Choose a washer...</option>
                                    <?php foreach ($washers as $washer): ?>
                                        <option value="<?php echo $washer['washer_id']; ?>">
                                            <?php echo htmlspecialchars($washer['full_name']) . 
                                                      ' - Rating: ' . $washer['rating'] . '/5.0 ' .
                                                      '(' . $washer['total_jobs'] . ' jobs)'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date *</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_time" class="form-label">Appointment Time *</label>
                                    <select class="form-select" id="appointment_time" name="appointment_time" required>
                                        <option value="">Select time...</option>
                                        <option value="08:00:00">8:00 AM</option>
                                        <option value="09:00:00">9:00 AM</option>
                                        <option value="10:00:00">10:00 AM</option>
                                        <option value="11:00:00">11:00 AM</option>
                                        <option value="13:00:00">1:00 PM</option>
                                        <option value="14:00:00">2:00 PM</option>
                                        <option value="15:00:00">3:00 PM</option>
                                        <option value="16:00:00">4:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Any special requests or instructions..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i> Confirm Booking
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Booking Summary -->
            <div class="card shadow mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div id="summaryContent">
                        <p class="text-muted text-center">Select service to see details</p>
                    </div>
                </div>
            </div>

            <!-- Booking Tips -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Booking Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Book in advance for better availability</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Arrive 5 minutes early</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Remove valuables from vehicle</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Inform us of any special requirements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('service_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    const duration = selectedOption.getAttribute('data-duration');
    const serviceName = selectedOption.text.split(' - ')[0];
    
    if (price) {
        document.getElementById('summaryContent').innerHTML = `
            <h6 class="fw-bold">${serviceName}</h6>
            <hr>
            <div class="d-flex justify-content-between mb-2">
                <span>Duration:</span>
                <strong>${duration} minutes</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Price:</span>
                <strong class="text-primary">₱${parseFloat(price).toFixed(2)}</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <span class="fw-bold">Total:</span>
                <strong class="text-primary h5">₱${parseFloat(price).toFixed(2)}</strong>
            </div>
        `;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
