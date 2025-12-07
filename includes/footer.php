    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-spa"></i> <?php echo SITE_NAME; ?></h5>
                    <p>Your wellness journey starts here. Book your appointment and experience tranquility.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/services.php">Services</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/booking.php">Book Appointment</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>pages/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> 123 Wellness Way, City<br>
                        <i class="fas fa-phone"></i> (02) 1234-5678<br>
                        <i class="fas fa-envelope"></i> info@wellness.com
                    </p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
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
