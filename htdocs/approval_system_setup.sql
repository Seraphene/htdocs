-- UB Lost & Found Approval System Setup
-- This script sets up the complete admin approval system

USE ub_lost_found;

-- Step 1: Add missing columns if they don't exist
-- Add Bio column to Student table
ALTER TABLE student ADD COLUMN IF NOT EXISTS Bio TEXT DEFAULT NULL AFTER PhotoURL;

-- Add PhotoConfirmed column to Student table
ALTER TABLE student ADD COLUMN IF NOT EXISTS PhotoConfirmed TINYINT(1) DEFAULT 0 AFTER Bio;

-- Add StatusConfirmed column to ReportItem table
ALTER TABLE reportitem ADD COLUMN IF NOT EXISTS StatusConfirmed TINYINT(1) DEFAULT 0 AFTER ReportStatusID;

-- Add StatusConfirmed column to Item table
ALTER TABLE item ADD COLUMN IF NOT EXISTS StatusConfirmed TINYINT(1) DEFAULT 0 AFTER StatusID;

-- Step 2: Insert status data if tables are empty
-- Insert Item Statuses (only if table is empty)
INSERT INTO itemstatus (StatusName, Description) 
SELECT * FROM (
    SELECT 'Available' as StatusName, 'Item is available for claiming' as Description
    UNION ALL SELECT 'Claimed', 'Item has been claimed by owner'
    UNION ALL SELECT 'Expired', 'Item has been disposed of after holding period'
    UNION ALL SELECT 'Pending', 'Item is under review'
) AS temp
WHERE NOT EXISTS (SELECT 1 FROM itemstatus LIMIT 1);

-- Insert Report Statuses (only if table is empty)
INSERT INTO reportstatus (StatusName, Description)
SELECT * FROM (
    SELECT 'Open' as StatusName, 'Report is still open and searching' as Description
    UNION ALL SELECT 'Found', 'Item has been found and matched'
    UNION ALL SELECT 'Closed', 'Report has been closed'
    UNION ALL SELECT 'Expired', 'Report has expired'
) AS temp
WHERE NOT EXISTS (SELECT 1 FROM reportstatus LIMIT 1);

-- Step 3: Update existing records with default approval status
-- Set PhotoConfirmed = 1 for students who already have profile photos
UPDATE student SET PhotoConfirmed = 1 WHERE ProfilePhoto IS NOT NULL AND PhotoConfirmed = 0;

-- Set StatusConfirmed = 1 for existing approved reports (for backward compatibility)
UPDATE reportitem SET StatusConfirmed = 1 WHERE StatusConfirmed = 0 AND ReportStatusID IS NOT NULL;

-- Set StatusConfirmed = 1 for existing approved items (for backward compatibility)
UPDATE item SET StatusConfirmed = 1 WHERE StatusConfirmed = 0 AND StatusID IS NOT NULL;

-- Step 4: Show the current state
SELECT 'Database Setup Complete' as Status;

-- Show table structures
SELECT 'Student Table Structure:' as Info;
DESCRIBE student;

SELECT 'ReportItem Table Structure:' as Info;
DESCRIBE reportitem;

SELECT 'Item Table Structure:' as Info;
DESCRIBE item;

-- Show status data
SELECT 'Item Statuses:' as Info;
SELECT * FROM itemstatus;

SELECT 'Report Statuses:' as Info;
SELECT * FROM reportstatus;

-- Show approval statistics
SELECT 'Approval Statistics:' as Info;
SELECT 
    'Students with approved photos' as Category,
    COUNT(*) as Count
FROM student 
WHERE PhotoConfirmed = 1 AND ProfilePhoto IS NOT NULL
UNION ALL
SELECT 
    'Students with pending photo approval' as Category,
    COUNT(*) as Count
FROM student 
WHERE PhotoConfirmed = 0 AND ProfilePhoto IS NOT NULL
UNION ALL
SELECT 
    'Approved lost item reports' as Category,
    COUNT(*) as Count
FROM reportitem 
WHERE StatusConfirmed = 1
UNION ALL
SELECT 
    'Pending lost item reports' as Category,
    COUNT(*) as Count
FROM reportitem 
WHERE StatusConfirmed = 0
UNION ALL
SELECT 
    'Approved found items' as Category,
    COUNT(*) as Count
FROM item 
WHERE StatusConfirmed = 1
UNION ALL
SELECT 
    'Pending found items' as Category,
    COUNT(*) as Count
FROM item 
WHERE StatusConfirmed = 0; 