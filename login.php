<?php
// Gerekli dosyaları dahil et
require_once 'includes/header.php'; // Misafirler için farklı bir header yapabiliriz
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Yönetici Girişi</h3>

                <?php
                if (isset($_GET['error'])) {
                    $error_msg = '';
                    if ($_GET['error'] == 'emptyfields') {
                        $error_msg = 'Lütfen tüm alanları doldurun.';
                    } elseif ($_GET['error'] == 'wrongpassword') {
                        $error_msg = 'Hatalı kullanıcı adı veya şifre.';
                    } elseif ($_GET['error'] == 'nouser') {
                        $error_msg = 'Böyle bir kullanıcı bulunamadı.';
                    }

                    if ($error_msg) {
                        echo '<div class="alert alert-danger">' . $error_msg . '</div>';
                    }
                }
                ?>

                <!-- Hata mesajları burada gösterilecek -->

                <form action="login_action.php" method="POST">
                    <div class="mb-3">
                        <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required>
                    </div>
                    <div class="mb-3">
                        <label for="sifre" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="sifre_sifirlama.php">Şifremi Unuttum</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<?php
// Sayfa alt kısmı
require_once 'includes/footer.php';
?>