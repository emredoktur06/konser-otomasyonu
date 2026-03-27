<?php
$host = 'localhost';
$dbname = 'biletim'; // ← doğru veritabanı adını yaz
$username = 'root';
$password = ''; // XAMPP için genelde boş olur

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
