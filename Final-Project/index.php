<?php
/**
 * Home Page - Car Wash Appointment System
 */
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'config/database.php';

// Get database connection
$db = getDBConnection();

// Fetch featured services
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE status = 'active' ORDER BY service_id LIMIT 6");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    error_log('Error fetching services: ' . $e->getMessage());
}
?>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Professional Car Wash Services</h1>
                    <p class="lead mb-4">Book your car wash appointment online and enjoy our premium services at competitive prices.</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-light btn-lg px-4">Book Now</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-light btn-lg px-4">Get Started</a>
                            <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="<?php echo IMG_URL; ?>hero-car.png" alt="Car Wash" class="img-fluid" onerror="this.src='https://via.placeholder.com/600x400?text=Car+Wash+Services'">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                        <h4>Easy Booking</h4>
                        <p>Book your appointment online in just a few clicks</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Expert Washers</h4>
                        <p>Experienced professionals handle your vehicle with care</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-hand-sparkles fa-3x text-primary mb-3"></i>
                        <h4>Quality Service</h4>
                        <p>Premium products and thorough cleaning guaranteed</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h4>Fast & Efficient</h4>
                        <p>Quick service without compromising on quality</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">Choose from our wide range of car wash services</p>
            </div>
            <div class="row">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="<?php echo IMG_URL . ($service['image_url'] ?? 'default-service.jpg'); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($service['service_name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/350x200?text=<?php echo urlencode($service['service_name']); ?>'">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-primary mb-0"><?php echo formatCurrency($service['base_price']); ?></span>
                                        <span class="text-muted"><i class="far fa-clock"></i> <?php echo $service['duration_minutes']; ?> min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No services available at the moment.</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>pages/services.php" class="btn btn-primary btn-lg">View All Services</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">How It Works</h2>
                <p class="lead text-muted">Simple steps to get your car washed</p>
            </div>
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle mx-auto mb-3">1</div>
                    <h5>Create Account</h5>
                    <p>Sign up for free and add your vehicle details</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle mx-auto mb-3">2</div>
                    <h5>Choose Service</h5>
                    <p>Select the service that fits your needs</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle mx-auto mb-3">3</div>
                    <h5>Book Appointment</h5>
                    <p>Pick a date and time that works for you</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle mx-auto mb-3">4</div>
                    <h5>Get Service</h5>
                    <p>Show up and let us take care of your car</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers who trust us with their vehicles</p>
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-light btn-lg px-5">Sign Up Now</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-light btn-lg px-5">Book Appointment</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
