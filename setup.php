<?php
/**
 * Database Setup Check Page
 * Helps verify database configuration and setup
 */
require_once 'config/database.php';

$errors = [];
$success = [];

// Check MySQL connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $success[] = "MySQL server connection: OK";
} catch (PDOException $e) {
    $errors[] = "MySQL server connection: FAILED - " . $e->getMessage();
    $errors[] = "Please ensure MySQL is running in XAMPP Control Panel";
}

// Check database exists
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        $success[] = "Database '" . DB_NAME . "' exists: OK";
        
        // Check if tables exist
        $pdo_db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $stmt = $pdo_db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) >= 7) {
            $success[] = "Database tables found: " . count($tables) . " tables";
        } else {
            $errors[] = "Database exists but tables are missing. Expected 7 tables, found " . count($tables);
            $errors[] = "Please import database/schema.sql";
        }
    } else {
        $errors[] = "Database '" . DB_NAME . "' does not exist";
        $errors[] = "Please import database/schema.sql to create the database";
    }
} catch (PDOException $e) {
    $errors[] = "Error checking database: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Check - Wellness Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-database"></i> Database Setup Check</h4>
            </div>
            <div class="card-body">
                <h5>Configuration</h5>
                <table class="table table-sm">
                    <tr>
                        <th>Host:</th>
                        <td><?php echo DB_HOST; ?></td>
                    </tr>
                    <tr>
                        <th>User:</th>
                        <td><?php echo DB_USER; ?></td>
                    </tr>
                    <tr>
                        <th>Database:</th>
                        <td><?php echo DB_NAME; ?></td>
                    </tr>
                </table>

                <hr>

                <h5>Status Check</h5>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <ul class="mb-0">
                            <?php foreach ($success as $msg): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $msg): ?>
                                <li><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($errors)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>All checks passed!</strong> Your database is configured correctly.
                    </div>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Homepage
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-info-circle"></i> Setup Instructions:</h6>
                        <ol>
                            <li>Ensure MySQL is running in XAMPP Control Panel</li>
                            <li>Open phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
                            <li>Click "Import" tab</li>
                            <li>Select the file: <code>database/schema.sql</code></li>
                            <li>Click "Go" to import</li>
                            <li>Then import: <code>database/sample_data.sql</code> (optional, for test data)</li>
                            <li>Refresh this page to verify setup</li>
                        </ol>
                    </div>
                    <a href="setup.php" class="btn btn-primary">
                        <i class="fas fa-sync"></i> Refresh Check
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

