<?php
// database configuration
require_once 'config.php';

// This is validation
function isValidDescription($description) {
    return !empty($description);
}



function isValidPricing($pricing) {
    return is_numeric($pricing);
}

function isValidShippingCost($shippingCost) {
    return is_numeric($shippingCost);
}

// Get all products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM products');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
}

// Create a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $description = $data['description'];
    $image = $data['image'];
    $pricing = $data['pricing'];
    $shippingCost = $data['shipping_cost'];

    if (!isValidDescription($description) || !isValidPricing($pricing) || !isValidShippingCost($shippingCost)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO products (description, image, pricing, shipping_cost) VALUES (?, ?, ?, ?)');
    $stmt->execute([$description, $image, $pricing, $shippingCost]);

    echo json_encode(['message' => 'Product created successfully']);
}

// Update a product
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $description = $data['description'];
    $image = $data['image'];
    $pricing = $data['pricing'];
    $shippingCost = $data['shipping_cost'];

    if (!isValidDescription($description) || !isValidPricing($pricing) || !isValidShippingCost($shippingCost)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE products SET description=?, image=?, pricing=?, shipping_cost=? WHERE id=?');
    $stmt->execute([$description, $image, $pricing, $shippingCost, $id]);

    echo json_encode(['message' => 'Product updated successfully']);
}

// Delete a product
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM products WHERE id=?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'Product deleted successfully']);
}
?>
