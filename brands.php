<?php
session_start();
require_once 'config.php';

// Upload dizinini kontrol et ve oluştur
$upload_dir = 'uploads/logos';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
chmod($upload_dir, 0777);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$message = '';

// Yeni marka ekleme
if (isset($_POST['add_brand'])) {
    $name = trim($_POST['brand_name']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (!empty($name)) {
        try {
            // Logo yükleme işlemi
            $logo_path = '';
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['logo']['size'] > $max_size) {
                    throw new Exception("Dosya boyutu çok büyük (Maksimum 5MB)");
                }

                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['logo']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception("Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir");
                }

                $new_filename = uniqid() . '.' . $ext;
                $upload_path = $upload_dir . '/' . $new_filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $logo_path = $upload_path;
                } else {
                    throw new Exception("Dosya yüklenirken bir hata oluştu");
                }
            }
            
            $stmt = $db->prepare("INSERT INTO brands (name, logo, status, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $logo_path, $status, 0]);
            $message = "Marka başarıyla eklendi!";
        } catch(Exception $e) {
            $message = "Hata: " . $e->getMessage();
        }
    }
}

// Marka güncelleme
if (isset($_POST['update_brand'])) {
    $id = (int)$_POST['brand_id'];
    $name = trim($_POST['brand_name']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    try {
        $current_brand = $db->prepare("SELECT logo FROM brands WHERE id = ?");
        $current_brand->execute([$id]);
        $brand = $current_brand->fetch();
        $logo_path = $brand['logo'];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($_FILES['logo']['size'] > $max_size) {
                throw new Exception("Dosya boyutu çok büyük (Maksimum 5MB)");
            }

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir");
            }

            $new_filename = uniqid() . '.' . $ext;
            $upload_path = $upload_dir . '/' . $new_filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                // Eski logoyu sil
                if (!empty($logo_path) && file_exists($logo_path)) {
                    unlink($logo_path);
                }
                $logo_path = $upload_path;
            } else {
                throw new Exception("Dosya yüklenirken bir hata oluştu");
            }
        }
        
        $stmt = $db->prepare("UPDATE brands SET name = ?, logo = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $logo_path, $status, $id]);
        $message = "Marka başarıyla güncellendi!";
    } catch(Exception $e) {
        $message = "Hata: " . $e->getMessage();
    }
}

// Marka silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $brand = $db->prepare("SELECT logo FROM brands WHERE id = ?");
        $brand->execute([$id]);
        $result = $brand->fetch();
        
        if ($result && $result['logo'] && file_exists($result['logo'])) {
            unlink($result['logo']);
        }
        
        $db->beginTransaction();
        
        $stmt = $db->prepare("DELETE FROM models WHERE brand_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        
        $db->commit();
        $message = "Marka ve ilgili modeller başarıyla silindi!";
    } catch(Exception $e) {
        $db->rollBack();
        $message = "Hata: " . $e->getMessage();
    }
}

// Markaları çek
$brands = $db->query("SELECT * FROM brands ORDER BY sort_order DESC, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marka Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Logo düzenleme için gerekli kütüphaneler -->
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-gray-800 hover:text-gray-600">← Dashboard</a>
                    <span class="ml-4 text-lg font-semibold">Marka Yönetimi</span>
                </div>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <?php if ($message): ?>
        <div id="alert-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Yeni Marka Ekleme Formu -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">Yeni Marka Ekle</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marka Adı</label>
                        <input type="text" name="brand_name" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Logo</label>
                        <div class="mt-1 flex items-center">
                            <div class="logo-preview-wrapper w-20 h-20 border-2 border-gray-300 border-dashed rounded-lg flex items-center justify-center overflow-hidden">
                                <img id="logo-preview" src="" class="hidden max-h-full">
                                <span class="text-gray-400">Logo</span>
                            </div>
                            <div class="ml-4 flex flex-col">
                                <button type="button" id="logo-upload-btn" 
                                        class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                                    Logo Seç
                                </button>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF (max. 5MB)</p>
                            </div>
                            <input type="file" id="logo-input" name="logo" accept="image/*" class="hidden">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="status" checked class="rounded border-gray-300">
                        <span class="ml-2">Aktif</span>
                    </label>
                </div>
                <button type="submit" name="add_brand" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Marka Ekle
                </button>
            </form>
        </div>

        <!-- Marka Listesi -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium mb-4">Mevcut Markalar</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-10"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Logo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka Adı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sıralama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-brands">
                        <?php foreach ($brands as $brand): ?>
                        <tr class="sortable-row" data-id="<?php echo $brand['id']; ?>">
                            <td class="px-2">
                                <span class="sort-handle cursor-move">☰</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($brand['logo'] && file_exists($brand['logo'])): ?>
                                <img src="<?php echo htmlspecialchars($brand['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($brand['name']); ?>" 
                                     class="h-10 w-10 object-contain">
                                <?php else: ?>
                                <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-500 text-xs"><?php echo substr($brand['name'], 0, 2); ?></span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $brand['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $brand['status'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 sort-order">
                                <?php echo $brand['sort_order']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-3">
                                    <a href="#" onclick="editBrand(<?php echo $brand['id']; ?>)"
                                       class="text-blue-600 hover:text-blue-900">Düzenle</a>
                                    <a href="#" onclick="deleteBrand(<?php echo $brand['id']; ?>)"
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
    <!-- Logo Düzenleme Modalı -->
    <div id="logo-modal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" id="close-modal-btn" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Kapat</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Logo Düzenle
                        </h3>
                        <div class="mt-4">
                            <div id="logo-cropper" class="max-h-96"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" id="crop-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Kırp ve Kaydet
                    </button>
                    <button type="button" id="cancel-crop-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let cropper = null;
    let logoChanged = false;

    // Logo seçme butonu
    document.getElementById('logo-upload-btn').addEventListener('click', function() {
        document.getElementById('logo-input').click();
    });

    // Logo input değişimi
    document.getElementById('logo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: 'Dosya boyutu 5MB\'dan büyük olamaz!'
                });
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-modal').classList.remove('hidden');
                
                const cropperContainer = document.getElementById('logo-cropper');
                cropperContainer.innerHTML = `<img src="${e.target.result}" style="max-width: 100%;">`;
                
                const image = cropperContainer.querySelector('img');
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Sıralamanın güncellenmesi
    $("#sortable-brands").sortable({
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
                url: 'update_brand_order.php',
                method: 'POST',
                data: { orders: orders },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı',
                        text: 'Sıralama güncellendi',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: 'Sıralama güncellenirken bir hata oluştu'
                    });
                }
            });
        }
    }).disableSelection();

    // Logo kırpma işlemi
    document.getElementById('crop-btn').addEventListener('click', function() {
        if (!cropper) return;
        
        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400
        });
        
        const preview = document.getElementById('logo-preview');
        preview.src = canvas.toDataURL();
        preview.classList.remove('hidden');
        preview.nextElementSibling.classList.add('hidden');
        
        logoChanged = true;
        
        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('logo', blob, 'logo.png');
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([blob], 'logo.png'));
            document.getElementById('logo-input').files = dataTransfer.files;
        });
        
        closeLogoModal();
    });

    // Modal kapatma işlemleri
    function closeLogoModal() {
        document.getElementById('logo-modal').classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    document.getElementById('close-modal-btn').addEventListener('click', closeLogoModal);
    document.getElementById('cancel-crop-btn').addEventListener('click', closeLogoModal);

    // Marka silme fonksiyonu
    function deleteBrand(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu marka ve ilgili tüm modeller silinecek. Bu işlem geri alınamaz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?delete=${id}`;
            }
        });
    }

    // ESC tuşu ile modal kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLogoModal();
        }
    });
    </script>
</body>
</html>