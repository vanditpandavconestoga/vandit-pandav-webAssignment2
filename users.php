<?php
require_once 'config.php';

// This is validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPassword($password) {
    return !empty($password);
}

function isValidUsername($username) {
    return !empty($username);
}

// Get all users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

// Create a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $password = $data['password'];
    $username = $data['username'];
    $purchaseHistory = $data['purchase_history'];
    $shippingAddress = $data['shipping_address'];

    // if (!isValidEmail($email) || !isValidPassword($password) || !isValidUsername($username)) {
    //     echo json_encode(['error' => 'Invalid data provided']);
    //     exit;
    // }

    $stmt = $pdo->prepare('INSERT INTO users (email, password, username, purchase_history, shipping_address) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$email, $password, $username, $purchaseHistory, $shippingAddress]);

    echo json_encode(['message' => 'User created successfully']);
}

// Update a user
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $email = $data['email'];
    $password = $data['password'];
    $username = $data['username'];
    $purchaseHistory = $data['purchase_history'];
    $shippingAddress = $data['shipping_address'];

    if (!isValidEmail($email) || !isValidPassword($password) || !isValidUsername($username)) {
        echo json_encode(['error' => 'Invalid data provided']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE users SET email=?, password=?, username=?, purchase_history=?, shipping_address=? WHERE id=?');
    $stmt->execute([$email, $password, $username, $purchaseHistory, $shippingAddress, $id]);

    echo json_encode(['message' => 'User updated successfully']);
}


// Delete a user
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'User deleted successfully']);
}

?>
