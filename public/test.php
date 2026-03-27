<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dir = __DIR__ . '/../storage/configs/';
echo "Prueba de escritura en: $dir <br>";

if (!is_dir($dir)) {
    echo "Creando directorio... <br>";
    if (mkdir($dir, 0777, true)) {
        echo "✅ Directorio creado. <br>";
    } else {
        echo "❌ Error al crear directorio. <br>";
    }
} else {
    echo "✅ El directorio ya existe. <br>";
}

$file = $dir . 'test_file.json';
$content = json_encode(['status' => 'ok', 'time' => date('Y-m-d H:i:s')]);

if (file_put_contents($file, $content)) {
    echo "✅ Archivo escrito con éxito en: $file <br>";
} else {
    echo "❌ Error al escribir el archivo. <br>";
}
