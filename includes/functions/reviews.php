<?php
/**
 * Review Management Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Submit review
 * @param int $appointment_id
 * @param int $user_id
 * @param int $rating
 * @param string $comment
 * @return array
 */
function submit_review($appointment_id, $user_id, $rating, $comment) {
    $db = getDBConnection();
    
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
    }
    
    try {
        // Validate that the user can review this appointment
        $stmt = $db->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ? AND status = 'completed'");
        $stmt->execute([$appointment_id, $user_id]);
        $appointment = $stmt->fetch();
        
        if (!$appointment) {
            return ['success' => false, 'message' => 'Appointment not found or cannot be reviewed'];
        }
        
        // Check if review already exists
        $stmt = $db->prepare("SELECT review_id FROM reviews WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Review already submitted for this appointment'];
        }
        
        // Create review
        $stmt = $db->prepare("INSERT INTO reviews (appointment_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$appointment_id, $user_id, $rating, $comment]);
        
        return ['success' => true, 'message' => 'Review submitted successfully', 'review_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Submit review error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Review submission failed. Please try again.'];
    }
}

/**
 * Get review by appointment ID
 * @param int $appointment_id
 * @return array|null
 */
function get_review_by_appointment($appointment_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT r.*, u.full_name as reviewer_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.appointment_id = ?
        ");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Get review error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all reviews
 * @param int $limit
 * @return array
 */
function get_all_reviews($limit = null) {
    $db = getDBConnection();
    
    try {
        $sql = "
            SELECT r.*, u.full_name as reviewer_name, s.service_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN appointments a ON r.appointment_id = a.appointment_id
            JOIN services s ON a.service_id = s.service_id
            ORDER BY r.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get all reviews error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get average rating for a therapist
 * @param int $therapist_id
 * @return float
 */
function get_therapist_rating($therapist_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_reviews
            FROM reviews r
            JOIN appointments a ON r.appointment_id = a.appointment_id
            WHERE a.therapist_id = ?
        ");
        $stmt->execute([$therapist_id]);
        $result = $stmt->fetch();
        
        return [
            'avg_rating' => round($result['avg_rating'], 2),
            'total_reviews' => $result['total_reviews']
        ];
    } catch (PDOException $e) {
        error_log('Get therapist rating error: ' . $e->getMessage());
        return ['avg_rating' => 0, 'total_reviews' => 0];
    }
}
?>

