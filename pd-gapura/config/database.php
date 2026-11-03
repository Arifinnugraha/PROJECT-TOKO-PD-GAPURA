<?php
/** Koneksi PostgreSQL Supabase. Secrets disimpan di environment server atau database.local.php. */
$config = [
    'host' => getenv('SUPABASE_DB_HOST') ?: '',
    'port' => getenv('SUPABASE_DB_PORT') ?: '5432',
    'dbname' => getenv('SUPABASE_DB_NAME') ?: 'postgres',
    'username' => getenv('SUPABASE_DB_USER') ?: '',
    'password' => getenv('SUPABASE_DB_PASSWORD') ?: '',
    'sslmode' => getenv('SUPABASE_DB_SSLMODE') ?: 'require',
];

// Alternatif untuk hosting yang tidak menyediakan pengaturan environment variables.
// File database.local.php tidak dimasukkan ke Git dan harus dijaga kerahasiaannya.
$localConfig = __DIR__ . '/database.local.php';
if (is_file($localConfig)) {
    $local = require $localConfig;
    if (is_array($local)) {
        $config = array_merge($config, $local);
    }
}

return $config;
