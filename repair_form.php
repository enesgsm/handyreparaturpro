<?php
session_start();
require_once 'config.php';

// Session kontrolü
if (!isset($_SESSION['repair_selection'])) {
    header('Location: index.php');
    exit();
}

$selection = $_SESSION['repair_selection'];

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $db->prepare("INSERT INTO orders (
            brand_id, model_id, repair_type_id, price,
            customer_name, customer_phone, customer_email, customer_address,
            imei_number, device_password, device_condition, special_notes,
            shipping_method, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        // Sipariş kaydı ve mail gönderme
        if ($stmt->execute([
            $selection['brand_id'],
            $selection['model_id'],
            $selection['repair_type_id'],
            $selection['price'],
            $_POST['customer_name'],
            $_POST['customer_phone'],
            $_POST['customer_email'],
            $_POST['customer_address'],
            $_POST['imei_number'],
            $_POST['device_password'],
            $_POST['device_condition'],
            $_POST['special_notes'],
            $_POST['shipping_method']
        ])) {
            $order_id = $db->lastInsertId();

            // Mail gönderme
            $to = "handyreparaturpro@gmail.com";
            $subject = "Yeni Onarım Siparişi - #RP" . str_pad($order_id, 6, "0", STR_PAD_LEFT);
            
            $message = "Yeni bir onarım siparişi alındı:\n\n";
            $message .= "Sipariş No: #RP" . str_pad($order_id, 6, "0", STR_PAD_LEFT) . "\n\n";
            $message .= "MÜŞTERİ BİLGİLERİ\n";
            $message .= "Ad Soyad: " . $_POST['customer_name'] . "\n";
            $message .= "Telefon: " . $_POST['customer_phone'] . "\n";
            $message .= "E-posta: " . $_POST['customer_email'] . "\n";
            $message .= "Adres: " . $_POST['customer_address'] . "\n\n";
            
            $message .= "CİHAZ BİLGİLERİ\n";
            $message .= "Marka: " . $selection['brand'] . "\n";
            $message .= "Model: " . $selection['model'] . "\n";
            $message .= "Arıza: " . $selection['repair'] . "\n";
            $message .= "IMEI: " . $_POST['imei_number'] . "\n";
            $message .= "Cihaz Durumu: " . $_POST['device_condition'] . "\n";
            $message .= "Cihaz Şifresi: " . $_POST['device_password'] . "\n\n";
            
            $message .= "SİPARİŞ DETAYLARI\n";
            $message .= "Fiyat: " . $selection['price'] . "€\n";
            $message .= "Kargo: " . strtoupper($_POST['shipping_method']) . "\n";
            $message .= "Özel Notlar: " . $_POST['special_notes'] . "\n";
            
            $headers = "From: noreply@handyreparaturpro.de\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($to, $subject, $message, $headers);

            unset($_SESSION['repair_selection']);
            header("Location: order_success.php?id=" . $order_id);
            exit();
        }
    } catch (PDOException $e) {
        $error = "Bir hata oluştu. Lütfen tekrar deneyin.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Onarım Talebi - <?php echo $site_settings['site_title']; ?></title>
   <style>
       * {
           margin: 0;
           padding: 0;
           box-sizing: border-box;
           font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
       }

       body {
           background-color: #f5f7fa;
           min-height: 100vh;
           padding: 20px;
       }

       .container {
           max-width: 800px;
           margin: 40px auto;
           background: white;
           border-radius: 20px;
           overflow: hidden;
           box-shadow: 0 2px 10px rgba(0,0,0,0.05);
       }

       .header {
           background: #20c997;
           color: white;
           padding: 25px 20px;
           text-align: center;
       }

       .form-container {
           padding: 30px;
       }

       .selection-summary {
           background: #f8f9fa;
           padding: 20px;
           border-radius: 8px;
           margin-bottom: 30px;
       }

       .summary-title {
           font-size: 18px;
           font-weight: 600;
           color: #333;
           margin-bottom: 15px;
       }

       .summary-item {
           display: flex;
           justify-content: space-between;
           margin-bottom: 10px;
           padding-bottom: 10px;
           border-bottom: 1px solid #e2e8f0;
       }

       .summary-item:last-child {
           border-bottom: none;
           margin-bottom: 0;
           padding-bottom: 0;
       }

       .summary-label {
           color: #666;
       }

       .summary-value {
           font-weight: 500;
           color: #333;
       }

       .form-section {
           margin-bottom: 30px;
       }

       .section-title {
           font-size: 18px;
           font-weight: 600;
           color: #333;
           margin-bottom: 20px;
       }

       .form-group {
           margin-bottom: 20px;
       }

       .form-group label {
           display: block;
           margin-bottom: 8px;
           color: #333;
           font-size: 14px;
       }

       .form-control {
           width: 100%;
           padding: 12px;
           border: 1px solid #e2e8f0;
           border-radius: 8px;
           font-size: 14px;
           color: #333;
       }

       .form-control:focus {
           outline: none;
           border-color: #20c997;
           box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.1);
       }

       textarea.form-control {
           min-height: 100px;
           resize: vertical;
       }

       .submit-button {
           display: block;
           width: 100%;
           padding: 16px;
           background: #20c997;
           color: white;
           border: none;
           border-radius: 8px;
           font-size: 16px;
           font-weight: 500;
           cursor: pointer;
           transition: background 0.3s ease;
       }

       .submit-button:hover {
           background: #1ba37e;
       }

       .error-message {
           background: #fee2e2;
           border: 1px solid #ef4444;
           color: #b91c1c;
           padding: 12px;
           border-radius: 8px;
           margin-bottom: 20px;
       }

       @media (max-width: 640px) {
           .container {
               margin: 20px auto;
           }

           .form-container {
               padding: 20px;
           }
       }
   </style>
</head>
<body>
   <div class="container">
       <div class="header">
           <h1>Onarım Talebi Oluştur</h1>
           <p>Lütfen aşağıdaki formu doldurun</p>
       </div>

       <div class="form-container">
           <?php if (isset($error)): ?>
               <div class="error-message">
                   <?php echo $error; ?>
               </div>
           <?php endif; ?>

           <!-- Seçim Özeti -->
           <div class="selection-summary">
               <h2 class="summary-title">Seçilen Onarım Detayları</h2>
               <div class="summary-item">
                   <span class="summary-label">Marka:</span>
                   <span class="summary-value"><?php echo htmlspecialchars($selection['brand']); ?></span>
               </div>
               <div class="summary-item">
                   <span class="summary-label">Model:</span>
                   <span class="summary-value"><?php echo htmlspecialchars($selection['model']); ?></span>
               </div>
               <div class="summary-item">
                   <span class="summary-label">Arıza Türü:</span>
                   <span class="summary-value"><?php echo htmlspecialchars($selection['repair']); ?></span>
               </div>
               <div class="summary-item">
                   <span class="summary-label">Tahmini Fiyat:</span>
                   <span class="summary-value" style="color: #20c997; font-weight: 600;">
                       <?php echo htmlspecialchars($selection['price']); ?>€
                   </span>
               </div>
           </div>

           <form method="POST" action="">
               <!-- Müşteri Bilgileri -->
               <div class="form-section">
                   <h3 class="section-title">Kişisel Bilgiler</h3>
                   <div class="form-group">
                       <label for="customer_name">Ad Soyad *</label>
                       <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                   </div>
                   <div class="form-group">
                       <label for="customer_phone">Telefon Numarası *</label>
                       <input type="tel" id="customer_phone" name="customer_phone" class="form-control" required>
                   </div>
                   <div class="form-group">
                       <label for="customer_email">E-posta Adresi *</label>
                       <input type="email" id="customer_email" name="customer_email" class="form-control" required>
                   </div>
                   <div class="form-group">
                       <label for="customer_address">Adres *</label>
                       <textarea id="customer_address" name="customer_address" class="form-control" required></textarea>
                   </div>
               </div>

               <!-- Cihaz Bilgileri -->
               <div class="form-section">
                   <h3 class="section-title">Cihaz Bilgileri</h3>
                   <div class="form-group">
                       <label for="imei_number">IMEI Numarası</label>
                       <input type="text" id="imei_number" name="imei_number" class="form-control" 
                              pattern="[0-9]*" minlength="15" maxlength="15" 
                              title="Lütfen 15 haneli IMEI numarasını girin">
                       <small style="color: #666; margin-top: 4px; display: block;">
                           *123# tuşlayarak IMEI numaranızı öğrenebilirsiniz
                       </small>
                   </div>
                   <div class="form-group">
                       <label for="device_password">Cihaz Şifresi</label>
                       <input type="text" id="device_password" name="device_password" class="form-control">
                       <small style="color: #666; margin-top: 4px; display: block;">
                           Opsiyonel: Cihazınızın ekran kilidini açmamız gerekirse
                       </small>
                   </div>
                   <div class="form-group">
                       <label for="device_condition">Cihazın Mevcut Durumu</label>
                       <textarea id="device_condition" name="device_condition" class="form-control"
                                placeholder="Örn: Ekranda çatlak var, arka kapak çizik, vs."></textarea>
                   </div>
               </div>

               <!-- Kargo Tercihi -->
               <div class="form-section">
                   <h3 class="section-title">Kargo Bilgileri</h3>
                   <div class="form-group">
                       <label for="shipping_method">Kargo Tercihi *</label>
                       <select id="shipping_method" name="shipping_method" class="form-control" required>
                           <option value="">Seçiniz</option>
                           <option value="dhl">DHL</option>
                           <option value="dpd">DPD</option>
                           <option value="ups">UPS</option>
                       </select>
                   </div>
               </div>

               <!-- Özel Notlar -->
               <div class="form-section">
                   <h3 class="section-title">Ek Bilgiler</h3>
                   <div class="form-group">
                       <label for="special_notes">Özel Notlar</label>
                       <textarea id="special_notes" name="special_notes" class="form-control"
                                placeholder="Bize iletmek istediğiniz ek bilgiler..."></textarea>
                   </div>
               </div>

               <button type="submit" class="submit-button">
                   Onarım Talebini Gönder
               </button>
           </form>
       </div>
   </div>

   <script>
       // IMEI validation
       document.getElementById('imei_number').addEventListener('input', function(e) {
           this.value = this.value.replace(/[^0-9]/g, '');
       });

       // Form validation
       document.querySelector('form').addEventListener('submit', function(e) {
           const phone = document.getElementById('customer_phone').value;
           if (!phone.match(/^\+?[0-9\s-]{10,}$/)) {
               alert('Lütfen geçerli bir telefon numarası girin');
               e.preventDefault();
           }
       });
   </script>
</body>
</html>