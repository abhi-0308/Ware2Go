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
        $stmt = $db->prepare("INSERT INTO Suppliers (name, contact_info, location_id, email, phone_number, rating)
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'], $data['contact_info'], $data['location_id'],
            $data['email'], $data['phone_number'], $data['rating']
        ]);
        echo json_encode(["message" => "Inserted successfully"]);
    }

    elseif ($action === 'update_field') {
        $id = $input['id'] ?? null;
        $field = $input['field'] ?? null;
        $value = $input['value'] ?? null;

        $allowed_fields = ['name', 'contact_info', 'location_id', 'email', 'phone_number', 'rating'];

        if (!$id || !$field || $value === null) {
            echo json_encode(["error" => "Missing ID, field, or value"]);
            exit;
        }

        if (!in_array($field, $allowed_fields)) {
            echo json_encode(["error" => "Invalid field"]);
            exit;
        }

        $sql = "UPDATE Suppliers SET $field = :value WHERE supplier_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["message" => "Field '$field' updated successfully for supplier_id $id"]);
    }

    elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM Suppliers WHERE supplier_id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(["message" => "Deleted successfully"]);
    }

    elseif ($action === 'select') {
        $stmt = $db->query("SELECT * FROM Suppliers");
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
