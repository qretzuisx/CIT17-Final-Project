<?php
/**
 * Profile Page - Handle profile updates
 */
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

require_once __DIR__ . '/../includes/functions/users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $result = change_password(
            $_SESSION['user_id'],
            $_POST['current_password'],
            $_POST['new_password']
        );
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    } else {
        $data = [
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone_number' => sanitize($_POST['phone_number'] ?? '')
        ];
        $result = update_user_profile($_SESSION['user_id'], $data);
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    }
}

redirect('dashboard.php');
?>
