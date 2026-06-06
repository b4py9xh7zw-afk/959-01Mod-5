<?php
/**
 * Cron job for seat management
 * This script should be run periodically (e.g., every hour) to:
 * 1. Mark overdue borrows as expired
 * 2. Auto-recover seats that have been expired for more than 24 hours
 * 
 * Usage: php cron_seat_check.php
 * Can be setup as a cron job: 0 * * * * /usr/bin/php /path/to/app/scripts/cron_seat_check.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SeatBorrow.php';

try {
    $seatBorrowModel = new SeatBorrow();
    
    echo "[" . date('Y-m-d H:i:s') . "] Starting seat check cron job...\n";
    
    $expiredCount = $seatBorrowModel->expireOverdue();
    echo "[" . date('Y-m-d H:i:s') . "] Marked {$expiredCount} overdue borrow(s) as expired.\n";
    
    $recoveredCount = $seatBorrowModel->autoRecoverExpired();
    echo "[" . date('Y-m-d H:i:s') . "] Auto-recovered {$recoveredCount} expired seat(s).\n";
    
    echo "[" . date('Y-m-d H:i:s') . "] Seat check completed successfully.\n";
    
    $stats = $seatBorrowModel->getSeatStats();
    echo "[" . date('Y-m-d H:i:s') . "] Current seat status:\n";
    echo "  - Total floating: " . ($stats['total_floating'] ?? 0) . "\n";
    echo "  - Idle: " . ($stats['idle_count'] ?? 0) . "\n";
    echo "  - Borrowed: " . ($stats['borrowed_count'] ?? 0) . "\n";
    echo "  - Abnormal: " . ($stats['abnormal_count'] ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
