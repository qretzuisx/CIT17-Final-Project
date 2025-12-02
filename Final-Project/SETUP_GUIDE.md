# Car Wash Appointment System - Setup Guide

## Complete Installation Instructions

Follow these steps carefully to set up the Car Wash Appointment System on your local machine.

---

## System Requirements

### Minimum Requirements
- **Web Server:** Apache 2.4+ or Nginx
- **PHP:** Version 7.4 or higher
- **MySQL:** Version 5.7 or higher
- **Browser:** Modern web browser (Chrome, Firefox, Edge, Safari)
- **Disk Space:** At least 100MB free space

### Recommended Software Stack
- **XAMPP** (Windows/Mac/Linux) - Includes Apache, PHP, and MySQL
- **WAMP** (Windows)
- **MAMP** (Mac)
- **LAMP** (Linux)

---

## Installation Steps

### Step 1: Install XAMPP (or equivalent)

1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer
3. Select components: Apache, MySQL, PHP, phpMyAdmin
4. Complete the installation
5. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Download and Extract Project Files

1. Download the complete project files
2. Extract the `UCCITCSWEB234079294` folder
3. Copy the folder to your web server directory:
   - **XAMPP:** `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux)
   - **WAMP:** `C:\wamp\www\`
   - **MAMP:** `/Applications/MAMP/htdocs/`

### Step 3: Create Database

#### Option A: Using phpMyAdmin (Recommended)

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click on "New" in the left sidebar
3. Enter database name: `carwash_system`
4. Select collation: `utf8mb4_unicode_ci`
5. Click "Create"

#### Option B: Using MySQL Command Line

```sql
CREATE DATABASE carwash_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Import Database Schema

#### Using phpMyAdmin:

1. Click on the `carwash_system` database in the left sidebar
2. Click on the "Import" tab
3. Click "Choose File"
4. Navigate to `UCCITCSWEB234079294/Final-Project/database/schema.sql`
5. Click "Go" at the bottom
6. Wait for success message

#### Using MySQL Command Line:

```bash
mysql -u root -p carwash_system < database/schema.sql
```

### Step 5: Import Sample Data (Optional)

This step is optional but recommended for testing.

#### Using phpMyAdmin:

1. In the `carwash_system` database, click "Import" tab
2. Choose file: `UCCITCSWEB234079294/Final-Project/database/sample_data.sql`
3. Click "Go"

#### Using MySQL Command Line:

```bash
mysql -u root -p carwash_system < database/sample_data.sql
```

### Step 6: Configure Database Connection

1. Open `UCCITCSWEB234079294/Final-Project/config/database.php` in a text editor
2. Verify/Update the database credentials:

```php
define('DB_HOST', 'localhost');    // Usually 'localhost'
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password (empty for XAMPP default)
define('DB_NAME', 'carwash_system'); // Database name
```

3. Save the file

### Step 7: Set File Permissions (Linux/Mac only)

```bash
chmod -R 755 UCCITCSWEB234079294
chmod -R 777 UCCITCSWEB234079294/Final-Project/uploads
```

### Step 8: Test the Installation

1. Open your web browser
2. Navigate to: `http://localhost/UCCITCSWEB234079294/Final-Project/Final-Project/`
3. You should see the home page

---

## Accessing the System

### Main URL
```
http://localhost/UCCITCSWEB234079294/Final-Project/Final-Project/
```

### Demo Login Credentials

#### Admin Account
- **Email:** admin@carwash.com
- **Password:** admin123

#### Customer Account
- **Email:** john@example.com
- **Password:** password123

#### Washer Account
- **Email:** mike@carwash.com
- **Password:** password123

---

## Testing the System

### Test User Registration

1. Go to `http://localhost/UCCITCSWEB234079294/Final-Project/Final-Project/auth/register.php`
2. Fill in all required fields
3. Click "Register"
4. Login with your new credentials

### Test Booking Flow

1. Login as a customer
2. Go to Profile and add a vehicle
3. Browse services
4. Book an appointment
5. Complete payment
6. View in dashboard

---

## Troubleshooting

### Issue 1: "Database connection failed"

**Solutions:**
- Check if MySQL is running in XAMPP Control Panel
- Verify database credentials in `config/database.php`
- Ensure database `carwash_system` exists
- Check MySQL port (default: 3306)

### Issue 2: "Page not found" or blank page

**Solutions:**
- Check if Apache is running
- Verify the URL: `http://localhost/UCCITCSWEB234079294/Final-Project/Final-Project/`
- Check for PHP errors: Enable `display_errors` in `php.ini`
- Clear browser cache

### Issue 3: CSS/JavaScript not loading

**Solutions:**
- Check file paths in `config/config.php`
- Verify `BASE_URL` constant
- Check browser console for errors (F12)
- Clear browser cache

### Issue 4: Session errors

**Solutions:**
- Check PHP session configuration
- Verify session save path has write permissions
- Clear browser cookies
- Restart Apache

### Issue 5: Cannot import database

**Solutions:**
- Check file upload limits in `php.ini`:
  - `upload_max_filesize = 64M`
  - `post_max_size = 64M`
- Use MySQL command line instead
- Check SQL file for syntax errors

---

## Database Schema Verification

After importing, verify these tables exist:

```sql
USE carwash_system;
SHOW TABLES;
```

You should see:
- appointments
- payments
- schedules
- services
- users
- vehicles
- washers

---

## Configuration Options

### Change Site URL (if not using localhost)

Edit `config/config.php`:

```php
define('SITE_URL', 'http://your-domain.com/UCCITCSWEB234079294');
```

### Enable/Disable Error Display

For development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

For production:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### Change Session Timeout

Edit `config/config.php`:

```php
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
```

---

## Security Checklist

Before deploying to production:

- [ ] Change all default passwords
- [ ] Update database credentials
- [ ] Disable error display
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Remove sample data
- [ ] Update SITE_URL configuration
- [ ] Test all security features
- [ ] Enable database backups
- [ ] Review error logs

---

## Backup Instructions

### Database Backup

#### Using phpMyAdmin:
1. Select `carwash_system` database
2. Click "Export" tab
3. Choose "Quick" export method
4. Select "SQL" format
5. Click "Go"

#### Using MySQL Command Line:
```bash
mysqldump -u root -p carwash_system > backup.sql
```

### File Backup

Copy the entire project folder:
```bash
cp -r UCCITCSWEB234079294 UCCITCSWEB234079294_backup
```

---

## Updating the System

1. Backup database and files
2. Replace files with new version
3. Run any update scripts if provided
4. Clear cache and sessions
5. Test thoroughly

---

## Uninstallation

### Remove Database
```sql
DROP DATABASE carwash_system;
```

### Remove Files
Delete the `UCCITCSWEB234079294` folder from your web server directory.

---

## Additional Resources

### Documentation
- `README.md` - Project overview and features
- `Final-Project.md` - Project requirements and rubrics
- `database/schema.sql` - Complete database structure

### Support Files
- `database/sample_data.sql` - Test data
- `assets/css/style.css` - Custom styles
- `assets/js/main.js` - JavaScript functions

### External Documentation
- PHP Manual: https://www.php.net/manual/
- MySQL Documentation: https://dev.mysql.com/doc/
- Bootstrap 5: https://getbootstrap.com/docs/5.3/

---

## Getting Help

If you encounter issues:

1. Check this setup guide thoroughly
2. Review error messages in browser console (F12)
3. Check Apache and PHP error logs
4. Verify all prerequisites are met
5. Check file permissions
6. Review configuration files

---

## Development Tips

### Recommended Tools
- **Code Editor:** Visual Studio Code, Sublime Text, PHPStorm
- **Database Tool:** phpMyAdmin, MySQL Workbench
- **Browser DevTools:** Chrome DevTools, Firefox Developer Tools
- **Version Control:** Git

### Best Practices
- Test on localhost before deploying
- Keep backups regularly
- Use prepared statements for SQL
- Validate all user inputs
- Keep software updated
- Follow PHP coding standards

---

## System Architecture

```
┌─────────────────────────────────────────┐
│           User Interface (HTML/CSS/JS)   │
├─────────────────────────────────────────┤
│           PHP Backend Layer              │
│  - Authentication                        │
│  - Business Logic                        │
│  - Data Validation                       │
├─────────────────────────────────────────┤
│           Database Layer (MySQL)         │
│  - 7 Tables with relationships           │
│  - Constraints and indexes               │
└─────────────────────────────────────────┘
```

---

## Performance Optimization

### PHP Configuration
- Enable OPcache
- Increase memory limit if needed
- Optimize session handling

### Database Optimization
- Regular maintenance
- Index optimization
- Query optimization

### Web Server
- Enable compression
- Cache static assets
- Use CDN for libraries

---

## Quick Reference Commands

### Start Services (XAMPP)
```bash
sudo /opt/lampp/lampp start
```

### Stop Services (XAMPP)
```bash
sudo /opt/lampp/lampp stop
```

### Check MySQL Status
```bash
mysql -u root -p -e "SELECT VERSION();"
```

### Clear PHP Sessions
```bash
rm -rf /tmp/sess_*
```
