<?php
/**
 * Service Management Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Get all services
 * @return array
 */
function get_all_services() {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM services ORDER BY service_name");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get services error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get service by ID
 * @param int $service_id
 * @return array|null
 */
function get_service_by_id($service_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM services WHERE service_id = ?");
        $stmt->execute([$service_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Get service error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Create new service
 * @param string $service_name
 * @param string $description
 * @param int $duration
 * @param float $price
 * @return array
 */
function create_service($service_name, $description, $duration, $price) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("INSERT INTO services (service_name, description, duration, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$service_name, $description, $duration, $price]);
        
        return ['success' => true, 'message' => 'Service created successfully', 'service_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Create service error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Service creation failed. Please try again.'];
    }
}

/**
 * Update service
 * @param int $service_id
 * @param array $data
 * @return array
 */
function update_service($service_id, $data) {
    $db = getDBConnection();
    
    try {
        $allowed_fields = ['service_name', 'description', 'duration', 'price'];
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
        
        $values[] = $service_id;
        $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE service_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        return ['success' => true, 'message' => 'Service updated successfully'];
    } catch (PDOException $e) {
        error_log('Update service error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Update failed. Please try again.'];
    }
}

/**
 * Delete service
 * @param int $service_id
 * @return array
 */
function delete_service($service_id) {
    $db = getDBConnection();
    
    try {
        // Check if service has appointments
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM appointments WHERE service_id = ?");
        $stmt->execute([$service_id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Cannot delete service with existing appointments'];
        }
        
        $stmt = $db->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->execute([$service_id]);
        
        return ['success' => true, 'message' => 'Service deleted successfully'];
    } catch (PDOException $e) {
        error_log('Delete service error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Delete failed. Please try again.'];
    }
}
?>

