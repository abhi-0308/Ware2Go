<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $db = new PDO("sqlite:warehousedb.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents("php://input"), true);
    $action = $_GET['action'] ?? $input['action'] ?? '';

    if ($action === 'insert') {
        $data = $input['data'];
        $stmt = $db->prepare("INSERT INTO Orders (customer_id, status, total_amount, shipping_address, payment_status)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['customer_id'], $data['status'], $data['total_amount'], 
            $data['shipping_address'], $data['payment_status']
        ]);
        echo json_encode(["message" => "Inserted successfully"]);
    }

    elseif ($action === 'update_field') {
        $id = $input['id'] ?? null;
        $field = $input['field'] ?? null;
        $value = $input['value'] ?? null;

        $allowed_fields = ['customer_id', 'status', 'total_amount', 'shipping_address', 'payment_status'];
        if (!$id || !$field || $value === null || !in_array($field, $allowed_fields)) {
            echo json_encode(["error" => "Missing or invalid parameters"]);
            exit;
        }

        $sql = "UPDATE Orders SET $field = :value WHERE order_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(["message" => "Updated successfully"]);
    }

    elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM Orders WHERE order_id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(["message" => "Deleted successfully"]);
    }

    elseif ($action === 'select') {
        $stmt = $db->query("SELECT * FROM Orders");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

    else {
        echo json_encode(["error" => "Invalid action"]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["error" => "General error: " . $e->getMessage()]);
}
