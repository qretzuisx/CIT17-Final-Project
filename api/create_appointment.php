<?php
/**
 * API: Create appointment
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/appointments.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $therapist_id = isset($data['therapist_id']) ? (int)$data['therapist_id'] : 0;
    $service_id = isset($data['service_id']) ? (int)$data['service_id'] : 0;
    $appointment_date = isset($data['appointment_date']) ? sanitize($data['appointment_date']) : '';
    $start_time = isset($data['start_time']) ? sanitize($data['start_time']) : '';
    
    if ($therapist_id && $service_id && $appointment_date && $start_time) {
        $result = create_appointment($_SESSION['user_id'], $therapist_id, $service_id, $appointment_date, $start_time);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

