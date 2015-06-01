<?php

error_reporting(1);

function compress_png($path_to_png_file, $max_quality = 90) {
    if (!file_exists($path_to_png_file)) {
        echo "File does not exist: $path_to_png_file";
    }
    
    $min_quality = 60;

    $compressed_png_content = shell_exec("/Users/viniciusmatteus/Downloads/pngquant/pngquant --quality=$min_quality-$max_quality - < ".escapeshellarg(    $path_to_png_file));

    if (!$compressed_png_content) {
        echo "Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?";
    }

    return $compressed_png_content;
}

$dir     = '/Users/viniciusmatteus/Documents/projetoAcao_upload/';
$dir_opt = '/Users/viniciusmatteus/Documents/projetoAcao_upload_opt/';
$files   = scandir($dir);

foreach ($files as $key => $value) {
   switch (exif_imagetype($dir . $value)) {
      case IMAGETYPE_JPEG:
         $img = imagecreatefromjpeg($dir . $value);
         imagejpeg($img, $dir_opt . $value ,70);
         break;
      case IMAGETYPE_PNG:
         $compressed_png_content = compress_png($dir . $value);
         file_put_contents($dir_opt . $value, $compressed_png_content);   
      default:
         copy($dir . $value, $dir_opt . $value);
         break;
   }
}