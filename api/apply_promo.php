<?php
/**
 * API: Apply promo code
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/payments.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $promo_code = isset($data['promo_code']) ? sanitize($data['promo_code']) : '';
    $total_amount = isset($data['total_amount']) ? (float)$data['total_amount'] : 0;
    
    if ($promo_code && $total_amount > 0) {
        $result = apply_promo_code($promo_code, $total_amount);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

