-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS xrentals;
CREATE DATABASE xrentals;
USE xrentals;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100),
    phone_number VARCHAR(20),
    profile_image VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rooms table
CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    room_type ENUM('Single Room', 'Double Room', 'Triple Room', '1BHK', '2BHK', '3BHK') NOT NULL,
    furnishing ENUM('Fully Furnished', 'Semi Furnished', 'Unfurnished') NOT NULL,
    bathroom_type ENUM('Attached', 'Common') NOT NULL,
    parking BOOLEAN DEFAULT FALSE,
    wifi BOOLEAN DEFAULT FALSE,
    air_conditioning BOOLEAN DEFAULT FALSE,
    balcony BOOLEAN DEFAULT FALSE,
    water_supply BOOLEAN DEFAULT FALSE,
    image_path VARCHAR(255),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    contact_whatsapp VARCHAR(20),
    status ENUM('Available', 'Rented', 'Pending', 'Inactive') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room reviews table
CREATE TABLE room_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Unread', 'Read', 'Responded') DEFAULT 'Unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_rooms_location ON rooms(location);
CREATE INDEX idx_rooms_price ON rooms(price);
CREATE INDEX idx_rooms_type ON rooms(room_type);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_reviews_rating ON room_reviews(rating);

-- Insert sample admin user
INSERT INTO users (username, password, email, full_name) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@xrentals.com', 'Admin User');

-- Insert sample rooms
INSERT INTO rooms (user_id, title, description, price, location, room_type, furnishing, bathroom_type, parking, wifi) 
VALUES 
(1, 'Cozy Single Room near University', 'A comfortable single room perfect for students. Close to amenities and public transport.', 
300.00, 'University Area', 'Single Room', 'Semi Furnished', 'Attached', true, true),

(1, 'Modern 2BHK Apartment', 'Spacious 2BHK apartment with modern amenities and great city views.', 
800.00, 'City Center', '2BHK', 'Fully Furnished', 'Attached', true, true);