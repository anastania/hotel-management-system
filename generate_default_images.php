<?php
// Fonction pour créer une image avec du texte
function createImageWithText($text, $filename) {
    // Créer une image de 800x600
    $image = imagecreatetruecolor(800, 600);
    
    // Couleurs
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 50, 50, 50);
    
    // Remplir le fond
    imagefill($image, 0, 0, $bg_color);
    
    // Ajouter le texte
    $font_size = 40;
    $font_path = __DIR__ . '/arial.ttf';
    
    // Si la police n'existe pas, utiliser un texte simple
    if (!file_exists($font_path)) {
        // Centrer le texte
        $text_width = strlen($text) * imagefontwidth(5);
        $text_height = imagefontheight(5);
        $x = (800 - $text_width) / 2;
        $y = (600 + $text_height) / 2;
        imagestring($image, 5, $x, $y, $text, $text_color);
    } else {
        // Utiliser TrueType Font
        $bbox = imagettfbbox($font_size, 0, $font_path, $text);
        $x = (800 - ($bbox[2] - $bbox[0])) / 2;
        $y = (600 - ($bbox[1] - $bbox[7])) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $text);
    }
    
    // Sauvegarder l'image
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
}

// Créer le dossier images/chambres s'il n'existe pas
$dir = __DIR__ . '/images/chambres';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Générer les images par défaut
createImageWithText('Chambre Simple', $dir . '/simple.jpg');
createImageWithText('Chambre Double', $dir . '/double.jpg');
createImageWithText('Suite', $dir . '/suite.jpg');
createImageWithText('Chambre', $dir . '/default.jpg');

echo "Images par défaut générées avec succès!";
?>
