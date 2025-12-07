/**
 * Wellness Center Booking System - Main JavaScript
 * Common utilities and AJAX functions
 */

// Base API URL
const API_BASE_URL = 'api/';

/**
 * AJAX Helper Function
 */
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                resolve(data);
            })
            .catch(error => {
                reject(error);
            });
    });
}

/**
 * Load Services
 */
async function loadServices() {
    try {
        const response = await makeRequest(API_BASE_URL + 'get_services.php');
        if (response.success) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Error loading services:', error);
        return [];
    }
}

/**
 * Load Therapists
 */
async function loadTherapists() {
    try {
        const response = await makeRequest(API_BASE_URL + 'get_therapists.php');
        if (response.success) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Error loading therapists:', error);
        return [];
    }
}

/**
 * Load Available Time Slots
 */
async function loadAvailability(therapistId, date, serviceId) {
    try {
        const url = `${API_BASE_URL}get_availability.php?therapist_id=${therapistId}&date=${date}&service_id=${serviceId}`;
        const response = await makeRequest(url);
        if (response.success) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Error loading availability:', error);
        return [];
    }
}

/**
 * Apply Promo Code
 */
async function applyPromoCode(promoCode, totalAmount) {
    try {
        const response = await makeRequest(API_BASE_URL + 'apply_promo.php', 'POST', {
            promo_code: promoCode,
            total_amount: totalAmount
        });
        return response;
    } catch (error) {
        console.error('Error applying promo code:', error);
        return { success: false, message: 'Failed to apply promo code' };
    }
}

/**
 * Create Appointment
 */
async function createAppointment(appointmentData) {
    try {
        const response = await makeRequest(API_BASE_URL + 'create_appointment.php', 'POST', appointmentData);
        return response;
    } catch (error) {
        console.error('Error creating appointment:', error);
        return { success: false, message: 'Failed to create appointment' };
    }
}

/**
 * Create Payment
 */
async function createPayment(paymentData) {
    try {
        const response = await makeRequest(API_BASE_URL + 'create_payment.php', 'POST', paymentData);
        return response;
    } catch (error) {
        console.error('Error creating payment:', error);
        return { success: false, message: 'Failed to process payment' };
    }
}

/**
 * Show Notification
 */
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Format Date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

/**
 * Format Time
 */
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

/**
 * Validate Email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate Phone
 */
function validatePhone(phone) {
    const re = /^[0-9]{10,15}$/;
    return re.test(phone.replace(/\D/g, ''));
}

/**
 * Password Strength Meter
 */
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    return {
        strength: strength,
        level: ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength] || 'Very Weak'
    };
}

/**
 * Show Loading Spinner
 */
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="spinner"></div>';
    }
}

/**
 * Hide Loading Spinner
 */
function hideLoading(element) {
    if (element) {
        element.innerHTML = '';
    }
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Initialize on DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
