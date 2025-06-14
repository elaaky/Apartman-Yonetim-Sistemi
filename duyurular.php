<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

try {
    $sql = "SELECT d.*, CONCAT(y.Ad, ' ', y.Soyad) as YoneticiAdi 
            FROM duyurular d 
            LEFT JOIN yöneticiler y ON d.YoneticiID = y.YoneticiID 
            ORDER BY d.Tarih DESC";
    $stmt = $db->query($sql);
    $duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Duyuru verileri çekilirken hata oluştu: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Yeni Duyuru Ekleme Formu -->
    <div class="col-md-4 mb-4">
        <h3>Yeni Duyuru Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="duyuru_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="baslik" class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="baslik" name="baslik" required>
                    </div>
                    <div class="mb-3">
                        <label for="icerik" class="form-label">İçerik</label>
                        <textarea class="form-control" id="icerik" name="icerik" rows="8" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Duyuru Yayınla</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Duyurular Listesi -->
    <div class="col-md-8">
        <h3>Yayınlanmış Duyurular</h3>
        <div class="list-group">
            <?php if (count($duyurular) > 0): ?>
                <?php foreach ($duyurular as $duyuru): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($duyuru['Baslik']); ?></h5>
                            <small><?php echo date('d.m.Y H:i', strtotime($duyuru['Tarih'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($duyuru['Icerik'])); ?></p>
                        <small class="text-muted">Yayınlayan: <?php echo htmlspecialchars($duyuru['YoneticiAdi']); ?></small>
                        <div class="mt-2">
                            <button onclick="confirmDelete(<?php echo $duyuru['DuyuruID']; ?>)" class="btn btn-sm btn-danger">Sil</button>
                            <form id="delete-form-<?php echo $duyuru['DuyuruID']; ?>" action="duyuru_action.php" method="POST" class="d-none">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="duyuru_id" value="<?php echo $duyuru['DuyuruID']; ?>">
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Henüz yayınlanmış bir duyuru yok.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function confirmDelete(duyuruId) {
        if (confirm("Bu duyuruyu silmek istediğinizden emin misiniz?")) {
            document.getElementById('delete-form-' + duyuruId).submit();
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>