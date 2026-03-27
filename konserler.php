<?php
session_start();
require 'db.php';

// Filtre ve arama işlemleri
$tur = isset($_GET['tur']) ? $_GET['tur'] : '';
$arama = isset($_GET['arama']) ? $_GET['arama'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Sayfa numarasını alıyoruz, default 1
$limit = 9; // Sayfa başına gösterilecek konser sayısı
$offset = ($page - 1) * $limit; // Offset hesaplama

$sql = "SELECT * FROM konserler WHERE 1";

if (!empty($tur)) {
    $sql .= " AND tur = '" . $conn->real_escape_string($tur) . "'";
}

if (!empty($arama)) {
    $sql .= " AND baslik LIKE '%" . $conn->real_escape_string($arama) . "%'";
}

$sql .= " ORDER BY tarih ASC LIMIT $limit OFFSET $offset"; // Sayfalandırma ekledik
$result = $conn->query($sql);

// Toplam konser sayısını almak için, tüm sonuçları sorguluyoruz
$sql_count = "SELECT COUNT(*) as total FROM konserler WHERE 1";
if (!empty($tur)) {
    $sql_count .= " AND tur = '" . $conn->real_escape_string($tur) . "'";
}
if (!empty($arama)) {
    $sql_count .= " AND baslik LIKE '%" . $conn->real_escape_string($arama) . "%'";
}
$count_result = $conn->query($sql_count);
$total_row = $count_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit); // Toplam sayfa sayısını hesaplıyoruz
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Konserler - Biletim</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="new_index_style.css?v=1.0.1">
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

<!-- Filtre ve Arama -->
<div class="container mt-5">
  <h2 class="text-center mb-4" >Tüm Konserler</h2>

  <form method="GET" class="form-inline justify-content-center mb-4">
    <select name="tur" class="form-control mr-2">
      <option value="">Tüm Türler</option>
      <option value="Pop" <?= $tur == 'Pop' ? 'selected' : '' ?>>Pop</option>
      <option value="Rock" <?= $tur == 'Rock' ? 'selected' : '' ?>>Rock</option>
      <option value="Jazz" <?= $tur == 'Jazz' ? 'selected' : '' ?>>Jazz</option>
      <option value="Elektronik" <?= $tur == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
    </select>

    <input type="text" name="arama" class="form-control mr-2" placeholder="Başlık ara..." value="<?= htmlspecialchars($arama) ?>">
    <button type="submit" class="btn btn-primary">Filtrele</button>
  </form>

  <!-- Konser Kartları -->
  <div class="row">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($konser = $result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="<?= $konser['resim_yolu'] ?>" class="card-img-top" alt="<?= $konser['baslik'] ?>">
            <div class="card-body text-center">
              <h5 class="card-title"><?= $konser['baslik'] ?></h5>
              <p class="card-text"><?= $konser['aciklama'] ?></p>
              <p class="text-muted"><small><?= date("d.m.Y", strtotime($konser['tarih'])) ?></small></p>
              <a href="konser_detay.php?id=<?= $konser['id'] ?>" class="btn btn-primary">Detayları Gör</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p>Aradığınız kriterlere uygun konser bulunamadı.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sayfalandırma -->
  <div class="d-flex justify-content-center">
    <nav aria-label="Sayfalar">
      <ul class="pagination">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?tur=<?= htmlspecialchars($tur) ?>&arama=<?= htmlspecialchars($arama) ?>&page=<?= $page - 1 ?>">Önceki</a>
          </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?tur=<?= htmlspecialchars($tur) ?>&arama=<?= htmlspecialchars($arama) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?tur=<?= htmlspecialchars($tur) ?>&arama=<?= htmlspecialchars($arama) ?>&page=<?= $page + 1 ?>">Sonraki</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });
</script>
</body>
</html>

<?php $conn->close(); ?>
