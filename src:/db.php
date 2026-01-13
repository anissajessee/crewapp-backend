<?php
// src/db.php
function db() : PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $host = "YOUR_HOST";
  $db   = "crewchat";
  $user = "YOUR_DB_USER";
  $pass = "YOUR_DB_PASS";
  $charset = "utf8mb4";

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];

  $pdo = new PDO($dsn, $user, $pass, $options);
  return $pdo;
}
