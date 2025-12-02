<?php
/**
 * Payment Page - Process appointment payment
 */
$page_title = 'Payment';
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to continue.');
    redirect('auth/login.php');
}

$db = getDBConnection();
$appointment_id = $_GET['appointment_id'] ?? '';

if (empty($appointment_id)) {
    setFlashMessage('danger', 'Invalid appointment.');
    redirect('pages/dashboard.php');
}

// Get appointment details
try {
    $stmt = $db->prepare("SELECT a.*, s.service_name, s.base_price, v.brand, v.model, v.plate_number, 
                                 u.full_name as washer_name
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN vehicles v ON a.vehicle_id = v.vehicle_id
                          JOIN washers w ON a.washer_id = w.washer_id
                          JOIN users u ON w.user_id = u.user_id
                          WHERE a.appointment_id = ? AND a.user_id = ?");
    $stmt->execute([$appointment_id, $_SESSION['user_id']]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        setFlashMessage('danger', 'Appointment not found.');
        redirect('pages/dashboard.php');
    }
    
    // Check if already paid
    $stmt = $db->prepare("SELECT payment_id FROM payments WHERE appointment_id = ? AND payment_status = 'completed'");
    $stmt->execute([$appointment_id]);
    if ($stmt->fetch()) {
        setFlashMessage('info', 'This appointment has already been paid.');
        redirect('pages/dashboard.php');
    }
    
} catch (PDOException $e) {
    error_log('Error fetching appointment: ' . $e->getMessage());
    setFlashMessage('danger', 'Error loading appointment details.');
    redirect('pages/dashboard.php');
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = sanitize($_POST['transaction_id'] ?? '');
    
    if (empty($payment_method)) {
        $error = 'Please select a payment method.';
    } else {
        try {
            $db->beginTransaction();
            
            // Generate transaction ID if not provided
            if (empty($transaction_id)) {
                $transaction_id = strtoupper(substr($payment_method, 0, 2)) . date('YmdHis') . rand(100, 999);
            }
            
            // Insert payment record
            $stmt = $db->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id, notes) 
                                 VALUES (?, ?, ?, 'completed', ?, 'Payment processed')");
            $stmt->execute([$appointment_id, $appointment['total_amount'], $payment_method, $transaction_id]);
            
            // Update appointment status
            $stmt = $db->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
            $stmt->execute([$appointment_id]);
            
            $db->commit();
            
            setFlashMessage('success', 'Payment successful! Your appointment is now confirmed.');
            redirect('pages/dashboard.php');
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Payment error: ' . $e->getMessage());
            $error = 'Payment processing failed. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Appointment Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Appointment Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Appointment ID</h6>
                            <p class="mb-0">#<?php echo $appointment['appointment_id']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Date & Time</h6>
                            <p class="mb-0"><?php echo formatDate($appointment['appointment_date']) . ' at ' . formatTime($appointment['appointment_time']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Service</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($appointment['service_name']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Vehicle</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($appointment['brand'] . ' ' . $appointment['model'] . ' (' . $appointment['plate_number'] . ')'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Washer</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($appointment['washer_name']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Amount to Pay</h6>
                            <p class="mb-0 h4 text-primary"><?php echo formatCurrency($appointment['total_amount']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-credit-card"></i> Payment Information</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="paymentForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Payment Method *</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card payment-option">
                                        <div class="card-body text-center">
                                            <input type="radio" class="btn-check" name="payment_method" id="cash" value="cash" required>
                                            <label class="btn btn-outline-primary w-100" for="cash">
                                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i><br>
                                                Cash on Service
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card payment-option">
                                        <div class="card-body text-center">
                                            <input type="radio" class="btn-check" name="payment_method" id="gcash" value="gcash">
                                            <label class="btn btn-outline-primary w-100" for="gcash">
                                                <i class="fas fa-mobile-alt fa-2x mb-2"></i><br>
                                                GCash
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card payment-option">
                                        <div class="card-body text-center">
                                            <input type="radio" class="btn-check" name="payment_method" id="paymaya" value="paymaya">
                                            <label class="btn btn-outline-primary w-100" for="paymaya">
                                                <i class="fas fa-mobile-alt fa-2x mb-2"></i><br>
                                                PayMaya
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card payment-option">
                                        <div class="card-body text-center">
                                            <input type="radio" class="btn-check" name="payment_method" id="credit_card" value="credit_card">
                                            <label class="btn btn-outline-primary w-100" for="credit_card">
                                                <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                                                Credit/Debit Card
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="transactionIdField" style="display: none;">
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">Transaction/Reference Number</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                       placeholder="Enter your transaction reference number">
                                <small class="text-muted">Optional: For online payments, enter your reference number</small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> By confirming this payment, you agree that the payment will be processed according to the selected method.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Confirm Payment - <?php echo formatCurrency($appointment['total_amount']); ?>
                            </button>
                            <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide transaction ID field based on payment method
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const transactionField = document.getElementById('transactionIdField');
        if (this.value !== 'cash') {
            transactionField.style.display = 'block';
        } else {
            transactionField.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
