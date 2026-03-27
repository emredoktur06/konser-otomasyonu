<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo "Geçersiz konser ID.";
    exit;
}

// Konser bilgisi
$sql = "SELECT * FROM konserler WHERE id = $id";
$konser = $conn->query($sql)->fetch_assoc();
if (!$konser) {
    echo "Konser bulunamadı.";
    exit;
}

// Yorum ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_ekle'])) {
    if (isset($_SESSION['kullanici_adi'])) {
        $kullanici_adi = $conn->real_escape_string($_SESSION['kullanici_adi']);
        $yorum = $conn->real_escape_string($_POST['yorum']);
        if ($kullanici_adi && $yorum) {
            $conn->query("INSERT INTO yorumlar (konser_id, kullanici_adi, yorum) VALUES ($id, '$kullanici_adi', '$yorum')");
        }
    } else {
        echo "Lütfen giriş yapın.";
        exit;
    }
}

// Bilet alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bilet_al'])) {
    if (isset($_SESSION['kullanici_adi'])) {
        $kullanici_adi = $conn->real_escape_string($_SESSION['kullanici_adi']); // Kullanıcı adını al
        $ad = $conn->real_escape_string($_POST['ad']);
        $email = $conn->real_escape_string($_POST['email']);
        $adet = (int)$_POST['adet'];
        $bilet_fiyati = $konser['bilet_fiyati']; // Konserin bilet fiyatı

        // Kullanıcı bakiyesi sorgusu
        $kullanici = $conn->query("SELECT * FROM kullanicilar WHERE kullanici_adi = '$kullanici_adi'")->fetch_assoc();
        if ($kullanici) {
            $mevcut_bakiye = $kullanici['bakiye']; // Kullanıcının mevcut bakiyesi
            $toplam_fiyat = $bilet_fiyati * $adet; // Toplam fiyat

            // Kullanıcının bu konser için daha önce bilet alıp almadığını kontrol et
            $bilet_kontrol = $conn->query("SELECT * FROM biletler WHERE konser_id = $id AND kullanici_adi = '$kullanici_adi'");
            if ($bilet_kontrol->num_rows > 0) {
                echo "Bu konser için zaten bir bilet aldınız.";
                exit;
            }

            // Bakiyeyi kontrol et
            if ($mevcut_bakiye >= $toplam_fiyat) {
                // Yeterli bakiye var, bilet alımını işle
                $baslik = $konser['baslik']; 
                $conn->query("INSERT INTO biletler (konser_id, kullanici_adi, email, adet, baslik) VALUES ($id, '$kullanici_adi', '$email', $adet,'$baslik')");

                // Yeni bilet sayısını güncelle
                $kalan_bilet = $konser['bilet_sayisi'] - $adet;
                $conn->query("UPDATE konserler SET bilet_sayisi = $kalan_bilet WHERE id = $id");

                // Kullanıcı bakiyesini güncelle
                $yeni_bakiye = $mevcut_bakiye - $toplam_fiyat;
                $conn->query("UPDATE kullanicilar SET bakiye = $yeni_bakiye WHERE kullanici_adi = '$kullanici_adi'");

                // Başarı mesajı
                $bilet_basarili = true;
                $konser['bilet_sayisi'] = $kalan_bilet;
            } else {
                $bilet_hata = "Yetersiz bakiye! Mevcut bakiyeniz: " . number_format($mevcut_bakiye, 2) . " TL.";
            }
        } else {
            echo "Kullanıcı bulunamadı.";
            exit;
        }
    } else {
        echo "Lütfen giriş yapın.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($konser['baslik']) ?> - Detay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="konserler._style.css?v=1.0.3">
</head>
<body style="background-image: url(images/back.jpg)">

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

<div class="container mt-5">
  <div class="row">
    <div class="col-md-6">
      <img src="<?= htmlspecialchars($konser['resim_yolu']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($konser['baslik']) ?>" style="width: 100%;">
    </div>
    <div class="col-md-6">
      <h2><?= htmlspecialchars($konser['baslik']) ?></h2>
      <p><strong>Tarih:</strong> <?= date("d.m.Y", strtotime($konser['tarih'])) ?></p>
      <p><strong>Tür:</strong> <?= htmlspecialchars($konser['tur']) ?></p>
      <p><strong>Yer:</strong> <?= isset($konser['Yer']) ? htmlspecialchars($konser['Yer']) : 'Belirtilmemiş' ?></p>
      <p><strong>Saat:</strong> <?= isset($konser['Saat']) ? htmlspecialchars($konser['Saat']) : 'Belirtilmemiş' ?></p>
      <p><strong>Bilet Fiyatı:</strong> <?= number_format($konser['bilet_fiyati'], 2) ?> TL</p>
      <p>
        <strong>Mevcut Bilet Sayısı:</strong>
        <?php if ($konser['bilet_sayisi'] > 0): ?>
          <?= $konser['bilet_sayisi'] ?> adet
        <?php else: ?>
          <span class="text-danger">TÜKENDİ</span>
        <?php endif; ?>
      </p>
      <p><?= htmlspecialchars($konser['aciklama']) ?></p>

      <h5 class="mt-4">Bilet Al</h5>
      <?php if ($konser['bilet_sayisi'] > 0): ?>
        <button id="biletAcButon" class="btn btn-success mb-3">Bilet Al</button>
        <div id="biletFormu" style="display: none;">
          <?php if (isset($bilet_hata)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($bilet_hata) ?></div>
          <?php endif; ?>
          <?php if (!empty($bilet_basarili)): ?>
            <div class="alert alert-success">Bilet başarıyla alındı!</div>
          <?php endif; ?>
          <form method="POST">
            <input type="hidden" name="bilet_al" value="1">
            <div class="form-group">
              <input type="text" name="ad" class="form-control" placeholder="Ad Soyad" required>
            </div>
            <div class="form-group">
              <input type="email" name="email" class="form-control" placeholder="E-posta" required>
            </div>
            <div class="form-group">
              <input type="number" name="adet" class="form-control" placeholder="Adet" min="1" max="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Satın Al</button>
          </form>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">Bu konserin tüm biletleri tükenmiştir.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Yorumlar -->
  <hr>
  <h4 class="mt-5">Yorumlar</h4>
  <form method="POST" class="mb-4">
    <input type="hidden" name="yorum_ekle" value="1">
    <div class="form-group">
      <textarea name="yorum" class="form-control" placeholder="Yorumunuz" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Yorum Yap</button>
  </form>

  <?php
    $yorumlar = $conn->query("SELECT * FROM yorumlar WHERE konser_id = $id ORDER BY tarih DESC");
    while ($y = $yorumlar->fetch_assoc()):
  ?>
    <div class="border p-3 mb-2">
      <strong><?= htmlspecialchars($y['kullanici_adi']) ?></strong>
      <small class="text-muted float-right"><?= date("d.m.Y H:i", strtotime($y['tarih'])) ?></small>
      <p class="mb-0"><?= nl2br(htmlspecialchars($y['yorum'])) ?></p>
    </div>
  <?php endwhile; ?>

  <!-- Benzer Konserler -->
  <hr>
  <h4 class="mt-5">Benzer Konserler (<?= htmlspecialchars($konser['tur']) ?>)</h4>
  <div class="row">
    <?php
    $tur = $conn->real_escape_string($konser['tur']);
    $benzer = $conn->query("SELECT * FROM konserler WHERE tur = '$tur' AND id != $id LIMIT 20");
    if ($benzer->num_rows > 0):
      while ($b = $benzer->fetch_assoc()): 
    ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($b['resim_yolu']) ?>" class="card-img-top" alt="<?= htmlspecialchars($b['baslik']) ?>">
            <div class="card-body text-center">
              <h5 class="card-title"><?= htmlspecialchars($b['baslik']) ?></h5>
              <p><?= htmlspecialchars($b['aciklama']) ?></p>
              <a href="konser_detay.php?id=<?= $b['id'] ?>" class="btn btn-outline-primary btn-sm">Detay</a>
            </div>
          </div>
        </div>
    <?php
      endwhile;
    else:
        echo "<p class='ml-3'>Benzer konser bulunamadı.</p>";
    endif;
    ?>
  </div>
</div>

<!-- JS -->
<script>
  document.getElementById('biletAcButon')?.addEventListener('click', function () {
    const form = document.getElementById('biletFormu');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  });

  // Pop-up uyarısı
  const form = document.querySelector('form');
  form.addEventListener('submit', function (e) {
    const adet = parseInt(document.querySelector('input[name="adet"]').value);
    if (adet > 1) {
      e.preventDefault();
      alert('Sadece bir tane bilet alabilirsiniz!');
    }
  });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });
</script>
</body>
</html>

<?php $conn->close(); ?>
