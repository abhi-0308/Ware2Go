<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    // Connect to the SQLite database
    $db = new PDO("sqlite:warehousedb.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read JSON input (for POST requests)
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $_GET['action'] ?? $input['action'] ?? '';

    if ($action === 'insert') {
        $data = $input['data'];
        $stmt = $db->prepare("INSERT INTO Locations (address, city, state, zip_code, country, phone_number)
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['address'], $data['city'], $data['state'],
            $data['zip_code'], $data['country'], $data['phone_number']
        ]);
        echo json_encode(["message" => "Inserted successfully"]);
    }

    elseif ($action === 'update_field') {
        $id = $input['id'];
        $field = $input['field'];
        $value = $input['value'];
    
        // ✅ Only allow specific fields to prevent SQL injection
        $allowed_fields = ['address', 'city', 'state', 'zip_code', 'country', 'phone_number'];
        if (!in_array($field, $allowed_fields)) {
            echo json_encode(["error" => "Invalid field"]);
            exit;
        }
    
        // Build the SQL dynamically but safely
        $stmt = $db->prepare("UPDATE Locations SET $field = :value WHERE location_id = :id");
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    
        echo json_encode(["message" => "Field updated successfully"]);
    }

    elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM Locations WHERE location_id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(["message" => "Deleted successfully"]);
    }

    elseif ($action === 'select') {
        $stmt = $db->query("SELECT * FROM Locations");
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
