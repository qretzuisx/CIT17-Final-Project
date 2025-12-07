<?php
/**
 * API: Create payment
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/payments.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $appointment_id = isset($data['appointment_id']) ? (int)$data['appointment_id'] : 0;
    $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
    $payment_method = isset($data['payment_method']) ? sanitize($data['payment_method']) : '';
    
    if ($appointment_id && $amount > 0 && $payment_method) {
        $result = create_payment_record($appointment_id, $amount, $payment_method);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

