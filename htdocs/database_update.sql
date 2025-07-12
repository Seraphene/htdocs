-- Database Update Script for UB Lost & Found
-- Add missing columns that are referenced in the PHP code

USE ub_lost_found;

-- Add Bio column to Student table
ALTER TABLE Student ADD COLUMN Bio TEXT DEFAULT NULL AFTER PhotoURL;

-- Add PhotoConfirmed column to Student table
ALTER TABLE Student ADD COLUMN PhotoConfirmed TINYINT(1) DEFAULT 0 AFTER Bio;

-- Add StatusConfirmed column to ReportItem table
ALTER TABLE ReportItem ADD COLUMN StatusConfirmed TINYINT(1) DEFAULT 0 AFTER ReportStatusID;

-- Add StatusConfirmed column to Item table
ALTER TABLE Item ADD COLUMN StatusConfirmed TINYINT(1) DEFAULT 0 AFTER StatusID;

-- Update existing records to have default values
UPDATE Student SET PhotoConfirmed = 1 WHERE ProfilePhoto IS NOT NULL;
UPDATE ReportItem SET StatusConfirmed = 1 WHERE ReportStatusID IS NOT NULL;
UPDATE Item SET StatusConfirmed = 1 WHERE StatusID IS NOT NULL;

-- Show the updated table structures
DESCRIBE Student;
DESCRIBE ReportItem;
DESCRIBE Item; 