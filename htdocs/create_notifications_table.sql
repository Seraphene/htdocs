-- Create Notifications Table for UB Lost & Found
USE ub_lost_found;

-- Create Notifications table
CREATE TABLE IF NOT EXISTS Notifications (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    StudentNo VARCHAR(20) NOT NULL,
    Type ENUM('photo_approved', 'photo_rejected', 'report_approved', 'report_rejected', 'item_matched', 'admin_message', 'system_alert') NOT NULL,
    Title VARCHAR(100) NOT NULL,
    Message TEXT NOT NULL,
    RelatedID INT DEFAULT NULL, -- ID of related item (ReportID, ItemID, etc.)
    IsRead TINYINT(1) DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentNo) REFERENCES Student(StudentNo) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_notifications_student ON Notifications(StudentNo);
CREATE INDEX idx_notifications_type ON Notifications(Type);
CREATE INDEX idx_notifications_read ON Notifications(IsRead);
CREATE INDEX idx_notifications_created ON Notifications(CreatedAt);

-- Show the table structure
DESCRIBE Notifications; 