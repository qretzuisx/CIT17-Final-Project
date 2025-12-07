<?php
/**
 * Payment Processing Functions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Create payment record
 * @param int $appointment_id
 * @param float $amount
 * @param string $payment_method
 * @return array
 */
function create_payment_record($appointment_id, $amount, $payment_method) {
    $db = getDBConnection();
    
    $allowed_methods = ['cash', 'credit_card', 'paypal'];
    if (!in_array($payment_method, $allowed_methods)) {
        return ['success' => false, 'message' => 'Invalid payment method'];
    }
    
    try {
        // Generate transaction ID
        $transaction_id = 'TXN' . date('YmdHis') . rand(1000, 9999);
        
        // Create payment record
        $stmt = $db->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, 'paid', ?)");
        $stmt->execute([$appointment_id, $amount, $payment_method, $transaction_id]);
        
        // Update appointment status to confirmed
        $stmt = $db->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        
        $payment_id = $db->lastInsertId();
        
        return ['success' => true, 'message' => 'Payment recorded successfully', 'payment_id' => $payment_id, 'transaction_id' => $transaction_id];
    } catch (PDOException $e) {
        error_log('Create payment error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Payment processing failed. Please try again.'];
    }
}

/**
 * Apply promo code
 * @param string $promo_code
 * @param float $total_amount
 * @return array
 */
function apply_promo_code($promo_code, $total_amount) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM promotions WHERE promo_code = ? AND start_date <= CURDATE() AND end_date >= CURDATE()");
        $stmt->execute([$promo_code]);
        $promo = $stmt->fetch();
        
        if (!$promo) {
            return ['success' => false, 'message' => 'Invalid or expired promo code'];
        }
        
        $discount = ($total_amount * $promo['discount_percent']) / 100;
        $discounted_amount = $total_amount - $discount;
        
        return [
            'success' => true,
            'message' => 'Promo code applied successfully',
            'discount_percent' => $promo['discount_percent'],
            'discount_amount' => $discount,
            'original_amount' => $total_amount,
            'final_amount' => $discounted_amount
        ];
    } catch (PDOException $e) {
        error_log('Apply promo code error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to apply promo code. Please try again.'];
    }
}

/**
 * Get payment by appointment ID
 * @param int $appointment_id
 * @return array|null
 */
function get_payment_by_appointment($appointment_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("SELECT * FROM payments WHERE appointment_id = ? ORDER BY payment_date DESC LIMIT 1");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Get payment error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all payments (admin)
 * @param array $filters
 * @return array
 */
function get_all_payments($filters = []) {
    $db = getDBConnection();
    
    try {
        $where = [];
        $params = [];
        
        if (isset($filters['status']) && $filters['status']) {
            $where[] = "p.payment_status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $where[] = "p.payment_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $where[] = "p.payment_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "
            SELECT p.*, a.appointment_date, s.service_name,
                   u.full_name as customer_name
            FROM payments p
            JOIN appointments a ON p.appointment_id = a.appointment_id
            JOIN services s ON a.service_id = s.service_id
            JOIN users u ON a.user_id = u.user_id
            $where_clause
            ORDER BY p.payment_date DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Get all payments error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Process refund
 * @param int $payment_id
 * @return array
 */
function process_refund($payment_id) {
    $db = getDBConnection();
    
    try {
        $stmt = $db->prepare("UPDATE payments SET payment_status = 'refunded' WHERE payment_id = ?");
        $stmt->execute([$payment_id]);
        
        // Update appointment status to canceled
        $stmt = $db->prepare("UPDATE appointments a JOIN payments p ON a.appointment_id = p.appointment_id SET a.status = 'canceled' WHERE p.payment_id = ?");
        $stmt->execute([$payment_id]);
        
        return ['success' => true, 'message' => 'Refund processed successfully'];
    } catch (PDOException $e) {
        error_log('Process refund error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Refund processing failed. Please try again.'];
    }
}
?>

