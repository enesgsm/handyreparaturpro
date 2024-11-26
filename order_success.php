<?php
session_start();
require_once 'config.php';

// Sipariş ID kontrolü
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['id'];

try {
    // Sipariş bilgilerini çek
    $stmt = $db->prepare("
        SELECT o.*, 
               b.name as brand_name, 
               m.name as model_name, 
               r.name as repair_name,
               CONCAT('RP', LPAD(o.id, 6, '0')) as order_number
        FROM orders o
        JOIN brands b ON o.brand_id = b.id
        JOIN models m ON o.model_id = m.id
        JOIN repair_types r ON o.repair_type_id = r.id
        WHERE o.id = ?
    ");
    
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: index.php');
        exit();
    }

} catch(PDOException $e) {
    $error = "Bir hata oluştu.";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Onayı - <?php echo $site_settings['site_title']; ?></title>
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
            max-width: 600px;
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

        .success-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon svg {
            width: 30px;
            height: 30px;
            color: #20c997;
        }

        .content {
            padding: 30px;
        }

        .order-number {
            text-align: center;
            margin-bottom: 30px;
        }

        .order-number h2 {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }

        .order-number p {
            font-size: 24px;
            font-weight: 600;
            color: #20c997;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .next-steps {
            background: #fff8e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .next-steps h3 {
            color: #b45309;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .next-steps ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .next-steps li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
            color: #92400e;
        }

        .next-steps li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #b45309;
        }

        .home-button {
            display: inline-block;
            width: 100%;
            padding: 12px;
            background: #20c997;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .home-button:hover {
            background: #1ba37e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h1>Sipariş Başarıyla Oluşturuldu!</h1>
            <p>Onarım talebiniz başarıyla alındı.</p>
        </div>

        <div class="content">
            <div class="order-number">
                <h2>Sipariş Numaranız</h2>
                <p><?php echo $order['order_number']; ?></p>
            </div>

            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Cihaz</span>
                    <span class="detail-value"><?php echo $order['brand_name'] . ' ' . $order['model_name']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Onarım</span>
                    <span class="detail-value"><?php echo $order['repair_name']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fiyat</span>
                    <span class="detail-value"><?php echo number_format($order['price'], 2); ?>€</span>
                </div>
            </div>

            <div class="next-steps">
                <h3>Sonraki Adımlar</h3>
                <ul>
                    <li>Cihazınızı güvenli bir şekilde paketleyin.</li>
                    <li>Sipariş numaranızı paketin üzerine yazın.</li>
                    <li>Seçtiğiniz kargo firması (<?php echo strtoupper($order['shipping_method']); ?>) ile gönderin.</li>
                    <li>Adresimiz: <?php echo $site_settings['company_address']; ?></li>
                    <li>Cihazınız bize ulaştığında size bilgi vereceğiz.</li>
                </ul>
            </div>

            <a href="index.php" class="home-button">Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>