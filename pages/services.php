<?php
/**
 * Services Page - Wellness Center
 */
$page_title = 'Our Services';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions/services.php';
require_once __DIR__ . '/../includes/functions/users.php';

$services = get_all_services();
$therapists = get_all_therapists();
?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1>Our Wellness Services</h1>
        <p class="lead text-muted">Choose the perfect service for your wellness journey</p>
    </div>

    <!-- Filter and Search Section -->
    <div class="filter-section mb-4">
        <div class="row">
            <!-- Search -->
            <div class="col-md-4 mb-3">
                <label for="searchInput" class="form-label">Search Services</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name or description...">
                </div>
            </div>

            <!-- Price Range -->
            <div class="col-md-4 mb-3">
                <label for="priceRange" class="form-label">Price Range: <span id="priceDisplay">$0 - $200</span></label>
                <input type="range" class="form-range" id="priceRange" min="0" max="200" value="200" step="10">
            </div>

            <!-- Duration Filter -->
            <div class="col-md-4 mb-3">
                <label class="form-label">Duration</label>
                <div class="btn-group w-100" role="group">
                    <input type="checkbox" class="btn-check" id="duration30" autocomplete="off" value="30">
                    <label class="btn btn-outline-primary" for="duration30">30min</label>
                    
                    <input type="checkbox" class="btn-check" id="duration60" autocomplete="off" value="60">
                    <label class="btn btn-outline-primary" for="duration60">60min</label>
                    
                    <input type="checkbox" class="btn-check" id="duration90" autocomplete="off" value="90">
                    <label class="btn btn-outline-primary" for="duration90">90min</label>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Therapist Filter -->
            <div class="col-md-4 mb-3">
                <label for="therapistFilter" class="form-label">Therapist</label>
                <select class="form-select" id="therapistFilter">
                    <option value="">All Therapists</option>
                    <?php foreach ($therapists as $therapist): ?>
                        <option value="<?php echo $therapist['user_id']; ?>"><?php echo htmlspecialchars($therapist['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="col-md-4 mb-3">
                <label for="sortSelect" class="form-label">Sort By</label>
                <select class="form-select" id="sortSelect">
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="duration">Duration</option>
                    <option value="name">Name (A-Z)</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="col-md-4 mb-3 d-flex align-items-end">
                <button class="btn btn-secondary w-100" id="clearFilters">
                    <i class="fas fa-redo"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="services-grid" id="servicesGrid">
        <?php foreach ($services as $service): ?>
            <div class="service-card" 
                 data-service-id="<?php echo $service['service_id']; ?>"
                 data-price="<?php echo $service['price']; ?>"
                 data-duration="<?php echo $service['duration']; ?>"
                 data-name="<?php echo htmlspecialchars(strtolower($service['service_name'])); ?>"
                 data-description="<?php echo htmlspecialchars(strtolower($service['description'])); ?>">
                <div class="service-card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-spa fa-4x text-primary"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($service['service_name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="service-price"><?php echo formatCurrency($service['price']); ?></span>
                        <span class="service-duration">
                            <i class="far fa-clock"></i> <?php echo $service['duration']; ?> min
                        </span>
                    </div>
                    <div class="mt-3">
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo BASE_URL; ?>pages/booking.php?service_id=<?php echo $service['service_id']; ?>" class="btn btn-primary w-100">
                                Book This Service
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary w-100">
                                Login to Book
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="alert alert-info text-center" style="display: none;">
        <i class="fas fa-info-circle"></i> No services match your filters. Please try different criteria.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const priceRange = document.getElementById('priceRange');
    const priceDisplay = document.getElementById('priceDisplay');
    const durationFilters = document.querySelectorAll('[id^="duration"]');
    const therapistFilter = document.getElementById('therapistFilter');
    const sortSelect = document.getElementById('sortSelect');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const servicesGrid = document.getElementById('servicesGrid');
    const noResults = document.getElementById('noResults');

    let services = Array.from(document.querySelectorAll('.service-card'));

    // Update price display
    priceRange.addEventListener('input', function() {
        priceDisplay.textContent = `$0 - $${this.value}`;
        filterServices();
    });

    // Filter services
    function filterServices() {
        const searchTerm = searchInput.value.toLowerCase();
        const maxPrice = parseInt(priceRange.value);
        const selectedDurations = Array.from(durationFilters)
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));
        const selectedTherapist = therapistFilter.value;
        const sortBy = sortSelect.value;

        let filtered = services.filter(service => {
            const price = parseFloat(service.dataset.price);
            const duration = parseInt(service.dataset.duration);
            const name = service.dataset.name;
            const description = service.dataset.description;

            // Search filter
            if (searchTerm && !name.includes(searchTerm) && !description.includes(searchTerm)) {
                return false;
            }

            // Price filter
            if (price > maxPrice) {
                return false;
            }

            // Duration filter
            if (selectedDurations.length > 0 && !selectedDurations.includes(duration)) {
                return false;
            }

            return true;
        });

        // Sort
        filtered.sort((a, b) => {
            switch(sortBy) {
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'duration':
                    return parseInt(a.dataset.duration) - parseInt(b.dataset.duration);
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                default:
                    return 0;
            }
        });

        // Update display
        services.forEach(service => {
            service.style.display = 'none';
        });

        filtered.forEach(service => {
            service.style.display = 'block';
        });

        // Show/hide no results message
        if (filtered.length === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    // Event listeners
    searchInput.addEventListener('input', debounce(filterServices, 300));
    durationFilters.forEach(cb => cb.addEventListener('change', filterServices));
    therapistFilter.addEventListener('change', filterServices);
    sortSelect.addEventListener('change', filterServices);

    // Clear filters
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        priceRange.value = 200;
        priceDisplay.textContent = '$0 - $200';
        durationFilters.forEach(cb => cb.checked = false);
        therapistFilter.value = '';
        sortSelect.value = 'price-low';
        filterServices();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
