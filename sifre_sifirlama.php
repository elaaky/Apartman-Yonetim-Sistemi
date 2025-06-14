<?php
require_once 'includes/header.php'; // veya header_guest.php
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card mt-5">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Şifre Sıfırlama</h3>
                <p class="text-center text-muted">Sisteme kayıtlı email adresinizi girin. Yönetici tarafından size yeni bir şifre atanacaktır.</p>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">Şifre sıfırlama talebiniz alınmıştır. Yönetici en kısa sürede sizinle iletişime geçecektir.</div>
                <?php endif; ?>

                <form action="sifre_sifirlama_action.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Adresiniz</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-info">Sıfırlama Talebi Gönder</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="login.php">Giriş ekranına geri dön</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>