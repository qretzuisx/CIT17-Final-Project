<?php
/**
 * User Dashboard - Wellness Center
 */
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your dashboard');
    redirect('auth/login.php');
}

require_once __DIR__ . '/../includes/functions/appointments.php';
require_once __DIR__ . '/../includes/functions/reviews.php';
require_once __DIR__ . '/../includes/functions/users.php';
require_once __DIR__ . '/../includes/functions/payments.php';
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Get appointments
$upcoming_appointments = get_user_appointments($user_id, 'confirmed');
$past_appointments = get_user_appointments($user_id, 'completed');
$pending_appointments = get_user_appointments($user_id, 'pending');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_review'])) {
        require_once __DIR__ . '/../includes/functions/reviews.php';
        $result = submit_review(
            (int)$_POST['appointment_id'],
            $user_id,
            (int)$_POST['rating'],
            sanitize($_POST['comment'])
        );
        if ($result['success']) {
            setFlashMessage('success', 'Review submitted successfully!');
            redirect('dashboard.php');
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
    
    if (isset($_POST['cancel_appointment'])) {
        $result = update_appointment_status((int)$_POST['appointment_id'], 'canceled');
        if ($result['success']) {
            setFlashMessage('success', 'Appointment canceled successfully');
            redirect('dashboard.php');
        }
    }
}
?>

<div class="container py-5">
    <!-- Welcome Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p class="text-muted mb-0">Manage your appointments and profile from here.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Upcoming:</strong> <?php echo count($upcoming_appointments); ?></p>
                    <p class="mb-2"><strong>Pending:</strong> <?php echo count($pending_appointments); ?></p>
                    <p class="mb-0"><strong>Completed:</strong> <?php echo count($past_appointments); ?></p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="booking.php" class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="fas fa-calendar-plus"></i> Book New Appointment
                    </a>
                    <a href="services.php" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-spa"></i> Browse Services
                    </a>
                    <a href="#profile" class="btn btn-outline-primary btn-sm w-100" onclick="document.getElementById('profile').scrollIntoView()">
                        <i class="fas fa-user"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Upcoming Appointments -->
            <?php if (!empty($upcoming_appointments) || !empty($pending_appointments)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upcoming & Pending Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $all_upcoming = array_merge($pending_appointments, $upcoming_appointments);
                        foreach ($all_upcoming as $appointment): 
                            $payment = get_payment_by_appointment($appointment['appointment_id']);
                        ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6><?php echo htmlspecialchars($appointment['service_name']); ?></h6>
                                            <p class="mb-1">
                                                <i class="far fa-calendar"></i> 
                                                <?php echo formatDate($appointment['appointment_date']); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="far fa-clock"></i> 
                                                <?php echo formatTime($appointment['start_time']); ?> - 
                                                <?php echo formatTime($appointment['end_time']); ?>
                                            </p>
                                            <p class="mb-0">
                                                <i class="fas fa-user-md"></i> 
                                                <?php echo htmlspecialchars($appointment['therapist_name']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <span class="badge badge-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                            <?php if ($payment && $payment['payment_status'] === 'paid'): ?>
                                                <div class="mt-2">
                                                    <small class="text-success"><i class="fas fa-check-circle"></i> Paid</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                <input type="hidden" name="cancel_appointment" value="1">
                                                <button type="submit" class="btn btn-danger btn-sm w-100 mb-2">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Past Appointments -->
            <?php if (!empty($past_appointments)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Past Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($past_appointments as $appointment): 
                            $review = get_review_by_appointment($appointment['appointment_id']);
                        ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6><?php echo htmlspecialchars($appointment['service_name']); ?></h6>
                                            <p class="mb-1">
                                                <i class="far fa-calendar"></i> 
                                                <?php echo formatDate($appointment['appointment_date']); ?> at 
                                                <?php echo formatTime($appointment['start_time']); ?>
                                            </p>
                                            <p class="mb-0">
                                                <i class="fas fa-user-md"></i> 
                                                <?php echo htmlspecialchars($appointment['therapist_name']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php if ($review): ?>
                                                <div class="mb-2">
                                                    <small>Your Rating: 
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i < $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $appointment['appointment_id']; ?>">
                                                    View Review
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $appointment['appointment_id']; ?>">
                                                    <i class="fas fa-star"></i> Leave Review
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Review Modal -->
                            <div class="modal fade" id="reviewModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Review for <?php echo htmlspecialchars($appointment['service_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if ($review): ?>
                                                <p><strong>Rating:</strong> 
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i < $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </p>
                                                <p><strong>Comment:</strong></p>
                                                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                            <?php else: ?>
                                                <form method="POST">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rating *</label>
                                                        <select class="form-select" name="rating" required>
                                                            <option value="5">5 - Excellent</option>
                                                            <option value="4">4 - Very Good</option>
                                                            <option value="3">3 - Good</option>
                                                            <option value="2">2 - Fair</option>
                                                            <option value="1">1 - Poor</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Comment</label>
                                                        <textarea class="form-control" name="comment" rows="4" required></textarea>
                                                    </div>
                                                    <button type="submit" name="submit_review" class="btn btn-primary">
                                                        Submit Review
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Profile Management -->
            <div class="card" id="profile">
                <div class="card-header">
                    <h5 class="mb-0">Account Management</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number" 
                                   value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>

                    <hr>

                    <h6>Change Password</h6>
                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
