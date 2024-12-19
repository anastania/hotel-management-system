<?php
require_once 'paypal_config.php';
?>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=<?php echo PAYPAL_CURRENCY; ?>"></script>
<div id="paypal-button-container"></div>
<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?php echo $prix_total; ?>'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            // Rediriger vers la page de traitement avec l'ID de la commande PayPal
            window.location.href = '<?php echo PAYPAL_RETURN_URL; ?>?order_id=' + data.orderID + '&reservation_id=<?php echo $reservation_id; ?>';
        });
    },
    onCancel: function(data) {
        window.location.href = '<?php echo PAYPAL_CANCEL_URL; ?>';
    }
}).render('#paypal-button-container');
</script>
