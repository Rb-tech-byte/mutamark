<?php

return [
    'offsite_disk' => env('BACKUP_OFFSITE_DISK'),
    'timezone' => env('BACKUP_TIMEZONE', 'Africa/Dar_es_Salaam'),
    'max_restore_bytes' => 128 * 1024 * 1024,
];
