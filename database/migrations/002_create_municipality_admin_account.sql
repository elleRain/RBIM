-- RBIM: Create Municipality Admin accounts (role = muni_admin)
-- Default login: username = municipality name | password = admin123
-- Run each step separately in phpMyAdmin (SQL tab) on the `rbim` database.

-- =============================================================================
-- STEP 1 — Add column (run once; skip if column already exists)
-- =============================================================================

-- 1a. Check first (empty result = column missing, safe to run 1b)
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'city_municipality_id';

-- 1b. Run ONLY if step 1a returned no rows.
-- Copy and run the statement below (without the comment lines):

ALTER TABLE `users`
ADD COLUMN `city_municipality_id` INT(11) NULL DEFAULT NULL AFTER `barangay_id`;

-- If you see: Duplicate column name 'city_municipality_id'
-- → column is already added. Skip Step 1 and continue to Step 2.

-- =============================================================================
-- STEP 2 — List municipalities
-- =============================================================================
SELECT city_municipality_id, name, province, type
FROM city_municipality
ORDER BY name;

-- =============================================================================
-- STEP 3 — Create ONE municipality admin (change municipality name)
-- Username = municipality name | Password = admin123
-- =============================================================================
INSERT INTO users (
    username,
    password,
    role,
    account_status,
    barangay,
    barangay_id,
    city_municipality_id
)
SELECT
    cm.name,
    '$2y$10$xCP/NE6MOmXI1mxGSUmxb.52GzA/u3XZyCfOCVhU4gyNDTuy8mt2u',
    'muni_admin',
    'active',
    NULL,
    NULL,
    cm.city_municipality_id
FROM city_municipality cm
WHERE cm.name = 'Lazi'
  AND NOT EXISTS (
      SELECT 1
      FROM users u
      WHERE u.role = 'muni_admin'
        AND u.city_municipality_id = cm.city_municipality_id
  )
LIMIT 1;

-- Other municipality names:
-- 'Enrique Villanueva', 'Larena', 'Lazi', 'Maria', 'San Juan', 'Siquijor'

-- =============================================================================
-- STEP 4 — Create municipality admin for ALL municipalities (one per LGU)
-- NOTE: Opening User Management as Super Admin also runs this automatically.
-- Username = municipality name | Password = admin123
-- =============================================================================
INSERT INTO users (
    username,
    password,
    role,
    account_status,
    barangay,
    barangay_id,
    city_municipality_id
)
SELECT
    cm.name,
    '$2y$10$xCP/NE6MOmXI1mxGSUmxb.52GzA/u3XZyCfOCVhU4gyNDTuy8mt2u',
    'muni_admin',
    'active',
    NULL,
    NULL,
    cm.city_municipality_id
FROM city_municipality cm
WHERE NOT EXISTS (
    SELECT 1
    FROM users u
    WHERE u.role = 'muni_admin'
      AND u.city_municipality_id = cm.city_municipality_id
);

-- =============================================================================
-- STEP 5 — Verify accounts
-- =============================================================================
SELECT
    u.admin_id,
    u.username,
    u.role,
    u.account_status,
    u.city_municipality_id,
    cm.name AS municipality
FROM users u
LEFT JOIN city_municipality cm ON cm.city_municipality_id = u.city_municipality_id
WHERE u.role = 'muni_admin'
ORDER BY cm.name, u.username;

-- =============================================================================
-- Login
-- =============================================================================
-- Role:     muni_admin
-- Username: Lazi          (exact municipality name)
-- Password: admin123
