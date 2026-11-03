<?php
// Salin file ini menjadi database.local.php, lalu isi dengan nilai dari Supabase.
// JANGAN commit database.local.php ke repository.
return [
    'host' => 'aws-0-REGION.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres',
    'username' => 'postgres.PROJECT_REF',
    'password' => 'ISI_PASSWORD_DATABASE_SUPABASE',
    'sslmode' => 'require',
];
