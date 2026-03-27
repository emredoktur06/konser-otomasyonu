<?php
session_start();
require 'config.php';

if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı verisi
$stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
$stmt->execute([$_SESSION['kullanici_adi']]);
$user = $stmt->fetch();

$profilFoto = $user['profile_photo'] ?? 'images/default-profile.png';
$bio = $user['bio'] ?? 'Kendinizi tanıtın.';
$dogumTarihi = $user['birthdate'] ?? '0000-00-00';
$konserSayisi = 0;
$puan = $konserSayisi * 50;

// Bakiye yükleme işlemi
if (isset($_POST['bakiye_yukle'])) {
    $eklenecek = (int) $_POST['yuklenecek_bakiye'];
    $id = $user['id'];
    $stmt = $conn->prepare("UPDATE kullanicilar SET bakiye = bakiye + ? WHERE id = ?");
    $stmt->execute([$eklenecek, $id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Kullanıcının e-mail bilgisine göre biletleri çek
$email = $user['email'];

$stmt = $conn->prepare("SELECT * FROM biletler WHERE email = ? AND tarih >= CURDATE()");
$stmt->execute([$email]);
$aktifBiletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM biletler WHERE email = ? AND tarih < CURDATE()");
$stmt->execute([$email]);
$gecmisBiletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Yorumlar
$stmt = $conn->prepare("SELECT * FROM yorumlar WHERE kullanici_adi = ?");
$stmt->execute([$_SESSION['kullanici_adi']]);
$yorumlar = $stmt->fetchAll();

// Konser geçmişi (isteğe bağlı)
$attendedConcerts = [
    ['etkinlik' => 'Rock Festivali', 'tarih' => '2024-05-01', 'lokasyon' => 'İstanbul'],
    ['etkinlik' => 'Jazz Gecesi', 'tarih' => '2023-10-15', 'lokasyon' => 'Ankara']
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Hesabım</title>
  <link rel="stylesheet" href="hesabim_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2e0e6c9f2.js" crossorigin="anonymous"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-5">
    <a class="navbar-brand" href="index.php">Biletim</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><a class="nav-link" href="Konserler.php">Konserler</a></li>
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

<div class="container my-5">
  <div class="row g-4">
    <div class="col-md-4">
      <div class="profil-kart p-3 position-relative">
        <button class="settings-btn" data-bs-toggle="modal" data-bs-target="#ayarModal"><i class="fas fa-cog"></i></button>
        <img src="<?= htmlspecialchars($profilFoto) ?>" class="img-fluid mb-3">
        <h5 class="text-center"><?= htmlspecialchars($user['kullanici_adi']) ?></h5>
        <p class="text-center text-muted"><?= htmlspecialchars($bio) ?></p>
        <ul class="list-group list-group-flush text-light mt-2">
          <li class="list-group-item bg-transparent border-0"><strong>E-posta:</strong> <?= htmlspecialchars($user['email']) ?></li>
          <li class="list-group-item bg-transparent border-0"><strong>Doğum Tarihi:</strong> <?= htmlspecialchars($dogumTarihi) ?></li>
        </ul>
      </div>

      <div class="sayac-kart mt-3 text-center p-3">
        <h5 class="text-pink"><?= $konserSayisi ?> Konser</h5>
        <p><?= $puan ?> Puan</p>
        <p><strong>Bakiye:</strong> <?= $user['bakiye'] ?? 0 ?> ₺</p>
        <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#bakiyeModal">Bakiye Yükle</button>
      </div>
    </div>

    <div class="col-md-8">
      <div class="biletler-container">
        <div class="biletler-scroll">
          <div class="bilet-bolum">
            <h5>Aktif Biletler</h5>
            <?php foreach ($aktifBiletler as $b): ?>
              <div class="bilet-kart d-flex justify-content-between">
                <span><?= htmlspecialchars($b['baslik']) ?></span>
                <span><?= htmlspecialchars($b['tarih']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="bilet-bolum">
            <h5>Geçmiş Biletler</h5>
            <?php foreach ($gecmisBiletler as $b): ?>
              <div class="bilet-kart">
                <h6><?= htmlspecialchars($b['baslik']) ?></h6>
                <p><?= htmlspecialchars($b['tarih']) ?> • Koltuk: <?= htmlspecialchars($b['koltuk']) ?></p>
                <a href="#" class="btn">Değerlendir</a>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="bilet-bolum mt-5">
            <h5>Katıldığı Konserler</h5>
            <?php foreach ($attendedConcerts as $concert): ?>
              <div class="konser-kart">
                <h6><?= htmlspecialchars($concert['etkinlik']) ?></h6>
                <p><?= $concert['tarih'] ?> • Lokasyon: <?= htmlspecialchars($concert['lokasyon']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="bilet-bolum mt-5">
            <h5>Puanlar ve Yorumlar</h5>
            <?php foreach ($yorumlar as $yorum): ?>
              <div class="puan-yorum-kart">
                <h6><?= htmlspecialchars($yorum['baslik']) ?></h6>
                <div class="puan">
                  <span class="badge bg-warning"><?= $yorum['puan'] ?> Puan</span>
                </div>
                <p class="yorum mt-2"><?= htmlspecialchars($yorum['yorum']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<footer class="footer text-center py-3">
  <div class="container">© 2025 Biletim. Tüm Hakları Saklıdır.</div>
</footer>

<!-- Profil Ayar Modal -->
<div class="modal fade" id="ayarModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ayar-modal p-3">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header border-0">
          <h5 class="modal-title text-white">Profil Ayarları</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="file" name="foto" class="form-control mb-3">
          <input type="text" name="yeni_kadi" class="form-control mb-3" value="<?= htmlspecialchars($user['kullanici_adi']) ?>" placeholder="Kullanıcı Adı">
          <input type="email" name="yeni_email" class="form-control mb-3" value="<?= htmlspecialchars($user['email']) ?>" placeholder="E-posta">
          <input type="date" name="yeni_dogum" class="form-control mb-3" value="<?= $dogumTarihi ?>">
          <textarea name="yeni_bio" rows="2" class="form-control mb-3" placeholder="Biyografi"><?= htmlspecialchars($bio) ?></textarea>
          <input type="password" name="yeni_sifre" class="form-control mb-3" placeholder="Yeni Şifre (opsiyonel)">
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-bilet w-100">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bakiye Yükleme Modal -->
<div class="modal fade" id="bakiyeModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Bakiye Yükle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="number" name="yuklenecek_bakiye" class="form-control" placeholder="₺ miktar" required min="1">
        </div>
        <div class="modal-footer">
          <button type="submit" name="bakiye_yukle" class="btn btn-primary">Yükle</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });
  function openModal(title, content) {
    document.getElementById('eventModalLabel').innerText = title;
    document.getElementById('eventModalContent').innerText = content;
    $('#eventModal').modal('show');
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
