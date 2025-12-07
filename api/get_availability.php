<?php
/**
 * API: Get available time slots
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/appointments.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $therapist_id = isset($_GET['therapist_id']) ? (int)$_GET['therapist_id'] : 0;
    $date = isset($_GET['date']) ? sanitize($_GET['date']) : '';
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
    
    if ($therapist_id && $date && $service_id) {
        $slots = get_available_time_slots($therapist_id, $date, $service_id);
        echo json_encode(['success' => true, 'data' => $slots]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

