<?php
$sifre = '123456'; // Hash'lemek istediğiniz şifre
$hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

echo "Lütfen bu uzun metni kopyalayıp veritabanındaki şifre alanına yapıştırın: <br><br>";
echo "<strong>" . $hashed_sifre . "</strong>";
?>