<?php
// Copy this file to config.php on shared hosting and fill in your MySQL details.
// (On Railway these values come from environment variables instead.)
return [
    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_NAME' => 'your_database',
    'DB_USER' => 'your_db_user',
    'DB_PASS' => 'your_db_password',
    'APP_DEBUG' => false,
    // Optional: first admin login is created from these on first run.
    // If left empty, open /admin/setup once to create the owner account.
    'ADMIN_USER' => '',
    'ADMIN_PASS' => '',
];
