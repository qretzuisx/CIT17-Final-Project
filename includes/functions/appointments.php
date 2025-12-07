<?php
/**
 * Appointment Management Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/services.php';

/**
 * Get available time slots for a therapist on a specific date
 * @param int $therapist_id
 * @param string $date
 * @param int $service_id
 * @return array
 */
function get_available_time_slots($therapist_id, $date, $service_id) {
    $db = getDBConnection();
    
    try {
        // Get service duration
        $service = get_service_by_id($service_id);
        if (!$service) {
            return [];
        }
        $duration = $service['duration'];
        
        // Get therapist availability for the date
        $stmt = $db->prepare("SELECT start_time, end_time FROM availability WHERE therapist_id = ? AND date = ?");
        $stmt->execute([$therapist_id, $date]);
        $availability = $stmt->fetchAll();
        
        if (empty($availability)) {
            return [];
        }
        
        // Get existing appointments for the date
        $stmt = $db->prepare("SELECT start_time, end_time FROM appointments WHERE therapist_id = ? AND appointment_date = ? AND status != 'canceled'");
        $stmt->execute([$therapist_id, $date]);
        $booked_slots = $stmt->fetchAll();
        
        // Generate available slots
        $available_slots = [];
        $slot_interval = 15; // 15-minute intervals
        
        foreach ($availability as $avail) {
            $start = new DateTime($date . ' ' . $avail['start_time']);
            $end = new DateTime($date . ' ' . $avail['end_time']);
            $current = clone $start;
            
            while ($current < $end) {
                $slot_end = clone $current;
                $slot_end->modify("+{$duration} minutes");
                
                // Check if slot fits within availability
                if ($slot_end <= $end) {
                    $slot_start_str = $current->format('H:i:s');
                    $slot_end_str = $slot_end->format('H:i:s');
                    
                    // Check if slot conflicts with booked appointments
                    $is_available = true;
                    foreach ($booked_slots as $booked) {
                        $booked_start = new DateTime($date . ' ' . $booked['start_time']);
                        $booked_end = new DateTime($date . ' ' . $booked['end_time']);
                        
                        if (($current < $booked_end) && ($slot_end > $booked_start)) {
                            $is_available = false;
                            break;
                        }
                    }
                    
                    if ($is_available) {
                        $available_slots[] = [
                            'start_time' => $slot_start_str,
                            'end_time' => $slot_end_str,
                            'display' => $current->format('h:i A') . ' - ' . $slot_end->format('h:i A')
                        ];
                    }
                }
                
                $current->modify("+{$slot_interval} minutes");
            }
        }
        
        return $available_slots;
    } catch (Exception $e) {
        error_log('Get available slots error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Create appointment
 * @param int $user_id
 * @param int $therapist_id
 * @param int $service_id
 * @param string $appointment_date
 * @param string $start_time
 * @return array
 */
function create_appointment($user_id, $therapist_id, $service_id, $appointment_date, $start_time) {
    $db = getDBConnection();
    
    try {
        // Get service details
        $service = get_service_by_id($service_id);
        if (!$service) {
            return ['success' => false, 'message' => 'Service not found'];
        }
        
        // Calculate end time
        $start_datetime = new DateTime($appointment_date . ' ' . $start_time);
        $end_datetime = clone $start_datetime;
        $end_datetime->modify("+{$service['duration']} minutes");
        $end_time = $end_datetime->format('H:i:s');
        
        // Validate availability
        $available_slots = get_available_time_slots($therapist_id, $appointment_date, $service_id);
        $is_available = false;
        foreach ($available_slots as $slot) {
            if ($slot['start_time'] === $start_time) {
                $is_available = true;
                break;
            }
        }
        
        if (!$is_available) {
            return ['success' => false, 'message' => 'Selected time slot is not available'];
        }
        
        // Create appointment
        $stmt = $db->prepare("INSERT INTO appointments (user_id, therapist_id, service_id, appointment_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $therapist_id, $service_id, $appointment_date, $start_time, $end_time]);
        
        $appointment_id = $db->lastInsertId();
        
        return ['success' => true, 'message' => 'Appointment created successfully', 'appointment_id' => $appointment_id];
    } catch (PDOException $e) {
        error_log('Create appointment error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Appointment creation failed. Please try again.'];
    }
}

/**
 * Update appointment status
 * @param int $appointment_id
 * @param string $new_status
 * @return array
 */
function update_appointment_status($appointment_id, $new_status) {
    $db = getDBConnection();
    
    $allowed_statuses = ['pending', 'confirmed', 'completed', 'canceled'];
    if (!in_array($new_status, $allowed_statuses)) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    try {
        $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt->execute([$new_status, $appointment_id]);
        
        return ['success' => true, 'message' => 'Appointment status updated successfully'];
    } catch (PDOException $e) {
        error_log('Update appointment status error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Update failed. Please try again.'];
    }
}

/**
 * Get user appointments
 * @param int $user_id
 * @param string $status Optional filter by status
 * @return array
 */
function get_user_appointments($user_id, $status = null) {
    $db = getDBConnection();
    
    try {
        if ($status) {
            $stmt = $db->prepare("
                SELECT a.*, s.service_name, s.price, s.duration, 
                       u.full_name as therapist_name, u.email as therapist_email
                FROM appointments a
                JOIN services s ON a.service_id = s.service_id
                JOIN users u ON a.therapist_id = u.user_id
                WHERE a.user_id = ? AND a.status = ?
                ORDER BY a.appointment_date DESC, a.start_time DESC
            ");
            $stmt->execute([$user_id, $status]);
        } else {
            $stmt = $db->prepare("
                SELECT a.*, s.service_name, s.price, s.duration, 
                       u.full_name as therapist_name, u.email as therapist_email
                FROM appointments a
                JOIN services s ON a.service_id = s.service_id
                JOIN users u ON a.therapist_id = u.user_id
                WHERE a.user_id = ?
                ORDER BY a.appointment_date DESC, a.start_time DESC
            ");
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get user appointments error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get appointment by ID
 * @param int $appointment_id
 * @return array|null
 */
function get_appointment_by_id($appointment_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT a.*, s.service_name, s.price, s.duration, 
                   u.full_name as customer_name, u.email as customer_email,
                   t.full_name as therapist_name, t.email as therapist_email
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            JOIN users u ON a.user_id = u.user_id
            JOIN users t ON a.therapist_id = t.user_id
            WHERE a.appointment_id = ?
        ");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Get appointment error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all appointments (admin)
 * @param array $filters
 * @return array
 */
function get_all_appointments($filters = []) {
    $db = getDBConnection();
    
    try {
        $where = [];
        $params = [];
        
        if (isset($filters['status']) && $filters['status']) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $where[] = "a.appointment_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $where[] = "a.appointment_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "
            SELECT a.*, s.service_name, s.price,
                   u.full_name as customer_name, u.email as customer_email,
                   t.full_name as therapist_name
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            JOIN users u ON a.user_id = u.user_id
            JOIN users t ON a.therapist_id = t.user_id
            $where_clause
            ORDER BY a.appointment_date DESC, a.start_time DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get all appointments error: ' . $e->getMessage());
        return [];
    }
}
?>

