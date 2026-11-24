-- Add municipality link column to users (run once on `rbim` database).

-- Check first:
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'city_municipality_id';

-- Run only if the check above returned no rows.
-- Copy and run the statement below:

ALTER TABLE `users`
ADD COLUMN `city_municipality_id` INT(11) NULL DEFAULT NULL AFTER `barangay_id`;

-- Role values:
--   super_admin  = Super Admin
--   muni_admin   = Admin (Municipality)
--   admin        = Admin (Barangay)
