<?php
/**
 * Home Page - Wellness Center Booking & Reservation System
 */
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'config/config.php';
require_once 'includes/functions/services.php';
require_once 'includes/functions/reviews.php';

// Get featured services and reviews
$services = get_all_services();
$reviews = get_all_reviews(5);
?>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Your Wellness Journey Starts Here</h1>
                    <p class="lead mb-4">Experience tranquility and rejuvenation at our premier wellness center. Book your appointment today and discover the path to better health and inner peace.</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-user-plus"></i> Create Account
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>pages/services.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-spa"></i> View Services
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-spa fa-10x" style="opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Overview Section -->
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Wellness Services</h2>
                <p class="lead text-muted">Choose from our wide range of therapeutic and relaxation services</p>
            </div>
            <div class="services-grid">
                <?php 
                $featured_services = array_slice($services, 0, 5);
                if (!empty($featured_services)): 
                    foreach ($featured_services as $service): 
                ?>
                    <div class="service-card fade-in">
                        <div class="service-card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-spa fa-3x text-primary"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($service['service_name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="service-price"><?php echo formatCurrency($service['price']); ?></span>
                                <span class="service-duration">
                                    <i class="far fa-clock"></i> <?php echo $service['duration']; ?> min
                                </span>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>pages/services.php#service-<?php echo $service['service_id']; ?>" class="btn btn-primary btn-sm w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No services available at the moment.</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5">
                <a href="<?php echo BASE_URL; ?>pages/services.php" class="btn btn-primary btn-lg">
                    View All Services
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <?php if (!empty($reviews)): ?>
    <section class="section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>What Our Clients Say</h2>
                <p class="lead text-muted">Real experiences from our valued customers</p>
            </div>
            <div class="testimonials-carousel">
                <?php foreach ($reviews as $review): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-avatar">
                            <?php echo strtoupper(substr($review['reviewer_name'], 0, 1)); ?>
                        </div>
                        <div class="testimonial-rating">
                            <?php 
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < $review['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <p class="mb-3">"<?php echo htmlspecialchars(substr($review['comment'], 0, 150)); ?><?php echo strlen($review['comment']) > 150 ? '...' : ''; ?>"</p>
                        <p class="mb-0"><strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong></p>
                        <small class="text-muted"><?php echo htmlspecialchars($review['service_name']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="section">
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
                        <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                        <h4>Expert Therapists</h4>
                        <p>Licensed professionals dedicated to your wellness</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                        <h4>Personalized Care</h4>
                        <p>Tailored treatments for your unique needs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-box p-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Safe & Clean</h4>
                        <p>Highest standards of hygiene and safety</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="section bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Relax? Book Your Session Today!</h2>
            <p class="lead mb-4">Join thousands of satisfied clients who trust us for their wellness journey</p>
            <div class="d-grid gap-2 d-md-flex justify-content-center">
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>pages/booking.php" class="btn btn-accent btn-lg px-5">
                    <i class="fas fa-calendar-check"></i> Book Now
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
