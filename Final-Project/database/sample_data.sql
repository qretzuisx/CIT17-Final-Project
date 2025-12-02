-- Sample Data for Car Wash Appointment System
USE carwash_system;

-- Sample Users (password for all: password123)
INSERT INTO users (username, email, password, full_name, phone, address, user_type) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '09171234567', '123 Main St, Manila', 'customer'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '09181234567', '456 Oak Ave, Quezon City', 'customer'),
('mike_washer', 'mike@carwash.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', '09191234567', '789 Pine Rd, Makati', 'washer'),
('tom_washer', 'tom@carwash.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tom Wilson', '09201234567', '321 Elm St, Pasig', 'washer');

-- Sample Vehicles
INSERT INTO vehicles (user_id, vehicle_type, brand, model, year, color, plate_number) VALUES
(2, 'sedan', 'Toyota', 'Camry', 2020, 'White', 'ABC1234'),
(2, 'suv', 'Honda', 'CR-V', 2021, 'Black', 'XYZ5678'),
(3, 'sedan', 'Mazda', '3', 2019, 'Red', 'DEF9012');

-- Sample Services
INSERT INTO services (service_name, description, base_price, duration_minutes, vehicle_type, image_url) VALUES
('Basic Wash', 'Exterior wash with soap and water, tire cleaning', 150.00, 30, 'all', 'images/basic-wash.jpg'),
('Premium Wash', 'Basic wash plus interior vacuuming and window cleaning', 300.00, 60, 'all', 'images/premium-wash.jpg'),
('Deluxe Wash', 'Premium wash plus waxing and tire shine', 500.00, 90, 'all', 'images/deluxe-wash.jpg'),
('Full Detail', 'Complete interior and exterior detailing with polish', 1200.00, 180, 'all', 'images/full-detail.jpg'),
('Engine Cleaning', 'Engine bay cleaning and degreasing', 400.00, 45, 'all', 'images/engine-clean.jpg'),
('Motorcycle Wash', 'Complete motorcycle washing and cleaning', 100.00, 20, 'motorcycle', 'images/moto-wash.jpg');

-- Sample Washers
INSERT INTO washers (user_id, specialization, experience_years, rating, total_jobs) VALUES
(4, 'Full Detailing, Engine Cleaning', 5, 4.85, 150),
(5, 'Premium Wash, Basic Cleaning', 3, 4.60, 98);

-- Sample Schedules (for the next 7 days)
INSERT INTO schedules (washer_id, schedule_date, start_time, end_time, status) VALUES
-- Mike's Schedule
(1, CURDATE(), '08:00:00', '09:30:00', 'available'),
(1, CURDATE(), '09:30:00', '11:00:00', 'available'),
(1, CURDATE(), '11:00:00', '12:30:00', 'available'),
(1, CURDATE(), '13:30:00', '15:00:00', 'available'),
(1, CURDATE(), '15:00:00', '16:30:00', 'available'),
-- Tom's Schedule
(2, CURDATE(), '08:00:00', '09:00:00', 'available'),
(2, CURDATE(), '09:00:00', '10:00:00', 'available'),
(2, CURDATE(), '10:00:00', '11:00:00', 'available'),
(2, CURDATE(), '13:00:00', '14:00:00', 'available'),
(2, CURDATE(), '14:00:00', '15:00:00', 'available');

-- Sample Appointments
INSERT INTO appointments (user_id, vehicle_id, service_id, washer_id, schedule_id, appointment_date, appointment_time, total_amount, status, notes) VALUES
(2, 1, 2, 1, 1, CURDATE(), '08:00:00', 300.00, 'confirmed', 'Please focus on interior cleaning'),
(3, 3, 1, 2, 6, CURDATE(), '08:00:00', 150.00, 'pending', NULL);

-- Sample Payments
INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id) VALUES
(1, 300.00, 'gcash', 'completed', 'GC2024010112345'),
(2, 150.00, 'cash', 'pending', NULL);
