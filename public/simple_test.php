<?php
echo "=== TEST CƠ BẢN TRÊN VINAHOST ===<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "<hr>";

echo "=== KIỂM TRA THƯ MỤC ===<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "<hr>";

echo "=== KIỂM TRA THƯ MỤC IMAGES ===<br>";
$imagesPath = __DIR__ . '/images';
echo "Images Path: " . $imagesPath . "<br>";
echo "Images tồn tại: " . (is_dir($imagesPath) ? 'CÓ' : 'KHÔNG') . "<br>";

if (is_dir($imagesPath)) {
    echo "Nội dung thư mục images:<br>";
    $files = scandir($imagesPath);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- " . $file . "<br>";
        }
    }
}
echo "<hr>";

echo "=== KIỂM TRA THƯ MỤC BANNERS ===<br>";
$bannersPath = __DIR__ . '/images/banners';
echo "Banners Path: " . $bannersPath . "<br>";
echo "Banners tồn tại: " . (is_dir($bannersPath) ? 'CÓ' : 'KHÔNG') . "<br>";

if (is_dir($bannersPath)) {
    echo "Nội dung thư mục banners:<br>";
    $files = scandir($bannersPath);
    $imageCount = 0;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "🖼️ " . $file . " (" . $ext . ")<br>";
                $imageCount++;
            } else {
                echo "- " . $file . "<br>";
            }
        }
    }
    echo "Tổng số ảnh: " . $imageCount . "<br>";
} else {
    echo "❌ Thư mục banners KHÔNG tồn tại!<br>";
    echo "Hãy tạo thư mục: " . $bannersPath . "<br>";
}
echo "<hr>";

echo "=== TEST URL ===<br>";
$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
echo "Base URL: " . $baseUrl . "<br>";
echo "Test banner URL: " . $baseUrl . "/images/banners/test.jpg<br>";
echo "<hr>";

echo "=== KẾT LUẬN ===<br>";
if (is_dir($bannersPath)) {
    echo "✅ Thư mục banners tồn tại<br>";
    echo "✅ Có thể hiển thị banner<br>";
} else {
    echo "❌ Thư mục banners KHÔNG tồn tại<br>";
    echo "❌ Cần tạo thư mục trước<br>";
}

echo "<br>File test hoàn thành!";
?>




