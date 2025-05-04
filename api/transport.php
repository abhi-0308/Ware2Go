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
        $stmt = $db->prepare("INSERT INTO Transport (vehicle_number, driver_name, contact_info, vehicle_type, capacity, status)
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['vehicle_number'], $data['driver_name'], $data['contact_info'],
            $data['vehicle_type'], $data['capacity'], $data['status']
        ]);
        echo json_encode(["message" => "Inserted successfully"]);
    }

    elseif ($action === 'update_field') {
        $id = $input['id'] ?? null;
        $field = $input['field'] ?? null;
        $value = $input['value'] ?? null;

        $allowed_fields = ['vehicle_number', 'driver_name', 'contact_info', 'vehicle_type', 'capacity', 'status'];
        if (!$id || !$field || $value === null || !in_array($field, $allowed_fields)) {
            echo json_encode(["error" => "Missing or invalid parameters"]);
            exit;
        }

        $sql = "UPDATE Transport SET $field = :value WHERE transport_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(["message" => "Updated successfully"]);
    }

    elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM Transport WHERE transport_id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(["message" => "Deleted successfully"]);
    }

    elseif ($action === 'select') {
        $stmt = $db->query("SELECT * FROM Transport");
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
