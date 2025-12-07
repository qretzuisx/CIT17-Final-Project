<?php
/**
 * Registration Page - Wellness Center
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/users.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
}

$error = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'phone_number' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = sanitize($_POST['full_name'] ?? '');
    $formData['email'] = sanitize($_POST['email'] ?? '');
    $formData['phone_number'] = sanitize($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'customer');
    
    // Validation
    if (empty($formData['full_name']) || empty($formData['email']) || 
        empty($formData['phone_number']) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $result = register_user(
            $formData['full_name'],
            $formData['email'],
            $formData['phone_number'],
            $password,
            $role
        );
        
        if ($result['success']) {
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect('login.php');
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h2>Create Account</h2>
                        <p class="text-muted">Join us today and start your wellness journey</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="registerForm">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($formData['full_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                   value="<?php echo htmlspecialchars($formData['phone_number']); ?>" 
                                   placeholder="09123456789" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                            </div>
                        </div>

                        <input type="hidden" name="role" value="customer">

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#">Terms and Conditions</a>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password match validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
