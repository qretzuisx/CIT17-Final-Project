<?php
/**
 * User Management Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Register a new user
 * @param string $full_name
 * @param string $email
 * @param string $phone_number
 * @param string $password
 * @param string $role
 * @return array
 */
function register_user($full_name, $email, $phone_number, $password, $role = 'customer') {
    $db = getDBConnection();
    
    try {
        // Check if email already exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $db->prepare("INSERT INTO users (full_name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone_number, $hashed_password, $role]);
        
        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 * @param string $email
 * @param string $password
 * @return array
 */
function login_user($email, $password) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT user_id, full_name, email, phone_number, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Start session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Update user profile
 * @param int $user_id
 * @param array $data
 * @return array
 */
function update_user_profile($user_id, $data) {
    $db = getDBConnection();
    
    try {
        $allowed_fields = ['full_name', 'email', 'phone_number'];
        $updates = [];
        $values = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $values[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        // Update session
        if (isset($data['full_name'])) {
            $_SESSION['full_name'] = $data['full_name'];
        }
        if (isset($data['email'])) {
            $_SESSION['email'] = $data['email'];
        }
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        error_log('Profile update error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Update failed. Please try again.'];
    }
}

/**
 * Change user password
 * @param int $user_id
 * @param string $old_password
 * @param string $new_password
 * @return array
 */
function change_password($user_id, $old_password, $new_password) {
    $db = getDBConnection();
    
    try {
        // Verify old password
        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($old_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    } catch (PDOException $e) {
        error_log('Password change error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Password change failed. Please try again.'];
    }
}

/**
 * Get user by ID
 * @param int $user_id
 * @return array|null
 */
function get_user_by_id($user_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT user_id, full_name, email, phone_number, role, created_at FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Get user error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all therapists
 * @return array
 */
function get_all_therapists() {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT user_id, full_name, email, phone_number FROM users WHERE role = 'therapist' ORDER BY full_name");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get therapists error: ' . $e->getMessage());
        return [];
    }
}
?>

