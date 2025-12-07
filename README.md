# Wellness Center Booking & Reservation System

A full-stack web application for managing appointments, payments, and user interactions for a wellness/therapy center.

## Features

- **Complete Booking System**: Multi-step booking process with service selection, therapist selection, date/time selection, and payment
- **User Management**: Registration, login, profile management with role-based access (customer, therapist, admin)
- **Service Management**: Browse, filter, and search wellness services
- **Appointment Management**: View, cancel, and manage appointments
- **Payment Processing**: Support for cash, credit card, and PayPal payments
- **Review System**: Submit and view reviews for completed appointments
- **Admin Dashboard**: Comprehensive admin panel for managing bookings, services, therapists, and payments
- **Responsive Design**: Mobile-friendly interface built with Bootstrap 5
- **Real-time Features**: Dynamic availability checking, form validation, and AJAX-powered interactions

## Technical Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **AJAX**: Fetch API for dynamic content loading

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### Step 1: Clone or Download

Download the project files to your web server directory:
```
C:\xampp\htdocs\CIT17-Final-Project
```

### Step 2: Database Setup

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the database schema:
   - Click "Import" tab
   - Select `database/schema.sql`
   - Click "Go"
4. Import sample data:
   - Click "Import" tab
   - Select `database/sample_data.sql`
   - Click "Go"

Alternatively, run the SQL files from command line:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p wellness_booking_system < database/sample_data.sql
```

### Step 3: Configuration

1. Open `config/database.php`
2. Update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wellness_booking_system');
```

3. Open `config/config.php`
4. Update the base URL if needed:
```php
define('SITE_URL', 'http://localhost/CIT17-Final-Project');
```

### Step 4: Access the Application

1. Start Apache in XAMPP
2. Open your browser and navigate to:
```
http://localhost/CIT17-Final-Project
```

## Default Accounts

### Admin Account
- **Email**: admin@wellness.com
- **Password**: password123
- **Role**: Admin

### Customer Account
- **Email**: john@example.com
- **Password**: password123
- **Role**: Customer

### Therapist Account
- **Email**: sarah@wellness.com
- **Password**: password123
- **Role**: Therapist

## Project Structure

```
CIT17-Final-Project/
├── api/                    # API endpoints for AJAX
│   ├── get_services.php
│   ├── get_therapists.php
│   ├── get_availability.php
│   ├── apply_promo.php
│   ├── create_appointment.php
│   ├── create_payment.php
│   └── get_reviews.php
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet with design system
│   ├── js/
│   │   └── main.js        # JavaScript utilities
│   └── images/
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database connection
├── database/
│   ├── schema.sql         # Database schema
│   └── sample_data.sql    # Sample data
├── includes/
│   ├── functions/         # Backend functions
│   │   ├── users.php
│   │   ├── services.php
│   │   ├── appointments.php
│   │   ├── payments.php
│   │   ├── reviews.php
│   │   └── availability.php
│   ├── header.php         # Common header
│   └── footer.php         # Common footer
├── pages/
│   ├── services.php       # Services listing page
│   ├── booking.php        # Booking page
│   ├── dashboard.php      # User dashboard
│   ├── admin.php          # Admin dashboard
│   └── profile.php        # Profile handler
├── index.php              # Home page
└── README.md              # This file
```

## Database Schema

The system uses 7 main tables:

1. **users**: User accounts (customers, therapists, admins)
2. **services**: Wellness services offered
3. **appointments**: Booking records
4. **payments**: Payment transactions
5. **availability**: Therapist availability schedules
6. **reviews**: Customer reviews and ratings
7. **promotions**: Promotional codes and discounts

## Key Features Implementation

### Booking Workflow
1. User selects a service
2. User selects a therapist
3. User selects date and available time slot
4. User applies promo code (optional)
5. User selects payment method
6. System creates appointment and payment record

### Authentication
- Session-based authentication
- Password hashing using PHP's `password_hash()`
- Role-based access control (customer, therapist, admin)

### AJAX Features
- Real-time availability checking
- Dynamic service loading
- Promo code validation
- Form submission without page reload

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Responsive Breakpoints

- Desktop: 1200px+
- Tablet: 768px - 1199px
- Mobile: < 768px

## Design System

### Colors
- Primary: #2E8B57 (Seagreen)
- Secondary: #87CEEB (Sky Blue)
- Accent: #FFB6C1 (Light Pink)
- Text: #2C3E50 (Dark Blue)

### Typography
- Font Family: Roboto (Google Fonts)
- Base Size: 1rem
- Line Height: 1.6

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Check if database exists: `wellness_booking_system`

### Session Issues
- Check PHP session directory permissions
- Ensure `session_start()` is called before any output

### AJAX Not Working
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Ensure user is logged in for protected endpoints

## Development Notes

- All passwords in sample data are hashed with bcrypt
- Default password for all test accounts: `password123`
- Promo codes in sample data are active for 30-60 days from current date

## License

This project is developed for educational purposes as part of CIT17 Final Project.

## Support

For issues or questions, please contact the development team.

---

**Version**: 1.0.0  
**Last Updated**: 2024

