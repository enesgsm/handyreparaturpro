<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_settings['site_title']; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

        .form-container {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-size: 14px;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            color: #333;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 15px;
        }

        select:disabled {
            background-color: #f8f9fa;
            color: #999;
        }

        .price-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }

        .price-label {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .price {
            font-size: 32px;
            color: #20c997;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .order-button-container {
            margin-top: 20px;
            text-align: center;
        }

        .order-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 16px 24px;
            background-color: #20c997;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }

        .order-button:hover {
            background-color: #1ba37e;
        }

        .order-button:disabled {
            background-color: #e2e8f0;
            cursor: not-allowed;
        }

        .order-button svg {
            margin-left: 8px;
            width: 20px;
            height: 20px;
        }

        .note {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 12px;
        }

        .whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #25D366;
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3);
            transition: transform 0.2s ease;
        }

        .whatsapp-button:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Handy Reparatur Service</h1>
            <p>Profesyonel ve Hızlı Tamir Hizmeti</p>
        </div>

        <div class="form-container">
            <div class="form-group">
                <label>Marka Seçin</label>
                <select id="brand">
                    <option value="">Marka Seçin</option>
                    <?php
                    try {
                        $stmt = $db->query("SELECT * FROM brands WHERE status = 1 ORDER BY sort_order DESC, name");
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                    } catch(PDOException $e) {
                        echo "<option value=''>Markalar yüklenemedi</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Model Seçin</label>
                <select id="model" disabled>
                    <option value="">Önce Marka Seçin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Arıza Türü</label>
                <select id="repair" disabled>
                    <option value="">Önce Model Seçin</option>
                </select>
            </div>

            <div class="price-display">
                <div class="price-label">Tahmini Fiyat</div>
                <div class="price">Lütfen seçim yapın</div>
                
                <div class="order-button-container">
                    <a href="repair_form.php" class="order-button" id="createOrder">
                        Onarım Talebi Oluştur
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                
                <p class="note">* Onarım talebinizi oluşturduktan sonra cihazınızı bize gönderebilirsiniz.</p>
            </div>
        </div>
    </div>

    <a href="#" id="whatsappButton" class="whatsapp-button">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        WhatsApp'tan Sor
    </a>

    <script>
        $(document).ready(function() {
            // İlk yüklemede butonu gizle
            $('#createOrder').hide();

            $('#brand').change(function() {
                const brandId = $(this).val();
                const modelSelect = $('#model');
                
                modelSelect.empty().append('<option value="">Model Seçin</option>').prop('disabled', true);
                $('#repair').empty().append('<option value="">Önce Model Seçin</option>').prop('disabled', true);
                $('.price').text('Lütfen seçim yapın');
                $('#createOrder').hide();
                
                if (brandId) {
                    $.getJSON('get_models.php', {brand_id: brandId}, function(models) {
                        models.forEach(model => {
                            modelSelect.append(`<option value="${model.id}">${model.name}</option>`);
                        });
                        modelSelect.prop('disabled', false);
                    });
                }
            });

            $('#model').change(function() {
                const modelId = $(this).val();
                const repairSelect = $('#repair');
                
                repairSelect.empty().append('<option value="">Arıza Türü Seçin</option>').prop('disabled', true);
                $('.price').text('Lütfen seçim yapın');
                $('#createOrder').hide();
                
                if (modelId) {
                    $.getJSON('get_repairs.php', {model_id: modelId}, function(repairs) {
                        repairs.forEach(repair => {
                            repairSelect.append(`<option value="${repair.id}" data-price="${repair.price}">${repair.name}</option>`);
                        });
                        repairSelect.prop('disabled', false);
                    });
                }
            });

            $('#repair').change(function() {
                const selectedOption = $(this).find(':selected');
                const price = selectedOption.data('price');
                
                if (price) {
                    $('.price').text(`${price}€`);
                    $('#createOrder').show();
                } else {
                    $('.price').text('Lütfen seçim yapın');
                    $('#createOrder').hide();
                }
            });

            $('#createOrder').click(function(e) {
                e.preventDefault();
                
                const brand = $('#brand option:selected').text();
                const model = $('#model option:selected').text();
                const repair = $('#repair option:selected').text();
                const price = $('.price').text();

                $.post('save_selections.php', {
                    brand: brand,
                    model: model,
                    repair: repair,
                    price: price
                }, function(response) {
                    window.location.href = 'repair_form.php';
                });
            });

            $('#whatsappButton').click(function(e) {
                e.preventDefault();
                
                const brand = $('#brand option:selected').text();
                const model = $('#model option:selected').text();
                const repair = $('#repair option:selected').text();
                const price = $('.price').text();
                
                let message = 'Merhaba, web sitenizden fiyat sorguladım.\n\n';
                
                if (brand && brand !== 'Marka Seçin' && model && model !== 'Model Seçin' && repair && repair !== 'Arıza Türü Seçin' && price !== 'Lütfen seçim yapın') {
                    message += `Marka: ${brand}\n`;
                    message += `Model: ${model}\n`;
                    message += `Arıza: ${repair}\n`;
                    message += `Fiyat: ${price}\n\n`;
                    message += 'Bu tamir için bilgi almak istiyorum.';
                } else {
                    message = 'Merhaba, telefon tamiri hakkında bilgi almak istiyorum.';
                }
                
                window.open(`https://wa.me/${<?php echo $site_settings['whatsapp_number']; ?>}?text=${encodeURIComponent(message)}`, '_blank');
            });
        });
    </script>
</body>
</html>