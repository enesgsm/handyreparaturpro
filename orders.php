<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Siparişleri çek
try {
    $query = "SELECT o.*, 
                     CONCAT('RP', LPAD(o.id, 6, '0')) as order_number,
                     b.name as brand_name, 
                     m.name as model_name, 
                     r.name as repair_name
              FROM orders o
              LEFT JOIN brands b ON o.brand_id = b.id
              LEFT JOIN models m ON o.model_id = m.id
              LEFT JOIN repair_types r ON o.repair_type_id = r.id
              ORDER BY o.created_at DESC";
              
    $orders = $db->query($query)->fetchAll();
} catch(PDOException $e) {
    $error = "Siparişler yüklenirken bir hata oluştu.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişler - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-semibold">Admin Panel</span>
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="brands.php" class="text-gray-600 hover:text-gray-900">Markalar</a>
                    <a href="models.php" class="text-gray-600 hover:text-gray-900">Modeller</a>
                    <a href="orders.php" class="text-gray-900 font-medium">Siparişler</a>
                    <a href="repair_types.php" class="text-gray-600 hover:text-gray-900">Onarım Türleri</a>
                </div>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-medium">Siparişler</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cihaz</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Onarım</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $order['order_number']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($order['brand_name'] . ' ' . $order['model_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['repair_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($order['price'], 2); ?>€
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select class="text-sm border-gray-300 rounded-md shadow-sm status-select" 
                                            data-order-id="<?php echo $order['id']; ?>">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Beklemede</option>
                                        <option value="received" <?php echo $order['status'] == 'received' ? 'selected' : ''; ?>>Teslim Alındı</option>
                                        <option value="in_repair" <?php echo $order['status'] == 'in_repair' ? 'selected' : ''; ?>>Onarımda</option>
                                        <option value="testing" <?php echo $order['status'] == 'testing' ? 'selected' : ''; ?>>Test Ediliyor</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Kargoya Verildi</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Tamamlandı</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>İptal Edildi</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button onclick="showOrderDetails(<?php echo $order['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        Detay
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // Durum değişikliği
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Sipariş durumu güncellendi',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: 'Sipariş durumu güncellenemedi'
                    });
                }
            });
        });
    });

    function showOrderDetails(orderId) {
        fetch(`get_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    Swal.fire({
                        title: `Sipariş Detayı #${order.order_number}`,
                        html: `
                            <div class="text-left">
                                <p class="mb-2"><strong>Müşteri:</strong> ${order.customer_name}</p>
                                <p class="mb-2"><strong>Telefon:</strong> ${order.customer_phone}</p>
                                <p class="mb-2"><strong>E-posta:</strong> ${order.customer_email}</p>
                                <p class="mb-2"><strong>Adres:</strong> ${order.customer_address}</p>
                                <p class="mb-2"><strong>IMEI:</strong> ${order.imei_number}</p>
                                <p class="mb-2"><strong>Cihaz Şifresi:</strong> ${order.device_password}</p>
                                <p class="mb-2"><strong>Cihaz Durumu:</strong> ${order.device_condition}</p>
                                <p class="mb-2"><strong>Kargo:</strong> ${order.shipping_method}</p>
                                <p class="mb-2"><strong>Özel Notlar:</strong> ${order.special_notes}</p>
                            </div>
                        `,
                        width: 600
                    });
                }
            });
    }
    </script>
</body>
</html>