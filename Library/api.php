<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Функция валидации данных
function validateBook($data) {
    $errors = [];

    if (empty($data['title']) || strlen(trim($data['title'])) < 2) {
        $errors[] = 'Title must be at least 2 characters long';
    }

    if (empty($data['author']) || strlen(trim($data['author'])) < 2) {
        $errors[] = 'Author must be at least 2 characters long';
    }

    if (!isset($data['pages']) || !is_numeric($data['pages']) || $data['pages'] <= 0) {
        $errors[] = 'Pages must be a positive number';
    }
    
    // ВАЛИДАЦИЯ PRICE И RATE
    if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
        $errors[] = 'Price must be a non-negative number';
    }
    
    if (!isset($data['rate']) || !is_numeric($data['rate']) || $data['rate'] < 0 || $data['rate'] > 5) {
        $errors[] = 'Rate must be a number between 0 and 5';
    }
    
    if (empty($data['published_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['published_date'])) {
        $errors[] = 'Invalid date format';
    }

    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT id, title, author, pages, published_date, price, rate FROM books WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = $pdo->query('SELECT id, title, author, pages, published_date, price, rate FROM books');
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $errors = validateBook($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO books (title, author, pages, published_date, price, rate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['author'], $data['pages'], $data['published_date'], $data['price'], $data['rate']]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $id = $_GET['id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $errors = validateBook($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, pages=?, published_date=?, price=?, rate=? WHERE id=?");
        $stmt->execute([$data['title'], $data['author'], $data['pages'], $data['published_date'], $data['price'], $data['rate'], $id]);
        echo json_encode(['message' => 'Updated']);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        echo json_encode(['message' => 'Deleted']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}