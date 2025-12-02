<?php
/**
 * Profile Page - User profile and vehicle management
 */
$page_title = 'My Profile';
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your profile.');
    redirect('auth/login.php');
}

$db = getDBConnection();
$error = '';
$success = '';

// Get user data
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error fetching user: ' . $e->getMessage());
    setFlashMessage('danger', 'Error loading profile.');
    redirect('pages/dashboard.php');
}

// Get user's vehicles
try {
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    $vehicles = [];
    error_log('Error fetching vehicles: ' . $e->getMessage());
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    try {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $phone, $address, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $full_name;
        setFlashMessage('success', 'Profile updated successfully!');
        redirect('pages/profile.php');
    } catch (PDOException $e) {
        error_log('Error updating profile: ' . $e->getMessage());
        $error = 'Failed to update profile.';
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch();
            
            if (password_verify($current_password, $user_data['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                setFlashMessage('success', 'Password changed successfully!');
                redirect('pages/profile.php');
            } else {
                $error = 'Current password is incorrect.';
            }
        } catch (PDOException $e) {
            error_log('Error changing password: ' . $e->getMessage());
            $error = 'Failed to change password.';
        }
    }
}

// Handle add vehicle
if (isset($_POST['add_vehicle'])) {
    $vehicle_type = sanitize($_POST['vehicle_type']);
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $year = sanitize($_POST['year']);
    $color = sanitize($_POST['color']);
    $plate_number = sanitize($_POST['plate_number']);
    
    try {
        $stmt = $db->prepare("INSERT INTO vehicles (user_id, vehicle_type, brand, model, year, color, plate_number) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $vehicle_type, $brand, $model, $year, $color, $plate_number]);
        setFlashMessage('success', 'Vehicle added successfully!');
        redirect('pages/profile.php#vehicles');
    } catch (PDOException $e) {
        error_log('Error adding vehicle: ' . $e->getMessage());
        $error = 'Failed to add vehicle. Plate number may already exist.';
    }
}

// Handle delete vehicle
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = $_POST['vehicle_id'];
    try {
        $stmt = $db->prepare("UPDATE vehicles SET status = 'inactive' WHERE vehicle_id = ? AND user_id = ?");
        $stmt->execute([$vehicle_id, $_SESSION['user_id']]);
        setFlashMessage('success', 'Vehicle removed successfully!');
        redirect('pages/profile.php#vehicles');
    } catch (PDOException $e) {
        error_log('Error deleting vehicle: ' . $e->getMessage());
        $error = 'Failed to remove vehicle.';
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="text-muted"><small>Member since <?php echo formatDate($user['created_at']); ?></small></p>
                </div>
            </div>
            
            <div class="list-group mt-3 shadow">
                <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-user"></i> Profile Information
                </a>
                <a href="#vehicles" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-car"></i> My Vehicles
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-lock"></i> Security
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="tab-content">
                <!-- Profile Information Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-user"></i> Profile Information</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Vehicles Tab -->
                <div class="tab-pane fade" id="vehicles">
                    <div class="card shadow mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-car"></i> My Vehicles</h4>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                <i class="fas fa-plus"></i> Add Vehicle
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($vehicles)): ?>
                                <div class="row">
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <?php if ($vehicle['status'] === 'active'): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                                                                <p class="card-text mb-1">
                                                                    <strong>Type:</strong> <?php echo ucfirst($vehicle['vehicle_type']); ?><br>
                                                                    <strong>Year:</strong> <?php echo $vehicle['year']; ?><br>
                                                                    <strong>Color:</strong> <?php echo htmlspecialchars($vehicle['color']); ?><br>
                                                                    <strong>Plate:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?>
                                                                </p>
                                                            </div>
                                                            <form method="POST" onsubmit="return confirm('Remove this vehicle?');">
                                                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                                                <button type="submit" name="delete_vehicle" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                    <p>No vehicles added yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                        <i class="fas fa-plus"></i> Add Your First Vehicle
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-lock"></i> Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="passwordForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="6" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-car"></i> Add New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                        <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select type...</option>
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="truck">Truck</option>
                            <option value="van">Van</option>
                            <option value="motorcycle">Motorcycle</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label">Brand *</label>
                            <input type="text" class="form-control" id="brand" name="brand" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label">Model *</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="year" class="form-label">Year *</label>
                            <input type="number" class="form-control" id="year" name="year" 
                                   min="1900" max="<?php echo date('Y') + 1; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" name="color">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="plate_number" class="form-label">Plate Number *</label>
                        <input type="text" class="form-control" id="plate_number" name="plate_number" 
                               placeholder="ABC1234" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_vehicle" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    var newPass = document.getElementById('new_password').value;
    var confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
