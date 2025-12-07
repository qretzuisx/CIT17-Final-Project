<?php
/**
 * Booking Page - Wellness Center
 */
$page_title = 'Book Appointment';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions/services.php';
require_once __DIR__ . '/../includes/functions/users.php';

if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to book an appointment');
    redirect('auth/login.php');
}

$services = get_all_services();
$therapists = get_all_therapists();
$selected_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4">Book Your Appointment</h2>

            <!-- Booking Steps -->
            <div class="booking-steps mb-5">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Service & Therapist</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Date & Time</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirm & Pay</div>
                </div>
            </div>

            <!-- Step 1: Service & Therapist Selection -->
            <div id="step1" class="booking-step">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Step 1: Select Service & Therapist</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Select Service *</label>
                            <select class="form-select" id="serviceSelect" required>
                                <option value="">Choose a service...</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['service_id']; ?>" 
                                            data-price="<?php echo $service['price']; ?>"
                                            data-duration="<?php echo $service['duration']; ?>"
                                            <?php echo $service['service_id'] == $selected_service_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['service_name']); ?> - 
                                        <?php echo formatCurrency($service['price']); ?> 
                                        (<?php echo $service['duration']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Select Therapist *</label>
                            <select class="form-select" id="therapistSelect" required>
                                <option value="">Choose a therapist...</option>
                                <?php foreach ($therapists as $therapist): ?>
                                    <option value="<?php echo $therapist['user_id']; ?>">
                                        <?php echo htmlspecialchars($therapist['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" onclick="goToStep(2)" id="nextStep1">
                                Next: Date & Time <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Date & Time Selection -->
            <div id="step2" class="booking-step" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Step 2: Select Date & Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Select Date *</label>
                            <input type="date" class="form-control" id="appointmentDate" required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Available Time Slots</label>
                            <div id="timeSlots" class="time-slots">
                                <p class="text-muted">Please select a service, therapist, and date first.</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button class="btn btn-primary" onclick="goToStep(3)" id="nextStep2" disabled>
                                Next: Confirm & Pay <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Confirmation & Payment -->
            <div id="step3" class="booking-step" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Step 3: Confirm & Payment</h5>
                    </div>
                    <div class="card-body">
                        <!-- Appointment Summary -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Appointment Summary</h6>
                                <p class="mb-1"><strong>Service:</strong> <span id="summaryService"></span></p>
                                <p class="mb-1"><strong>Therapist:</strong> <span id="summaryTherapist"></span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="summaryDate"></span></p>
                                <p class="mb-1"><strong>Time:</strong> <span id="summaryTime"></span></p>
                                <p class="mb-0"><strong>Duration:</strong> <span id="summaryDuration"></span> minutes</p>
                            </div>
                        </div>

                        <!-- Cost Calculation -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Service Price:</span>
                                <span id="originalPrice">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                                <span>Discount:</span>
                                <span class="text-success" id="discountAmount">-$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="totalPrice">$0.00</strong>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="mb-4">
                            <label class="form-label">Promo Code (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="promoCode" placeholder="Enter promo code">
                                <button class="btn btn-outline-primary" onclick="applyPromo()">Apply</button>
                            </div>
                            <small id="promoMessage" class="text-muted"></small>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label">Payment Method *</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="">Select payment method...</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the terms and conditions *
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary" onclick="goToStep(2)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button class="btn btn-primary btn-lg" onclick="confirmBooking()" id="confirmBtn">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" style="display: none;">
                <div class="text-center">
                    <div class="spinner"></div>
                    <p>Processing your booking...</p>
                </div>
            </div>
        </div>

        <!-- Summary Panel (Right Side) -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header">
                    <h5 class="mb-0">Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div id="bookingSummary">
                        <p class="text-muted">Complete the steps to see your booking summary.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let bookingData = {
    service_id: null,
    therapist_id: null,
    appointment_date: null,
    start_time: null,
    service_price: 0,
    discount: 0,
    final_price: 0
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    updateSummary();
    
    // Service and therapist selection
    document.getElementById('serviceSelect').addEventListener('change', function() {
        bookingData.service_id = this.value;
        const option = this.options[this.selectedIndex];
        if (option.value) {
            bookingData.service_price = parseFloat(option.dataset.price);
            bookingData.duration = parseInt(option.dataset.duration);
        }
        updateSummary();
        if (bookingData.service_id && bookingData.therapist_id) {
            loadAvailability();
        }
    });

    document.getElementById('therapistSelect').addEventListener('change', function() {
        bookingData.therapist_id = this.value;
        updateSummary();
        if (bookingData.service_id && bookingData.therapist_id) {
            loadAvailability();
        }
    });

    // Date selection
    document.getElementById('appointmentDate').addEventListener('change', function() {
        bookingData.appointment_date = this.value;
        updateSummary();
        loadAvailability();
    });
});

function goToStep(step) {
    // Hide all steps
    for (let i = 1; i <= 3; i++) {
        document.getElementById('step' + i).style.display = 'none';
        document.querySelector(`.step[data-step="${i}"]`).classList.remove('active');
    }

    // Show selected step
    document.getElementById('step' + step).style.display = 'block';
    document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

    // Mark previous steps as completed
    for (let i = 1; i < step; i++) {
        document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
    }
}

async function loadAvailability() {
    if (!bookingData.service_id || !bookingData.therapist_id || !bookingData.appointment_date) {
        return;
    }

    const slotsContainer = document.getElementById('timeSlots');
    slotsContainer.innerHTML = '<div class="spinner"></div>';

    try {
        const slots = await loadAvailabilityData(
            bookingData.therapist_id,
            bookingData.appointment_date,
            bookingData.service_id
        );

        if (slots.length === 0) {
            slotsContainer.innerHTML = '<p class="text-muted">No available time slots for this date. Please select another date.</p>';
            return;
        }

        slotsContainer.innerHTML = '';
        slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-slot';
            btn.textContent = slot.display;
            btn.dataset.startTime = slot.start_time;
            btn.onclick = function() {
                document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                bookingData.start_time = this.dataset.startTime;
                updateSummary();
                document.getElementById('nextStep2').disabled = false;
            };
            slotsContainer.appendChild(btn);
        });
    } catch (error) {
        slotsContainer.innerHTML = '<p class="text-danger">Error loading availability. Please try again.</p>';
    }
}

async function loadAvailabilityData(therapistId, date, serviceId) {
    const response = await fetch(`api/get_availability.php?therapist_id=${therapistId}&date=${date}&service_id=${serviceId}`);
    const data = await response.json();
    return data.success ? data.data : [];
}

function updateSummary() {
    const summaryDiv = document.getElementById('bookingSummary');
    const serviceSelect = document.getElementById('serviceSelect');
    const therapistSelect = document.getElementById('therapistSelect');
    
    let html = '';
    
    if (bookingData.service_id && serviceSelect.options[serviceSelect.selectedIndex]) {
        const serviceName = serviceSelect.options[serviceSelect.selectedIndex].text.split(' - ')[0];
        html += `<p><strong>Service:</strong> ${serviceName}</p>`;
        html += `<p><strong>Price:</strong> ${formatCurrency(bookingData.service_price)}</p>`;
    }
    
    if (bookingData.therapist_id && therapistSelect.options[therapistSelect.selectedIndex]) {
        html += `<p><strong>Therapist:</strong> ${therapistSelect.options[therapistSelect.selectedIndex].text}</p>`;
    }
    
    if (bookingData.appointment_date) {
        html += `<p><strong>Date:</strong> ${formatDate(bookingData.appointment_date)}</p>`;
    }
    
    if (bookingData.start_time) {
        html += `<p><strong>Time:</strong> ${formatTime(bookingData.start_time)}</p>`;
    }
    
    if (!html) {
        html = '<p class="text-muted">Complete the steps to see your booking summary.</p>';
    }
    
    summaryDiv.innerHTML = html;
    
    // Update step 3 summary
    if (document.getElementById('step3').style.display !== 'none') {
        updateStep3Summary();
    }
}

function updateStep3Summary() {
    const serviceSelect = document.getElementById('serviceSelect');
    const therapistSelect = document.getElementById('therapistSelect');
    
    if (serviceSelect.options[serviceSelect.selectedIndex]) {
        document.getElementById('summaryService').textContent = serviceSelect.options[serviceSelect.selectedIndex].text.split(' - ')[0];
    }
    if (therapistSelect.options[therapistSelect.selectedIndex]) {
        document.getElementById('summaryTherapist').textContent = therapistSelect.options[therapistSelect.selectedIndex].text;
    }
    if (bookingData.appointment_date) {
        document.getElementById('summaryDate').textContent = formatDate(bookingData.appointment_date);
    }
    if (bookingData.start_time) {
        document.getElementById('summaryTime').textContent = formatTime(bookingData.start_time);
    }
    if (bookingData.duration) {
        document.getElementById('summaryDuration').textContent = bookingData.duration;
    }
    
    document.getElementById('originalPrice').textContent = formatCurrency(bookingData.service_price);
    const finalPrice = bookingData.service_price - bookingData.discount;
    bookingData.final_price = finalPrice;
    document.getElementById('totalPrice').textContent = formatCurrency(finalPrice);
}

async function applyPromo() {
    const promoCode = document.getElementById('promoCode').value;
    const messageEl = document.getElementById('promoMessage');
    
    if (!promoCode) {
        messageEl.textContent = 'Please enter a promo code';
        messageEl.className = 'text-danger';
        return;
    }
    
    messageEl.textContent = 'Applying...';
    messageEl.className = 'text-info';
    
    try {
        const result = await applyPromoCode(promoCode, bookingData.service_price);
        if (result.success) {
            bookingData.discount = result.discount_amount;
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('discountAmount').textContent = '-' + formatCurrency(result.discount_amount);
            updateStep3Summary();
            messageEl.textContent = 'Promo code applied successfully!';
            messageEl.className = 'text-success';
        } else {
            bookingData.discount = 0;
            document.getElementById('discountRow').style.display = 'none';
            updateStep3Summary();
            messageEl.textContent = result.message;
            messageEl.className = 'text-danger';
        }
    } catch (error) {
        messageEl.textContent = 'Error applying promo code';
        messageEl.className = 'text-danger';
    }
}

async function confirmBooking() {
    if (!bookingData.service_id || !bookingData.therapist_id || !bookingData.appointment_date || !bookingData.start_time) {
        showNotification('Please complete all booking details', 'error');
        return;
    }
    
    const paymentMethod = document.getElementById('paymentMethod').value;
    if (!paymentMethod) {
        showNotification('Please select a payment method', 'error');
        return;
    }
    
    if (!document.getElementById('termsCheck').checked) {
        showNotification('Please agree to the terms and conditions', 'error');
        return;
    }
    
    // Hide steps, show loading
    document.getElementById('step3').style.display = 'none';
    document.getElementById('loadingIndicator').style.display = 'block';
    
    try {
        // Create appointment
        const appointmentResult = await createAppointment({
            service_id: bookingData.service_id,
            therapist_id: bookingData.therapist_id,
            appointment_date: bookingData.appointment_date,
            start_time: bookingData.start_time
        });
        
        if (!appointmentResult.success) {
            throw new Error(appointmentResult.message);
        }
        
        // Create payment
        const paymentResult = await createPayment({
            appointment_id: appointmentResult.appointment_id,
            amount: bookingData.final_price,
            payment_method: paymentMethod
        });
        
        if (!paymentResult.success) {
            throw new Error(paymentResult.message);
        }
        
        // Success
        window.location.href = 'dashboard.php?success=booking_confirmed';
        
    } catch (error) {
        document.getElementById('loadingIndicator').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
        showNotification(error.message || 'Booking failed. Please try again.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
