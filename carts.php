<?php
require_once 'config.php';

// This is validation
function isValidProductId($productId) {
    return !empty($productId);
}

function isValidQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0;
}

function isValidUserId($userId) {
    return !empty($userId);
}

// Get all cart data with user and product details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT c.id AS cartId, c.userId, u.email AS userEmail, u.username AS userName, 
                                  p.id AS productId, p.description AS productDescription, p.image AS productImage, p.pricing AS productPrice, p.shipping_cost AS shippingCosts, c.quantity
                           FROM carts c
                           JOIN users u ON c.userId = u.id
                           JOIN products p ON c.productId = p.id');
    $stmt->execute();
    $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge data into single result
    $result = [];
    foreach ($carts as $cart) {
        $cartId = $cart['cartId'];
        if (!isset($result[$cartId])) {
            $result[$cartId] = [
                'cartId' => $cartId,
                'userId' => $cart['userId'],
                'userEmail' => $cart['userEmail'],
                'userName' => $cart['userName'],
                'products' => [],
            ];
        }
        $result[$cartId]['products'][] = [
            'productId' => $cart['productId'],
            'productDescription' => $cart['productDescription'],
            'productPrice' => $cart['productPrice'],
            'quantity' => $cart['quantity'],
        ];
    }

    echo json_encode(array_values($result));
}


// Create a new cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'];
    $quantity = $data['quantity'];
    $userId = $data['userId']; 
    $user = null;

    if (!isValidProductId($productId) || !isValidQuantity($quantity) || !isValidUserId($userId)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    // Fetch user data based on userId
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['message' => 'User not found']);
        http_response_code(404);
        exit();
    }

    // Fetch product data based on productId
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['message' => 'Product not found']);
        http_response_code(404);
        exit();
    }

    // Insert the product into the cart
    $stmt = $pdo->prepare('INSERT INTO carts (productId, quantity, userId) VALUES (?, ?, ?)');
    $stmt->execute([$productId, $quantity, $userId]);

    echo json_encode(['message' => 'Cart created successfully']);
}

// Update a cart
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $productId = $data['productId']; // New product ID to update
    $quantity = $data['quantity']; // New quantity to update

    if (!isValidProductId($productId) || !isValidQuantity($quantity)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    // Fetch existing cart data
    $stmt = $pdo->prepare('SELECT * FROM carts WHERE id = ?');
    $stmt->execute([$id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode(['message' => 'Cart not found']);
        http_response_code(404);
        exit();
    }

    // Fetch product details based on product ID
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['message' => 'Product not found']);
        http_response_code(404);
        exit();
    }

    // Update cart with new product ID and quantity
    $stmt = $pdo->prepare('UPDATE carts SET productId = ?, quantity = ? WHERE id = ?');
    $stmt->execute([$productId, $quantity, $id]);

    echo json_encode(['message' => 'Cart updated successfully']);
}



// Delete a cart
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM carts WHERE id=?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'Cart deleted successfully']);
}
?>
