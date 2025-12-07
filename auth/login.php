<?php
/**
 * Login Page - Wellness Center
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/users.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('pages/admin.php');
    } else {
        redirect('pages/dashboard.php');
    }
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $result = login_user($email, $password);
        
        if ($result['success']) {
            setFlashMessage('success', 'Welcome back, ' . $_SESSION['full_name'] . '!');
            
            // Redirect based on role
            if (isAdmin()) {
                redirect('pages/admin.php');
            } else {
                redirect('pages/dashboard.php');
            }
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-spa fa-3x text-primary mb-3"></i>
                        <h2>Login</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php">Register here</a>
                        </p>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Demo Accounts:</strong><br>
                            Admin: admin@wellness.com / password123<br>
                            Customer: john@example.com / password123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
