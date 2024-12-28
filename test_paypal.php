<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test PayPal Payment</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test PayPal Payment</h2>
        <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="sb-43zqe22815739@personal.example.com">
            <input type="hidden" name="item_name" value="Test Payment">
            <input type="hidden" name="amount" value="1.00">
            <input type="hidden" name="currency_code" value="EUR">
            <input type="hidden" name="return" value="http://localhost/Gestion_reservation_hotel/payment_success_simple.php?order_id=TEST123">
            <input type="hidden" name="cancel_return" value="http://localhost/Gestion_reservation_hotel/payment_cancel_simple.php?order_id=TEST123">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="no_note" value="1">
            
            <input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
        </form>
    </div>
</body>
</html>
