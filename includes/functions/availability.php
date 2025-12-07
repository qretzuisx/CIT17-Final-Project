<?php
/**
 * Availability Management Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Add therapist availability
 * @param int $therapist_id
 * @param string $date
 * @param string $start_time
 * @param string $end_time
 * @return array
 */
function add_therapist_availability($therapist_id, $date, $start_time, $end_time) {
    $db = getDBConnection();
    
    try {
        // Validate time range
        $start = new DateTime($date . ' ' . $start_time);
        $end = new DateTime($date . ' ' . $end_time);
        
        if ($end <= $start) {
            return ['success' => false, 'message' => 'End time must be after start time'];
        }
        
        $stmt = $db->prepare("INSERT INTO availability (therapist_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$therapist_id, $date, $start_time, $end_time]);
        
        return ['success' => true, 'message' => 'Availability added successfully', 'availability_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Add availability error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add availability. Please try again.'];
    }
}

/**
 * Get therapist availability
 * @param int $therapist_id
 * @param string $date_start
 * @param string $date_end
 * @return array
 */
function get_therapist_availability($therapist_id, $date_start = null, $date_end = null) {
    $db = getDBConnection();
    
    try {
        if ($date_start && $date_end) {
            $stmt = $db->prepare("SELECT * FROM availability WHERE therapist_id = ? AND date BETWEEN ? AND ? ORDER BY date, start_time");
            $stmt->execute([$therapist_id, $date_start, $date_end]);
        } else {
            $stmt = $db->prepare("SELECT * FROM availability WHERE therapist_id = ? ORDER BY date, start_time");
            $stmt->execute([$therapist_id]);
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get availability error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Delete availability
 * @param int $availability_id
 * @return array
 */
function delete_availability($availability_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("DELETE FROM availability WHERE availability_id = ?");
        $stmt->execute([$availability_id]);
        
        return ['success' => true, 'message' => 'Availability deleted successfully'];
    } catch (PDOException $e) {
        error_log('Delete availability error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete availability. Please try again.'];
    }
}
?>

