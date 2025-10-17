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

// Валидация данных книги
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

    if (empty($data['published_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['published_date'])) {
        $errors[] = 'Invalid date format';
    }

    // Проверка: дата не может быть в будущем
    if (!empty($data['published_date']) && strtotime($data['published_date']) > time()) {
        $errors[] = 'Published date cannot be in the future';
    }

    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($book) {
                echo json_encode($book);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Book not found']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM books");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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

        $stmt = $pdo->prepare("INSERT INTO books (title, author, pages, published_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['author'], $data['pages'], $data['published_date']]);
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

        $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, pages=?, published_date=? WHERE id=?");
        $stmt->execute([$data['title'], $data['author'], $data['pages'], $data['published_date'], $id]);
        echo json_encode(['message' => 'Updated']);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['message' => 'Deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
