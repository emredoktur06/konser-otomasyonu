<?php
session_start();
require_once 'config.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    // Kullanıcı veritabanında var mı?
    $sql = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$kullanici_adi]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($sifre, $user['sifre'])) {
        // Şifre doğru ➔ Giriş başarılı
        $_SESSION['kullanici_adi'] = $user['kullanici_adi'];
        header("Location: index.php");
        exit();
    } else {
        // Hatalı giriş
        header("Location: login.php?error=hatali_giris");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Modern Login/Register</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css">
  <link rel="stylesheet" href="login_style.css">
  <link rel="stylesheet" href="new_index_style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-5">
    <a class="navbar-brand" href="index.php">Biletim</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <!-- Sağ Taraftaki Menü -->
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="Konserler.php">Konserler</a>
        </li>

        <?php if (isset($_SESSION['kullanici_adi'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
              <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item" href="hesabim.php">Hesabım</a>
              <a class="dropdown-item" href="logout.php">Çıkış Yap</a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


<!-- Login Box -->
<div class="box">
  <div class="login">

    <form action="login.php" method="POST" class="formBx loginBx active">
      <h2><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap <i class="fa-solid fa-heart"></i></h2>
      <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
      <input type="password" name="sifre" placeholder="Şifre" required>
      <input type="submit" value="Giriş Yap" />
      <div class="group">
        <a href="#">Şifremi Unuttum</a>
        <a href="#" id="goRegister">Kayıt Ol</a>
      </div>
    </form>

    <form action="register.php" method="POST" class="formBx registerBx">
      <h2><i class="fa-solid fa-user-plus"></i> Kayıt Ol <i class="fa-solid fa-heart"></i></h2>
      <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="sifre" placeholder="Şifre" required>
      <input type="password" name="sifre_tekrar" placeholder="Şifre Tekrar" required>
      <input type="submit" value="Kayıt Ol" />
      <div class="group">
        <a href="#" id="goLogin">Giriş Yap</a>
      </div>
    </form>

  </div>
</div>

<!-- Footer -->
<footer class="text-white text-center">
  <div class="container">
    <p class="mb-0">© 2025 Biletim. Tüm hakları saklıdır.</p>
    <div class="mt-2">
      <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
      <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
      <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
      <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
    </div>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.getElementById('goRegister').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('.loginBx').classList.remove('active');
    document.querySelector('.registerBx').classList.add('active');
  });

  document.getElementById('goLogin').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('.registerBx').classList.remove('active');
    document.querySelector('.loginBx').classList.add('active');
  });
</script>

</body>
</html>
