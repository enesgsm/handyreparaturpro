<?php
session_start();
require_once 'config.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$message = '';

// Yeni fiyat ekleme veya güncelleme
if (isset($_POST['update_price'])) {
    $model_id = (int)$_POST['model_id'];
    $repair_type_id = (int)$_POST['repair_type_id'];
    $price = (float)$_POST['price'];

    try {
        $stmt = $db->prepare("INSERT INTO prices (model_id, repair_type_id, price) 
                             VALUES (?, ?, ?) 
                             ON DUPLICATE KEY UPDATE price = ?");
        $stmt->execute([$model_id, $repair_type_id, $price, $price]);
        $message = "Fiyat başarıyla güncellendi!";
    } catch(PDOException $e) {
        $message = "Hata: Fiyat güncellenemedi.";
    }
}

// Markaları al
$brands = $db->query("SELECT * FROM brands ORDER BY name")->fetchAll();

// Tamir türlerini al
$repair_types = $db->query("SELECT * FROM repair_types ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiyat Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex">
                    <a href="dashboard.php" class="text-gray-800 hover:text-gray-600">← Dashboard</a>
                    <span class="ml-4 text-lg font-semibold">Fiyat Yönetimi</span>
                </div>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Fiyat Ekleme/Güncelleme Formu -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">Fiyat Ekle/Güncelle</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Marka</label>
                    <select id="brand_select" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Marka Seçin</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Model</label>
                    <select name="model_id" id="model_select" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Önce Marka Seçin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tamir Türü</label>
                    <select name="repair_type_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Tamir Türü Seçin</option>
                        <?php foreach ($repair_types as $repair): ?>
                            <option value="<?php echo $repair['id']; ?>">
                                <?php echo htmlspecialchars($repair['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fiyat (€)</label>
                    <input type="number" name="price" required min="0" step="0.01"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <button type="submit" name="update_price" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Fiyatı Güncelle
                </button>
            </form>
        </div>

        <!-- Mevcut Fiyatlar Tablosu -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium mb-4">Mevcut Fiyatlar</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tamir Türü</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiyat (€)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $query = "SELECT b.name as brand_name, m.name as model_name, 
                                        rt.name as repair_name, p.price
                                 FROM prices p
                                 JOIN models m ON p.model_id = m.id
                                 JOIN brands b ON m.brand_id = b.id
                                 JOIN repair_types rt ON p.repair_type_id = rt.id
                                 ORDER BY b.name, m.name, rt.name";
                        $prices = $db->query($query)->fetchAll();
                        foreach ($prices as $price):
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($price['brand_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($price['model_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($price['repair_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo number_format($price['price'], 2); ?>€
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Marka seçildiğinde modelleri getir
        $('#brand_select').change(function() {
            const brandId = $(this).val();
            const modelSelect = $('#model_select');
            
            modelSelect.empty().append('<option value="">Model Seçin</option>');
            
            if (brandId) {
                $.getJSON('get_models.php', {brand_id: brandId}, function(models) {
                    models.forEach(model => {
                        modelSelect.append(`<option value="${model.id}">${model.name}</option>`);
                    });
                });
            }
        });
    });
    </script>
</body>
</html>