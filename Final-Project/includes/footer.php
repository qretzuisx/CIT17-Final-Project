    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-car-wash"></i> Car Wash System</h5>
                    <p>Professional car wash services at your convenience.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>index.php" class="text-white-50">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/services.php" class="text-white-50">Services</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/booking.php" class="text-white-50">Book Appointment</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/contact.php" class="text-white-50">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p class="text-white-50">
                        <i class="fas fa-map-marker-alt"></i> 123 Main Street, Manila<br>
                        <i class="fas fa-phone"></i> (02) 1234-5678<br>
                        <i class="fas fa-envelope"></i> info@carwash.com
                    </p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Car Wash Appointment System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo JS_URL; ?>main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo JS_URL . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
