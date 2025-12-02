<?php
/**
 * Services Page - Display all available car wash services
 */
$page_title = 'Our Services';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/header.php';

// Get database connection
$db = getDBConnection();

// Fetch all active services
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE status = 'active' ORDER BY base_price ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    error_log('Error fetching services: ' . $e->getMessage());
}
?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Our Services</h1>
        <p class="lead text-muted">Choose the perfect car wash service for your vehicle</p>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchService" placeholder="Search services...">
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="row" id="servicesContainer">
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4 mb-4 service-item">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <img src="<?php echo IMG_URL . ($service['image_url'] ?? 'default-service.jpg'); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($service['service_name']); ?>"
                             style="height: 250px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/400x250?text=<?php echo urlencode($service['service_name']); ?>'">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($service['description']); ?></p>
                            
                            <div class="service-details mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-clock text-primary"></i> Duration:</span>
                                    <strong><?php echo $service['duration_minutes']; ?> minutes</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-car text-primary"></i> Vehicle Type:</span>
                                    <strong><?php echo ucfirst($service['vehicle_type']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-tag text-primary"></i> Price:</span>
                                    <strong class="text-primary h5 mb-0"><?php echo formatCurrency($service['base_price']); ?></strong>
                                </div>
                            </div>

                            <?php if (isLoggedIn()): ?>
                                <a href="<?php echo BASE_URL; ?>pages/booking.php?service_id=<?php echo $service['service_id']; ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-check"></i> Book This Service
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-primary w-100">
                                    Login to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No Services Available</h4>
                    <p>Please check back later for our car wash services.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Why Choose Us Section -->
    <div class="row mt-5 pt-5 border-top">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Why Choose Our Services?</h2>
        </div>
        <div class="col-md-3 text-center mb-3">
            <i class="fas fa-star fa-3x text-warning mb-3"></i>
            <h5>Quality Products</h5>
            <p>We use only premium car care products</p>
        </div>
        <div class="col-md-3 text-center mb-3">
            <i class="fas fa-user-check fa-3x text-success mb-3"></i>
            <h5>Trained Staff</h5>
            <p>Experienced professionals at your service</p>
        </div>
        <div class="col-md-3 text-center mb-3">
            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
            <h5>Safe & Secure</h5>
            <p>Your vehicle is in safe hands</p>
        </div>
        <div class="col-md-3 text-center mb-3">
            <i class="fas fa-money-bill-wave fa-3x text-info mb-3"></i>
            <h5>Best Prices</h5>
            <p>Competitive pricing for all services</p>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchService').addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var serviceItems = document.querySelectorAll('.service-item');
    
    serviceItems.forEach(function(item) {
        var serviceName = item.querySelector('.card-title').textContent.toLowerCase();
        var serviceDesc = item.querySelector('.card-text').textContent.toLowerCase();
        
        if (serviceName.includes(searchText) || serviceDesc.includes(searchText)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// Add hover effect
document.querySelectorAll('.hover-shadow').forEach(function(card) {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.transition = 'all 0.3s ease';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
