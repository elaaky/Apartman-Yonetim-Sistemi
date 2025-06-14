<?php
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // E-postanın veritabanında olup olmadığını kontrol et
    $stmt = $db->prepare("SELECT * FROM yöneticiler WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Kullanıcı bulundu. Gerçek senaryoda burada e-posta gönderilir.
        // Biz sadece talebin alındığını belirterek geri yönlendiriyoruz.
        // İleri seviye: Buraya, yeni bir "sifirlama_talepleri" tablosuna kayıt ekleme kodu yazılabilir.
    }

    // E-posta bulunsa da bulunmasa da aynı mesajı göstererek
    // kötü niyetli kullanıcıların hangi e-postaların kayıtlı olduğunu öğrenmesini engelliyoruz.
    header("Location: sifre_sifirlama.php?status=success");
    exit();
}
