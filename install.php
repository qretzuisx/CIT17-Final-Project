<?php
/**
 * Automatic Database Installation Script
 * This script will create the database and import the schema automatically
 */
require_once 'config/database.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$error = '';

// Step 1: Test connection without database
// Step 2: Create database
// Step 3: Import schema
// Step 4: Import sample data (optional)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create_database') {
            // Connect without database
            $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $message = "Database '" . DB_NAME . "' created successfully!";
            $step = 2;
        } elseif ($_POST['action'] === 'import_schema') {
            // Read and execute schema file
            $schema_file = __DIR__ . '/database/schema.sql';
            if (!file_exists($schema_file)) {
                throw new Exception("Schema file not found: $schema_file");
            }
            
            $sql = file_get_contents($schema_file);
            // Remove CREATE DATABASE and USE statements if they exist
            $sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
            $sql = preg_replace('/USE[^;]+;/i', '', $sql);
            
            // Connect to the database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Execute SQL statements
            $pdo->exec("USE `" . DB_NAME . "`");
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore some common errors
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            $message = "Database schema imported successfully!";
            $step = 3;
        } elseif ($_POST['action'] === 'import_sample_data') {
            // Read and execute sample data file
            $data_file = __DIR__ . '/database/sample_data.sql';
            if (!file_exists($data_file)) {
                throw new Exception("Sample data file not found: $data_file");
            }
            
            $sql = file_get_contents($data_file);
            // Remove USE statement if it exists
            $sql = preg_replace('/USE[^;]+;/i', '', $sql);
            
            // Connect to the database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Execute SQL statements
            $pdo->exec("USE `" . DB_NAME . "`");
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore duplicate key errors
                        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                            // Don't throw, just log
                            error_log("Sample data import warning: " . $e->getMessage());
                        }
                    }
                }
            }
            
            $message = "Sample data imported successfully!";
            $step = 4;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Check current status
$db_exists = false;
$tables_exist = false;
$data_exists = false;

try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $db_exists = $stmt->rowCount() > 0;
    
    if ($db_exists) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $tables_exist = count($tables) >= 7;
        
        if ($tables_exist) {
            // Check if data exists
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            $data_exists = $result['count'] > 0;
        }
    }
} catch (PDOException $e) {
    $error = "Cannot connect to MySQL: " . $e->getMessage();
}

// Determine current step based on status
if (!$db_exists) {
    $step = 1;
} elseif (!$tables_exist) {
    $step = 2;
} elseif (!$data_exists) {
    $step = 3;
} else {
    $step = 4;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Installation - Wellness Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .install-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .step-card {
            margin-bottom: 20px;
            opacity: 0.6;
            transition: opacity 0.3s;
        }
        .step-card.active {
            opacity: 1;
            border: 2px solid #2E8B57;
        }
        .step-card.completed {
            opacity: 1;
            border: 1px solid #28A745;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c757d;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .step-card.completed .step-number {
            background: #28A745;
        }
        .step-card.active .step-number {
            background: #2E8B57;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-database"></i> Database Installation Wizard</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <h5>Installation Steps</h5>

                <!-- Step 1: Create Database -->
                <div class="card step-card <?php echo $step >= 1 && !$db_exists ? 'active' : ($db_exists ? 'completed' : ''); ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-number">1</span>
                            <h6 class="mb-0">Create Database</h6>
                        </div>
                        <p class="text-muted">Create the '<?php echo DB_NAME; ?>' database</p>
                        <?php if ($db_exists): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check"></i> Database exists
                            </div>
                        <?php elseif ($step == 1): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="create_database">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Database
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Step 2: Import Schema -->
                <div class="card step-card <?php echo $step >= 2 && !$tables_exist ? 'active' : ($tables_exist ? 'completed' : ''); ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-number">2</span>
                            <h6 class="mb-0">Import Database Schema</h6>
                        </div>
                        <p class="text-muted">Create all required tables (Users, Services, Appointments, etc.)</p>
                        <?php if ($tables_exist): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check"></i> Schema imported successfully
                            </div>
                        <?php elseif ($db_exists && $step == 2): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="import_schema">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Import Schema
                                </button>
                            </form>
                        <?php elseif (!$db_exists): ?>
                            <p class="text-muted"><small>Complete step 1 first</small></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Step 3: Import Sample Data -->
                <div class="card step-card <?php echo $step >= 3 && !$data_exists ? 'active' : ($data_exists ? 'completed' : ''); ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-number">3</span>
                            <h6 class="mb-0">Import Sample Data (Optional)</h6>
                        </div>
                        <p class="text-muted">Import test data including sample users, services, and appointments</p>
                        <?php if ($data_exists): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check"></i> Sample data imported
                            </div>
                        <?php elseif ($tables_exist && $step == 3): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="import_sample_data">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Import Sample Data
                                </button>
                            </form>
                        <?php elseif (!$tables_exist): ?>
                            <p class="text-muted"><small>Complete step 2 first</small></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Step 4: Complete -->
                <?php if ($step == 4): ?>
                    <div class="card step-card completed">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Installation Complete!</h5>
                            <p class="text-muted">Your database is ready to use.</p>
                            <div class="mt-4">
                                <a href="index.php" class="btn btn-success btn-lg me-2">
                                    <i class="fas fa-home"></i> Go to Homepage
                                </a>
                                <a href="setup.php" class="btn btn-outline-primary">
                                    <i class="fas fa-check"></i> Verify Installation
                                </a>
                            </div>
                            <hr>
                            <div class="alert alert-info mt-3">
                                <strong>Default Login Credentials:</strong><br>
                                <strong>Admin:</strong> admin@wellness.com / password123<br>
                                <strong>Customer:</strong> john@example.com / password123<br>
                                <strong>Therapist:</strong> sarah@wellness.com / password123
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

