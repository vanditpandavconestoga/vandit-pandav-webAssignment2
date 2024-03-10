<?php
require_once 'config.php';


// This is validation
function isValidProductId($productId) {
    return !empty($productId);
}

function isValidUserId($userId) {
    return !empty($userId);
}

function isValidRating($rating) {
    return is_numeric($rating) && $rating >= 1 && $rating <= 5;
}

function isValidText($text) {
    return !empty($text);
}

// Get all comments data with user and product details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT c.id AS commentId, c.productId, c.userId, u.email AS userEmail, u.username AS userName, 
                                  p.id AS productId, p.description AS productDescription, p.image AS productImage, p.pricing AS productPrice, p.shipping_cost AS shippingCosts,
                                  c.rating, c.images, c.text
                           FROM comments c
                           JOIN users u ON c.userId = u.id
                           JOIN products p ON c.productId = p.id');
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
}

// Create a new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'];
    $userId = $data['userId'];
    $rating = $data['rating'];
    $images = $data['images'];
    $text = $data['text'];

    if (!isValidProductId($productId) || !isValidUserId($userId) || !isValidRating($rating)  || !isValidText($text)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO comments (productId, userId, rating, images, text) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$productId, $userId, $rating, $images, $text]);

    echo json_encode(['message' => 'Comment created successfully']);
}

// Update a comment
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $productId = $data['productId'];
    $userId = $data['userId'];
    $rating = $data['rating'];
    $images = $data['images'];
    $text = $data['text'];

    if (!isValidProductId($productId) || !isValidUserId($userId) || !isValidRating($rating)  || !isValidText($text)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE comments SET productId=?, userId=?, rating=?, images=?, text=? WHERE id=?');
    $stmt->execute([$productId, $userId, $rating, $images, $text, $id]);

    echo json_encode(['message' => 'Comment updated successfully']);
}

// Delete a comment
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM comments WHERE id=?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'Comment deleted successfully']);
}
?>
