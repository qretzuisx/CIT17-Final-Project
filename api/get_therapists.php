<?php
/**
 * API: Get all therapists
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions/users.php';

$therapists = get_all_therapists();
echo json_encode(['success' => true, 'data' => $therapists]);
?>

