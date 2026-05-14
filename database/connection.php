<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sms';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed. Please check your XAMPP MySQL settings.');
}

$conn->set_charset('utf8mb4');
