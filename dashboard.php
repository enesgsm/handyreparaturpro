<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// İstatistikleri çek
try {
    // Toplam ve aktif onarım sayıları
    $stats = [
        'total_brands' => $db->query("SELECT COUNT(*) FROM brands WHERE status = 1")->fetchColumn(),
        'total_models' => $db->query("SELECT COUNT(*) FROM models WHERE status = 1")->fetchColumn(),
        'active_repairs' => $db->query("SELECT COUNT(*) FROM repairs_log WHERE status IN ('pending', 'in_progress')")->fetchColumn(),
        'total_repairs' => $db->query("SELECT COUNT(*) FROM repairs_log")->fetchColumn()
    ];

    // Marka bazında onarım sayıları
    $brand_stats = $db->query("
        SELECT b.name, COUNT(r.id) as repair_count 
        FROM brands b 
        LEFT JOIN repairs_log r ON b.id = r.brand_id 
        WHERE b.status = 1 
        GROUP BY b.id, b.name 
        ORDER BY repair_count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Aylık onarım istatistikleri
    $monthly_stats = $db->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status IN ('pending', 'in_progress') THEN 1 ELSE 0 END) as active
        FROM repairs_log
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Veritabanı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-semibold">Admin Panel</span>
                    <a href="dashboard.php" class="text-gray-900 font-medium">Dashboard</a>
                    <a href="brands.php" class="text-gray-600 hover:text-gray-900">Markalar</a>
                    <a href="models.php" class="text-gray-600 hover:text-gray-900">Modeller</a>
                    <a href="orders.php" class="text-gray-600 hover:text-gray-900">Siparişler</a>
                    <a href="repair_types.php" class="text-gray-600 hover:text-gray-900">Onarım Türleri</a>
                </div>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- İstatistik Kartları -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Toplam Marka -->
            <a href="brands.php" class="block">
                <div class="bg-white overflow-hidden shadow rounded-lg transition-shadow hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Toplam Marka</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_brands']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Toplam Model -->
            <a href="models.php" class="block">
                <div class="bg-white overflow-hidden shadow rounded-lg transition-shadow hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Toplam Model</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_models']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Aktif Onarımlar -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Aktif Onarımlar</dt>
                                <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['active_repairs']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toplam Onarım -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Toplam Onarım</dt>
                                <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_repairs']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafikler -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Marka Dağılımı -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Marka Dağılımı</h3>
                <div style="height: 400px; position: relative;">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>

            <!-- Aylık İstatistikler -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aylık İstatistikler</h3>
                <div style="height: 400px; position: relative;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Marka dağılımı grafiği
        const brandCtx = document.getElementById('brandChart').getContext('2d');
        const brandChart = new Chart(brandCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($brand_stats, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($brand_stats, 'repair_count')); ?>,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Aylık istatistikler grafiği
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
                datasets: [
                    {
                        label: 'Tamamlanan',
                        data: <?php echo json_encode(array_column($monthly_stats, 'completed')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    },
                    {
                        label: 'İptal Edilen',
                        data: <?php echo json_encode(array_column($monthly_stats, 'cancelled')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    },
                    {
                        label: 'Aktif',
                        data: <?php echo json_encode(array_column($monthly_stats, 'active')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    }
                ]
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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>