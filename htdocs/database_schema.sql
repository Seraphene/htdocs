-- University of Batangas Lost & Found Database Schema
-- For local MySQL Workbench setup

-- Create database
CREATE DATABASE IF NOT EXISTS ub_lost_found;
USE ub_lost_found;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS ReportItem_Match;
DROP TABLE IF EXISTS ReportItem;
DROP TABLE IF EXISTS Item;
DROP TABLE IF EXISTS ItemClass;
DROP TABLE IF EXISTS ItemStatus;
DROP TABLE IF EXISTS ReportStatus;
DROP TABLE IF EXISTS Student;
DROP TABLE IF EXISTS Admin;

-- Create Admin table
CREATE TABLE Admin (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    AdminName VARCHAR(100) NOT NULL,
    PhotoURL VARCHAR(255) DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Student table
CREATE TABLE Student (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    StudentNo VARCHAR(20) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    StudentName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PhoneNo VARCHAR(20) DEFAULT NULL,
    Course VARCHAR(100) DEFAULT NULL,
    YearLevel VARCHAR(20) DEFAULT NULL,
    PhotoURL VARCHAR(255) DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create ItemClass table
CREATE TABLE ItemClass (
    ItemClassID INT PRIMARY KEY AUTO_INCREMENT,
    ClassName VARCHAR(100) NOT NULL,
    Description TEXT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create ItemStatus table
CREATE TABLE ItemStatus (
    StatusID INT PRIMARY KEY AUTO_INCREMENT,
    StatusName VARCHAR(50) NOT NULL,
    Description TEXT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create ReportStatus table
CREATE TABLE ReportStatus (
    ReportStatusID INT PRIMARY KEY AUTO_INCREMENT,
    StatusName VARCHAR(50) NOT NULL,
    Description TEXT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Item table (for found items)
CREATE TABLE Item (
    ItemID INT PRIMARY KEY AUTO_INCREMENT,
    ItemName VARCHAR(200) NOT NULL,
    Description TEXT DEFAULT NULL,
    ItemClassID INT NOT NULL,
    StatusID INT NOT NULL,
    PhotoURL VARCHAR(255) DEFAULT NULL,
    LocationFound VARCHAR(200) DEFAULT NULL,
    DateFound DATE NOT NULL,
    AdminID INT NOT NULL,
    ContactInfo TEXT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ItemClassID) REFERENCES ItemClass(ItemClassID),
    FOREIGN KEY (StatusID) REFERENCES ItemStatus(StatusID),
    FOREIGN KEY (AdminID) REFERENCES Admin(AdminID)
);

-- Create ReportItem table (for lost items)
CREATE TABLE ReportItem (
    ReportID INT PRIMARY KEY AUTO_INCREMENT,
    ItemName VARCHAR(200) NOT NULL,
    Description TEXT DEFAULT NULL,
    ItemClassID INT NOT NULL,
    ReportStatusID INT NOT NULL,
    PhotoURL VARCHAR(255) DEFAULT NULL,
    LostLocation VARCHAR(200) DEFAULT NULL,
    DateOfLoss DATE NOT NULL,
    StudentNo VARCHAR(20) NOT NULL,
    ContactInfo TEXT DEFAULT NULL,
    Reward DECIMAL(10,2) DEFAULT 0.00,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ItemClassID) REFERENCES ItemClass(ItemClassID),
    FOREIGN KEY (ReportStatusID) REFERENCES ReportStatus(ReportStatusID),
    FOREIGN KEY (StudentNo) REFERENCES Student(StudentNo)
);

-- Create ReportItem_Match table (for matching lost and found items)
CREATE TABLE ReportItem_Match (
    MatchID INT PRIMARY KEY AUTO_INCREMENT,
    ReportID INT NOT NULL,
    ItemID INT NOT NULL,
    MatchScore DECIMAL(5,2) DEFAULT 0.00,
    MatchStatus ENUM('Pending', 'Confirmed', 'Rejected') DEFAULT 'Pending',
    MatchedBy INT DEFAULT NULL, -- AdminID
    MatchedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Notes TEXT DEFAULT NULL,
    FOREIGN KEY (ReportID) REFERENCES ReportItem(ReportID),
    FOREIGN KEY (ItemID) REFERENCES Item(ItemID),
    FOREIGN KEY (MatchedBy) REFERENCES Admin(AdminID)
);

-- Insert sample data

-- Insert Admin
INSERT INTO Admin (Username, Password, Email, AdminName) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'foundlost004@gmail.com', 'Admin User');

-- Insert Item Classes
INSERT INTO ItemClass (ClassName, Description) VALUES
('Electronics', 'Electronic devices like phones, laptops, tablets, etc.'),
('Books', 'Textbooks, notebooks, and other reading materials'),
('Clothing', 'Clothes, shoes, bags, and accessories'),
('Jewelry', 'Rings, necklaces, watches, and other jewelry items'),
('Documents', 'IDs, certificates, papers, and important documents'),
('Sports Equipment', 'Balls, rackets, gym equipment, etc.'),
('Others', 'Other miscellaneous items');

-- Insert Item Statuses
INSERT INTO ItemStatus (StatusName, Description) VALUES
('Available', 'Item is available for claiming'),
('Claimed', 'Item has been claimed by owner'),
('Expired', 'Item has been disposed of after holding period'),
('Pending', 'Item is under review');

-- Insert Report Statuses
INSERT INTO ReportStatus (StatusName, Description) VALUES
('Open', 'Report is still open and searching'),
('Found', 'Item has been found and matched'),
('Closed', 'Report has been closed'),
('Expired', 'Report has expired');

-- Insert sample Students
INSERT INTO Student (StudentNo, PasswordHash, StudentName, Email, PhoneNo, Course, YearLevel) VALUES
('2021-0001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Dela Cruz', 'juan.delacruz@ub.edu.ph', '09123456789', 'BS Computer Science', '3rd Year'),
('2021-0002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', 'maria.santos@ub.edu.ph', '09187654321', 'BS Business Administration', '2nd Year'),
('2021-0003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Garcia', 'pedro.garcia@ub.edu.ph', '09111222333', 'BS Engineering', '4th Year');

-- Insert sample Found Items (Items)
INSERT INTO Item (ItemName, Description, ItemClassID, StatusID, LocationFound, DateFound, AdminID, ContactInfo) VALUES
('iPhone 13', 'Black iPhone 13 with clear case, found near the library', 1, 1, 'University Library - Ground Floor', '2024-01-15', 1, 'Contact admin at foundlost004@gmail.com'),
('Calculus Textbook', 'Calculus: Early Transcendentals 8th Edition, has notes inside', 2, 1, 'Engineering Building - Room 201', '2024-01-14', 1, 'Contact admin at foundlost004@gmail.com'),
('Red Backpack', 'Red Jansport backpack with laptop compartment', 3, 1, 'Student Center - Cafeteria', '2024-01-13', 1, 'Contact admin at foundlost004@gmail.com'),
('Silver Watch', 'Silver analog watch with leather strap', 4, 1, 'Business Building - Hallway', '2024-01-12', 1, 'Contact admin at foundlost004@gmail.com'),
('Student ID Card', 'Student ID with name partially visible', 5, 1, 'Main Gate - Security Office', '2024-01-11', 1, 'Contact admin at foundlost004@gmail.com');

-- Insert sample Lost Items (ReportItems)
INSERT INTO ReportItem (ItemName, Description, ItemClassID, ReportStatusID, LostLocation, DateOfLoss, StudentNo, ContactInfo, Reward) VALUES
('Samsung Galaxy S21', 'Blue Samsung phone with cracked screen protector', 1, 1, 'Computer Lab - Room 301', '2024-01-10', '2021-0001', 'Contact: 09123456789, Email: juan.delacruz@ub.edu.ph', 500.00),
('Accounting Book', 'Financial Accounting textbook with yellow highlighter marks', 2, 1, 'Business Building - Library', '2024-01-09', '2021-0002', 'Contact: 09187654321, Email: maria.santos@ub.edu.ph', 200.00),
('White Sneakers', 'White Nike sneakers, size 9, with blue laces', 3, 1, 'Gymnasium - Basketball Court', '2024-01-08', '2021-0003', 'Contact: 09111222333, Email: pedro.garcia@ub.edu.ph', 300.00),
('Gold Ring', 'Gold ring with small diamond, family heirloom', 4, 1, 'Engineering Building - Parking Lot', '2024-01-07', '2021-0001', 'Contact: 09123456789, Email: juan.delacruz@ub.edu.ph', 1000.00),
('Laptop Charger', 'HP laptop charger with black cable', 1, 1, 'Student Center - Study Area', '2024-01-06', '2021-0002', 'Contact: 09187654321, Email: maria.santos@ub.edu.ph', 150.00);

-- Insert sample matches
INSERT INTO ReportItem_Match (ReportID, ItemID, MatchScore, MatchStatus, MatchedBy) VALUES
(1, 1, 85.50, 'Pending', 1),
(2, 2, 90.00, 'Confirmed', 1),
(3, 3, 75.25, 'Pending', 1);

-- Create indexes for better performance
CREATE INDEX idx_student_studentno ON Student(StudentNo);
CREATE INDEX idx_student_email ON Student(Email);
CREATE INDEX idx_admin_username ON Admin(Username);
CREATE INDEX idx_admin_email ON Admin(Email);
CREATE INDEX idx_item_class ON Item(ItemClassID);
CREATE INDEX idx_item_status ON Item(StatusID);
CREATE INDEX idx_reportitem_class ON ReportItem(ItemClassID);
CREATE INDEX idx_reportitem_status ON ReportItem(ReportStatusID);
CREATE INDEX idx_reportitem_studentno ON ReportItem(StudentNo);
CREATE INDEX idx_item_admin ON Item(AdminID);

-- Show all tables
SHOW TABLES;

-- Show sample data
SELECT 'Admin' as TableName, COUNT(*) as Count FROM Admin
UNION ALL
SELECT 'Student', COUNT(*) FROM Student
UNION ALL
SELECT 'ItemClass', COUNT(*) FROM ItemClass
UNION ALL
SELECT 'ItemStatus', COUNT(*) FROM ItemStatus
UNION ALL
SELECT 'ReportStatus', COUNT(*) FROM ReportStatus
UNION ALL
SELECT 'Item (Found)', COUNT(*) FROM Item
UNION ALL
SELECT 'ReportItem (Lost)', COUNT(*) FROM ReportItem
UNION ALL
SELECT 'ReportItem_Match', COUNT(*) FROM ReportItem_Match; 