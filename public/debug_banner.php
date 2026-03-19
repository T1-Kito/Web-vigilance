<?php
echo "=== DEBUG BANNER TRÊN VINAHOST ===<br>";
echo "Thời gian: " . date('Y-m-d H:i:s') . "<br>";
echo "<hr>";

// Kết nối database với thông tin đúng từ phpMyAdmin
try {
    $host = 'localhost';
    $dbname = 'agajcvso_gamedoan'; // Database đúng từ phpMyAdmin
    $username = 'agajcvso_webvikhang'; // Username có thể đúng
    $password = '01222945112Aa@';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Kết nối database thành công<br>";
    
    // Kiểm tra bảng banners
    $stmt = $pdo->query("SHOW TABLES LIKE 'banners'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Bảng banners tồn tại<br>";
        
        // Lấy dữ liệu banner
        $stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC LIMIT 5");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Số banner trong database: " . count($banners) . "<br>";
        echo "<hr>";
        
        foreach ($banners as $banner) {
            echo "<h4>Banner ID: " . $banner['id'] . "</h4>";
            echo "<ul>";
            echo "<li><strong>Title:</strong> " . ($banner['title'] ?: 'Không có') . "</li>";
            echo "<li><strong>Image Path:</strong> " . $banner['image_path'] . "</li>";
            echo "<li><strong>Link URL:</strong> " . ($banner['link_url'] ?: 'Không có') . "</li>";
            echo "<li><strong>Sort Order:</strong> " . $banner['sort_order'] . "</li>";
            echo "<li><strong>Is Active:</strong> " . ($banner['is_active'] ? 'Có' : 'Không') . "</li>";
            echo "</ul>";
            
            // Kiểm tra file ảnh
            if ($banner['image_path']) {
                $imagePath = __DIR__ . '/' . $banner['image_path'];
                echo "<strong>Full Image Path:</strong> " . $imagePath . "<br>";
                echo "<strong>File tồn tại:</strong> " . (file_exists($imagePath) ? '✅ CÓ' : '❌ KHÔNG') . "<br>";
                
                if (file_exists($imagePath)) {
                    echo "<strong>File size:</strong> " . filesize($imagePath) . " bytes<br>";
                    echo "<strong>Đọc được:</strong> " . (is_readable($imagePath) ? '✅ CÓ' : '❌ KHÔNG') . "<br>";
                    
                    // Test hiển thị ảnh
                    $imageUrl = 'https://quanlynhansu.id.vn/' . $banner['image_path'];
                    echo "<strong>Image URL:</strong> <a href='$imageUrl' target='_blank'>$imageUrl</a><br>";
                    echo "<strong>Ảnh:</strong><br>";
                    echo "<img src='$imageUrl' style='max-width:200px;max-height:150px;border:1px solid #ccc;' alt='Banner " . $banner['id'] . "'><br>";
                }
            }
            echo "<hr>";
        }
        
    } else {
        echo "❌ Bảng banners KHÔNG tồn tại<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Lỗi kết nối database: " . $e->getMessage() . "<br>";
    echo "<hr>";
    
    // Thử với username khác
    try {
        $username = 'agajcvso_webcn';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        echo "✅ Kết nối thành công với username: $username<br>";
        
        // Lấy dữ liệu banner
        $stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC LIMIT 5");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Số banner trong database: " . count($banners) . "<br>";
        echo "<hr>";
        
        foreach ($banners as $banner) {
            echo "<h4>Banner ID: " . $banner['id'] . "</h4>";
            echo "<ul>";
            echo "<li><strong>Title:</strong> " . ($banner['title'] ?: 'Không có') . "</li>";
            echo "<li><strong>Image Path:</strong> " . $banner['image_path'] . "</li>";
            echo "<li><strong>Link URL:</strong> " . ($banner['link_url'] ?: 'Không có') . "</li>";
            echo "<li><strong>Sort Order:</strong> " . $banner['sort_order'] . "</li>";
            echo "<li><strong>Is Active:</strong> " . ($banner['is_active'] ? 'Có' : 'Không') . "</li>";
            echo "</ul>";
            
            // Kiểm tra file ảnh
            if ($banner['image_path']) {
                $imagePath = __DIR__ . '/' . $banner['image_path'];
                echo "<strong>Full Image Path:</strong> " . $imagePath . "<br>";
                echo "<strong>File tồn tại:</strong> " . (file_exists($imagePath) ? '✅ CÓ' : '❌ KHÔNG') . "<br>";
                
                if (file_exists($imagePath)) {
                    echo "<strong>File size:</strong> " . filesize($imagePath) . " bytes<br>";
                    echo "<strong>Đọc được:</strong> " . (is_readable($imagePath) ? '✅ CÓ' : '❌ KHÔNG') . "<br>";
                    
                    // Test hiển thị ảnh
                    $imageUrl = 'https://quanlynhansu.id.vn/' . $banner['image_path'];
                    echo "<strong>Image URL:</strong> <a href='$imageUrl' target='_blank'>$imageUrl</a><br>";
                    echo "<strong>Ảnh:</strong><br>";
                    echo "<img src='$imageUrl' style='max-width:200px;max-height:150px;border:1px solid #ccc;' alt='Banner " . $banner['id'] . "'><br>";
                }
            }
            echo "<hr>";
        }
        
    } catch (PDOException $e2) {
        echo "❌ Không thể kết nối với cả 2 username<br>";
    }
}

echo "<hr>";
echo "<h3>🔍 KIỂM TRA THỦ CÔNG:</h3>";
echo "1. Vào admin panel, xem banner có hiển thị ảnh không<br>";
echo "2. Kiểm tra console browser có lỗi gì không<br>";
echo "3. Kiểm tra Network tab xem ảnh có load được không<br>";
echo "4. Kiểm tra đường dẫn ảnh có đúng không<br>";
?>
