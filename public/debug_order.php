<?php
// Debug file để kiểm tra thông tin đơn hàng
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "<h2>Debug Order Information</h2>";

// Kiểm tra đơn hàng ID 9
$order = Order::find(9);
if ($order) {
    echo "<h3>Order ID 9:</h3>";
    echo "Order ID: " . $order->id . "<br>";
    echo "User ID: " . $order->user_id . " (Type: " . gettype($order->user_id) . ")<br>";
    echo "Status: " . $order->status . "<br>";
    echo "Created: " . $order->created_at . "<br>";
    
    // Kiểm tra user của đơn hàng
    $orderUser = User::find($order->user_id);
    if ($orderUser) {
        echo "Order User: " . $orderUser->name . " (ID: " . $orderUser->id . ")<br>";
    } else {
        echo "Order User: NOT FOUND<br>";
    }
} else {
    echo "Order ID 9: NOT FOUND<br>";
}

echo "<h3>All Users:</h3>";
$users = User::all();
foreach ($users as $user) {
    echo "User ID: " . $user->id . " - Name: " . $user->name . " - Email: " . $user->email . "<br>";
}

echo "<h3>All Orders:</h3>";
$orders = Order::all();
foreach ($orders as $order) {
    echo "Order ID: " . $order->id . " - User ID: " . $order->user_id . " - Status: " . $order->status . "<br>";
}
?> 