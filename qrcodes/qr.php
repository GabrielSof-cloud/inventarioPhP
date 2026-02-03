<?php
require_once __DIR__.'/lib/phpqrcode/qrlib.php';

function generalQR($texto, $archivo = null, $dimension=6) {

    QRcode::png($texto, $archivo, QR_ECLEVEL_L,$dimension);
}


?>