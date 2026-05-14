<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');

header('Location: ' . app_url('admin/attendance_sheet.php'));
exit;
