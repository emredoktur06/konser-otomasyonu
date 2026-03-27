<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'];
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];

    // Şifreler aynı mı kontrol et
    if ($sifre !== $sifre_tekrar) {
        header("Location: login.php?error=sifre_uyusmuyor");
        exit();
    }

    // Şifreyi güvenli şekilde hash'le
    $sifre_hashli = password_hash($sifre, PASSWORD_DEFAULT);

    // Veritabanına kaydet
    $sql = "INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$kullanici_adi, $email, $sifre_hashli])) {
        // Kayıt başarılı ➔ Otomatik giriş yap
        $_SESSION['kullanici_adi'] = $kullanici_adi;
        header("Location: index.php");
        exit();
    } else {
        // Kayıt başarısızsa login ekranına hata ile yönlendir
        header("Location: login.php?error=kayit_basarisiz");
        exit();
    }
}
?>
