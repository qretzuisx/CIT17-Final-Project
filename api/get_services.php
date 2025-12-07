<?php
/**
 * API: Get all services
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/services.php';

$services = get_all_services();
echo json_encode(['success' => true, 'data' => $services]);
?>

