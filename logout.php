<?php
session_start();
session_unset(); // Tüm session değişkenlerini temizle
session_destroy(); // Session'ı bitir
header("Location:index.php"); // Anasayfaya yönlendir
exit();
?>
