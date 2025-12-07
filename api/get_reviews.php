<?php
/**
 * API: Get all reviews
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/reviews.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$reviews = get_all_reviews($limit);
echo json_encode(['success' => true, 'data' => $reviews]);
?>

