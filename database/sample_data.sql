-- Sample Data for Wellness Center Booking & Reservation System
USE wellness_booking_system;

-- Sample Users (password for all: password123 - hashed with bcrypt)
INSERT INTO users (full_name, email, phone_number, password, role) VALUES
-- Customers
('John Doe', 'john@example.com', '09171234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Jane Smith', 'jane@example.com', '09181234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
-- Therapists
('Sarah Johnson', 'sarah@wellness.com', '09191234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'therapist'),
('Michael Chen', 'michael@wellness.com', '09201234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'therapist'),
-- Admin
('Admin User', 'admin@wellness.com', '09211234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Services
INSERT INTO services (service_name, description, duration, price) VALUES
('Swedish Massage', 'A gentle full-body massage that helps reduce stress and promote relaxation. Perfect for first-time massage clients.', 60, 1500.00),
('Deep Tissue Massage', 'A therapeutic massage technique that targets deeper layers of muscle and connective tissue. Ideal for chronic pain and tension.', 90, 2200.00),
('Hot Stone Therapy', 'A relaxing massage using heated stones to ease muscle tension and improve circulation.', 75, 2500.00),
('Aromatherapy Session', 'A soothing massage using essential oils to enhance physical and emotional well-being.', 60, 1800.00),
('Reflexology', 'A therapeutic foot massage that stimulates pressure points to improve overall health and wellness.', 45, 1200.00),
('Therapeutic Yoga Session', 'One-on-one yoga session tailored to your needs and goals.', 60, 1400.00),
('Meditation & Mindfulness', 'Guided meditation sessions to reduce stress and improve mental clarity.', 45, 1000.00);

-- Sample Availability (for therapists - next 7 days)
INSERT INTO availability (therapist_id, date, start_time, end_time) VALUES
-- Sarah's availability (therapist_id = 3)
(3, CURDATE() + INTERVAL 1 DAY, '09:00:00', '12:00:00'),
(3, CURDATE() + INTERVAL 1 DAY, '13:00:00', '17:00:00'),
(3, CURDATE() + INTERVAL 2 DAY, '09:00:00', '12:00:00'),
(3, CURDATE() + INTERVAL 2 DAY, '13:00:00', '17:00:00'),
(3, CURDATE() + INTERVAL 3 DAY, '10:00:00', '15:00:00'),
-- Michael's availability (therapist_id = 4)
(4, CURDATE() + INTERVAL 1 DAY, '08:00:00', '12:00:00'),
(4, CURDATE() + INTERVAL 1 DAY, '14:00:00', '18:00:00'),
(4, CURDATE() + INTERVAL 2 DAY, '09:00:00', '13:00:00'),
(4, CURDATE() + INTERVAL 2 DAY, '14:00:00', '17:00:00'),
(4, CURDATE() + INTERVAL 3 DAY, '10:00:00', '16:00:00'),
(4, CURDATE() + INTERVAL 4 DAY, '09:00:00', '15:00:00');

-- Sample Appointments
INSERT INTO appointments (user_id, therapist_id, service_id, appointment_date, start_time, end_time, status) VALUES
(1, 3, 1, CURDATE() + INTERVAL 1 DAY, '10:00:00', '11:00:00', 'confirmed'),
(2, 4, 2, CURDATE() + INTERVAL 2 DAY, '10:00:00', '11:30:00', 'pending'),
(1, 3, 3, CURDATE() - INTERVAL 5 DAY, '14:00:00', '15:15:00', 'completed'),
(2, 4, 4, CURDATE() - INTERVAL 3 DAY, '11:00:00', '12:00:00', 'completed');

-- Sample Payments
INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id, payment_date) VALUES
(1, 1500.00, 'credit_card', 'paid', 'TXN2024010112345', NOW()),
(2, 2200.00, 'cash', 'unpaid', NULL, NULL),
(3, 2500.00, 'paypal', 'paid', 'PP2024010212345', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 1800.00, 'credit_card', 'paid', 'TXN2024010312345', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Sample Reviews
INSERT INTO reviews (appointment_id, user_id, rating, comment, created_at) VALUES
(3, 1, 5, 'Amazing hot stone therapy! Sarah is incredibly skilled and the entire experience was relaxing and rejuvenating. Highly recommend!', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 2, 4, 'Great aromatherapy session with Michael. The essential oils were perfect and I felt so relaxed afterward. Will definitely book again.', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Sample Promotions
INSERT INTO promotions (promo_code, description, discount_percent, start_date, end_date) VALUES
('WELCOME20', 'Welcome offer: 20% off your first booking', 20.00, CURDATE(), CURDATE() + INTERVAL 30 DAY),
('SUMMER15', 'Summer special: 15% off all services', 15.00, CURDATE(), CURDATE() + INTERVAL 60 DAY),
('WELLNESS10', 'Wellness month: 10% off all massage services', 10.00, CURDATE(), CURDATE() + INTERVAL 45 DAY);
