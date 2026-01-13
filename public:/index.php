<?php
require_once __DIR__ . "/../src/db.php";

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple test endpoint
if ($path === "/health" && $method === "GET") {
  echo json_encode(["ok" => true]);
  exit;
}

// Send message: POST /messages/send
if ($path === "/messages/send" && $method === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  $company_id = (int)$data["company_id"];
  $conversation_id = (int)$data["conversation_id"];
  $sender_id = (int)$data["sender_id"];
  $body = trim($data["body"] ?? "");
  $message_type = $data["message_type"] ?? "normal";

  if ($body === "") {
    http_response_code(400);
    echo json_encode(["error" => "Message body required"]);
    exit;
  }

  $pdo = db();
  $stmt = $pdo->prepare("
    INSERT INTO messages (company_id, conversation_id, sender_id, body, message_type)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->execute([$company_id, $conversation_id, $sender_id, $body, $message_type]);

  $message_id = (int)$pdo->lastInsertId();

  // Audit log
  $audit = $pdo->prepare("
    INSERT INTO audit_logs (company_id, actor_id, action_type, entity_type, entity_id, metadata)
    VALUES (?, ?, 'MESSAGE_SENT', 'message', ?, JSON_OBJECT('conversation_id', ?, 'message_type', ?))
  ");
  $audit->execute([$company_id, $sender_id, $message_id, $conversation_id, $message_type]);

  echo json_encode(["message_id" => $message_id]);
  exit;
}

http_response_code(404);
echo json_encode(["error" => "Not found"]);
