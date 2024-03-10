<?php
require_once 'config.php';


// This is validation
function isValidProductId($productId) {
    return !empty($productId);
}

function isValidUserId($userId) {
    return !empty($userId);
}

function isValidQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0;
}

function isValidTotalAmount($totalAmount) {
    return is_numeric($totalAmount) && $totalAmount > 0;
}

// Get all orders data with user and product details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT o.id AS orderId, o.productId, o.userId, u.email AS userEmail, u.username AS userName,
                                  p.id AS productId, p.description AS productDescription, p.image AS productImage, p.pricing AS productPrice, p.shipping_cost AS shippingCosts,
                                  o.quantity, o.total_amount
                           FROM orders o
                           JOIN users u ON o.userId = u.id
                           JOIN products p ON o.productId = p.id');
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
}

// Create a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'];
    $userId = $data['userId'];
    $quantity = $data['quantity'];
    $totalAmount = $data['total_amount'];

    if (!isValidProductId($productId) || !isValidUserId($userId) || !isValidQuantity($quantity) || !isValidTotalAmount($totalAmount)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO `orders` (productId, userId, quantity, total_amount) VALUES (?, ?, ?, ?)');
    $stmt->execute([$productId, $userId, $quantity, $totalAmount]);

    echo json_encode(['message' => 'Order created successfully']);
}

// Update an order
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $productId = $data['productId'];
    $quantity = $data['quantity'];
    $totalAmount = $data['total_amount'];

    if (!isValidProductId($productId) || !isValidQuantity($quantity) || !isValidTotalAmount($totalAmount)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE `orders` SET productId=?, quantity=?, total_amount=? WHERE id=?');
    $stmt->execute([$productId, $quantity, $totalAmount, $id]);

    echo json_encode(['message' => 'Order updated successfully']);
}

// Delete an order
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM orders WHERE id=?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'Order deleted successfully']);
}
?>
