<?php session_start(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biletim - Etkinlikler</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css">

  <!-- Bootstrap & AOS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css">

  <!-- Custom Style -->
  <link rel="stylesheet" href="new_index_style.css?v=1.0.2">

</head>
<body>

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

<!-- Hero -->
<section class="hero-section text-center">
  <div class="container">
    <h1>Müziğin Ritmini Yakala!</h1>
    <p>Şehrindeki en iyi konserler burada.</p>
  </div>
</section>

<!-- Büyük Kartlar -->
<section class="top-concerts">
  <div class="card-row">
    <?php
      $etkinlikler = [
        ['img' => 'images/resim1.webp', 'title' => 'Bahar Festivali', 'desc' => 'Açık havada müzik keyfi!'],
        ['img' => 'images/resim3.webp', 'title' => 'Kış Konseri', 'desc' => 'Sıcak melodilerle ruhunu ısıt!'],
        ['img' => 'images/i.webp', 'title' => 'Yaz Partisi', 'desc' => 'Deniz kenarında dans zamanı!']
      ];
      foreach ($etkinlikler as $e): ?>
        <div class="big-card" onclick="openModal('<?= $e['title'] ?>', '<?= $e['desc'] ?>')">
          <img src="<?= $e['img'] ?>" alt="<?= $e['title'] ?>">
          <div class="card-info">
            <h5><?= $e['title'] ?></h5>
            <p><?= $e['desc'] ?></p>
            <a href="#" class="btn-bilet-al">Bilet Al</a>
          </div>
        </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Scroll Kartlar -->
<section class="scroll-section">
  <div class="container-fluid px-5">
    <h2 class="text-center mb-5">Yaklaşan Etkinlikler</h2>
    
    <div class="scroll-wrapper-container">
      <div class="scrolling-wrapper">
        <div class="card" onclick="openModal('Rock Festivali', 'Unutulmaz bir rock gecesi!')">
          <img src="images/resim1.webp" alt="Rock Festivali">
          <div class="card-body text-center">
            <h5 class="card-title">Rock Festivali</h5>
            <p class="card-text">Enerji dolu gece!</p>
            <a href="#" class="btn-bilet-al">Bilet Al</a>
          </div>
        </div>
        <div class="card" onclick="openModal('Jazz Gecesi', 'Caz melodileriyle keyifli bir akşam!')">
          <img src="images/i (1).webp" alt="Jazz Gecesi">
          <div class="card-body text-center">
            <h5 class="card-title">Jazz Gecesi</h5>
            <p class="card-text">Caz keyfi burada!</p>
            <a href="#" class="btn-bilet-al">Bilet Al</a>
          </div>
        </div>
        <div class="card" onclick="openModal('Elektronik Dans', 'Sabaha kadar sürecek dans partisi!')">
          <img src="images/i (2).webp" alt="Elektronik Dans">
          <div class="card-body text-center">
            <h5 class="card-title">Elektronik Dans</h5>
            <p class="card-text">Sabaha kadar dans!</p>
            <a href="#" class="btn-bilet-al">Bilet Al</a>
          </div>
        </div>
        <div class="card" onclick="openModal('Pop Konseri', 'Ünlü pop yıldızları sahnede!')">
          <img src="images/resim2.webp" alt="Pop Konseri">
          <div class="card-body text-center">
            <h5 class="card-title">Pop Konseri</h5>
            <p class="card-text">Pop müzik şöleni!</p>
            <a href="#" class="btn-bilet-al">Bilet Al</a>
          </div>
        </div>
      </div>

      <!-- Sağ alta buton -->
      <div class="text-right mt-3 mr-3">
        <a href="konserler.php" class="btn-tumunu-goster">Tümünü Göster</a>
      </div>
    </div>
  </div>
</section>


<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalLabel">Etkinlik Detayı</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="eventModalContent"></div>
    </div>
  </div>
</div>

<!-- Scripts -->
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
<!-- Footer -->
<footer class="footer mt-5">
  <div class="container-fluid px-5 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">

    <p class="mb-2 mb-md-0">&copy; 2025 Biletim. Tüm Hakları Saklıdır.</p>

    <div class="social-icons">
      <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
      <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
      <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
      <a href="#" target="_blank"><i class="fab fa-youtube"></i></a>
    </div>

  </div>
</footer>

</body>

</html>
