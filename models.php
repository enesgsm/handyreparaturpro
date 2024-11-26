<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$message = '';
$selected_brand = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// Mevcut markaları çek
$brands = $db->query("SELECT * FROM brands WHERE status = 1 ORDER BY name")->fetchAll();

// Seçilen markaya göre modelleri çek
$query = "SELECT m.id, m.name as model_name, m.sort_order, m.status, b.name as brand_name, b.id as brand_id 
         FROM models m 
         JOIN brands b ON m.brand_id = b.id";
if ($selected_brand > 0) {
    $query .= " WHERE m.brand_id = " . $selected_brand;
}
$query .= " ORDER BY b.name, m.sort_order DESC, m.name";
$models = $db->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-gray-800 hover:text-gray-600">← Dashboard</a>
                    <span class="ml-4 text-lg font-semibold">Model Yönetimi</span>
                </div>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Marka Filtresi -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex-grow">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Markaya Göre Filtrele</label>
                    <select name="brand" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" onchange="this.form.submit()">
                        <option value="0">Tüm Markalar</option>
                        <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand['id'] ?>" <?= ($selected_brand == $brand['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selected_brand > 0): ?>
                <div class="flex-none mt-6">
                    <a href="?brand=0" class="text-sm text-gray-600 hover:text-gray-900">Filtreyi Temizle</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Model Listesi -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium">Mevcut Modeller</h2>
                <a href="#" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Yeni Model Ekle</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-10"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sıralama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-models">
                        <?php foreach ($models as $model): ?>
                        <tr class="sortable-row" data-id="<?= $model['id'] ?>" data-brand="<?= $model['brand_id'] ?>">
                            <td class="px-2">
                                <span class="sort-handle cursor-move">☰</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($model['brand_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($model['model_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $model['status'] ? 'Aktif' : 'Pasif' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 sort-order">
                                <?= $model['sort_order'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-3">
                                    <a href="?edit=<?= $model['id'] ?>" class="text-blue-600 hover:text-blue-900">Düzenle</a>
                                    <a href="?delete=<?= $model['id'] ?>" 
                                       onclick="return confirm('Bu modeli silmek istediğinizden emin misiniz?')"
                                       class="text-red-600 hover:text-red-900">Sil</a>
                                </div>
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
        $("#sortable-models").sortable({
            handle: ".sort-handle",
            axis: "y",
            helper: function(e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($originals.eq(index).outerWidth());
                });
                return $helper;
            },
            update: function(event, ui) {
                let orders = {};
                $('.sortable-row').each(function(index) {
                    let newOrder = ($('.sortable-row').length - index) * 10;
                    orders[$(this).data('id')] = newOrder;
                    $(this).find('.sort-order').text(newOrder);
                });
                
                $.ajax({
                    url: 'update_order.php',
                    method: 'POST',
                    data: { orders: orders },
                    success: function(response) {
                        console.log('Sıralama güncellendi');
                    },
                    error: function() {
                        alert('Sıralama güncellenirken bir hata oluştu');
                        location.reload();
                    }
                });
            }
        }).disableSelection();
    });
    </script>
</body>
</html>