<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

try {
    // Finansal Özet
    $toplam_borc = $db->query("SELECT SUM(Tutar) FROM aidatlar WHERE OdendiMi = 0")->fetchColumn() ?? 0;
    $bu_ay_toplanan = $db->query("SELECT SUM(Tutar) FROM aidatlar WHERE OdendiMi = 1 AND DATE_FORMAT(OdemeTarihi, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn() ?? 0;
    $bu_ay_gider = $db->query("SELECT SUM(Tutar) FROM giderler WHERE DATE_FORMAT(Tarih, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn() ?? 0;
    $toplam_gelir_genel = $db->query("SELECT SUM(Tutar) FROM aidatlar WHERE OdendiMi = 1")->fetchColumn() ?? 0;
    $toplam_gider_genel = $db->query("SELECT SUM(Tutar) FROM giderler")->fetchColumn() ?? 0;
    $kasa_durumu = $toplam_gelir_genel - $toplam_gider_genel;

    // Diğer Veriler
    $son_duyurular_stmt = $db->query("SELECT * FROM duyurular ORDER BY Tarih DESC LIMIT 4");
    $son_duyurular = $son_duyurular_stmt->fetchAll(PDO::FETCH_ASSOC);
    $sql_blok_ozet = "SELECT b.BinaAdi, COUNT(DISTINCT d.DaireID) as daire_sayisi, COUNT(s.SakinID) as sakin_sayisi FROM binalar b LEFT JOIN daireler d ON b.BinaID = d.BinaID LEFT JOIN sakinler s ON d.DaireID = s.DaireID GROUP BY b.BinaID, b.BinaAdi ORDER BY b.BinaAdi";
    $stmt_blok_ozet = $db->query($sql_blok_ozet);
    $blok_ozetleri = $stmt_blok_ozet->fetchAll(PDO::FETCH_ASSOC);

    // Grafik için Veri
    $aylar_tr = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
    $aylar = [];
    $gelirler = [];
    $giderler = [];
    for ($i = 5; $i >= 0; $i--) {
        $ay_timestamp = strtotime("-$i months");
        $ay_no = date('n', $ay_timestamp) - 1;
        $ay_str = $aylar_tr[$ay_no] . " " . date('Y', $ay_timestamp);
        $ay_sorgu = date('Y-m', $ay_timestamp);
        $gelir_sql = "SELECT SUM(Tutar) FROM aidatlar WHERE OdendiMi = 1 AND DATE_FORMAT(OdemeTarihi, '%Y-%m') = ?";
        $gelir_stmt = $db->prepare($gelir_sql);
        $gelir_stmt->execute([$ay_sorgu]);
        $gelir = $gelir_stmt->fetchColumn() ?? 0;
        $gider_sql = "SELECT SUM(Tutar) FROM giderler WHERE DATE_FORMAT(Tarih, '%Y-%m') = ?";
        $gider_stmt = $db->prepare($gider_sql);
        $gider_stmt->execute([$ay_sorgu]);
        $gider = $gider_stmt->fetchColumn() ?? 0;
        $aylar[] = $ay_str;
        $gelirler[] = $gelir;
        $giderler[] = $gider;
    }
} catch (PDOException $e) {
    die("Dashboard verileri çekilirken bir hata oluştu: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Ana Panel</h1>
    </div>

    <!-- Üstteki 4'lü Finansal Özet Kartları -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Toplam Borç</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($toplam_borc, 2, ',', '.'); ?> TL</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-calendar-x fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Bu Ay Toplanan</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($bu_ay_toplanan, 2, ',', '.'); ?> TL</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-currency-dollar fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Bu Ayki Gider</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($bu_ay_gider, 2, ',', '.'); ?> TL</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clipboard-data fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Kasa Durumu (Net)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo number_format($kasa_durumu, 2, ',', '.'); ?> TL</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-wallet2 fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!--                   YENİ DÜZENLEME BURADA BAŞLIYOR               -->
    <!-- ============================================================== -->
    <div class="row">
        <!-- Bloklara Göre Genel Durum (Tam Genişlik) -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Bloklara Göre Genel Durum</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Bloklar yan yana dizilecek -->
                        <?php foreach ($blok_ozetleri as $ozet): ?>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="p-3 border rounded h-100">
                                    <h5 class="card-title text-muted"><?php echo htmlspecialchars($ozet['BinaAdi']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span><i class="bi bi-building"></i> Toplam Daire</span>
                                        <span class="badge bg-primary fs-6"><?php echo $ozet['daire_sayisi']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span><i class="bi bi-people-fill"></i> Toplam Sakin</span>
                                        <span class="badge bg-info fs-6"><?php echo $ozet['sakin_sayisi']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Duyurular (Tam Genişlik) -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-megaphone-fill"></i> Son Duyurular</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    <?php if (count($son_duyurular) > 0): foreach ($son_duyurular as $duyuru): ?>
                            <a href="#" class="list-group-item list-group-item-action duyuru-item" data-bs-toggle="modal" data-bs-target="#duyuruDetayModal" data-baslik="<?php echo htmlspecialchars($duyuru['Baslik']); ?>" data-icerik="<?php echo nl2br(htmlspecialchars(trim($duyuru['Icerik']))); ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold text-truncate"><?php echo htmlspecialchars($duyuru['Baslik']); ?></h6>
                                    <small class="text-muted flex-shrink-0 ms-2"><?php echo date('d.m.Y', strtotime($duyuru['Tarih'])); ?></small>
                                </div>
                                <p class="mb-1 small text-muted text-truncate"><?php echo htmlspecialchars(trim($duyuru['Icerik'])); ?></p>
                            </a>
                        <?php endforeach;
                    else: ?>
                        <div class="list-group-item">
                            <p class="text-muted mb-0">Henüz yayınlanmış bir duyuru yok.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grafik (Tam Genişlik) -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-line-fill"></i> Son 6 Aylık Gelir-Gider Analizi</h6>
                </div>
                <div class="card-body">
                    <canvas id="gelirGiderGrafigi" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ve JS kodları aynı kalacak -->
<div class="modal fade" id="duyuruDetayModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duyuruDetayModalLabel">Duyuru Detayı</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="duyuruIcerik"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button></div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var duyuruModal = document.getElementById('duyuruDetayModal');
        if (duyuruModal) {
            duyuruModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                duyuruModal.querySelector('.modal-title').textContent = button.getAttribute('data-baslik');
                duyuruModal.querySelector('.modal-body').innerHTML = button.getAttribute('data-icerik');
            });
        }
        const ctx = document.getElementById('gelirGiderGrafigi');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($aylar); ?>,
                    datasets: [{
                        label: 'Gelirler (TL)',
                        data: <?php echo json_encode($gelirler); ?>,
                        backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    }, {
                        label: 'Giderler (TL)',
                        data: <?php echo json_encode($giderler); ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>