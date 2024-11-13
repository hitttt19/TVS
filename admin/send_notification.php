<?php
include('../db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve the ticket number from the data
    $ticket_no = $data['ticket_no'];

    // Get the license ID associated with the ticket number
    $stmt = $pdo->prepare("SELECT license_id FROM offense_records WHERE ticket_no = ?");
    $stmt->execute([$ticket_no]);
    $license_id = $stmt->fetchColumn();

    // Get the driver ID from the drivers table using the license ID
    $stmt = $pdo->prepare("SELECT id FROM drivers WHERE license_id = ?");
    $stmt->execute([$license_id]);
    $driver_id = $stmt->fetchColumn();

    if ($driver_id) {
        // Prepare the notification message
        $message = "You have received a traffic violation notice regarding {$data['offense_name']}. Ticket No: {$data['ticket_no']}. Amount: {$data['offense_rate']}.";

        // Insert the notification
        $stmt = $pdo->prepare("INSERT INTO notifications (driver_id, message, created_at, status, ticket_no) VALUES (?, ?, NOW(), 'unread', ?)");
        $stmt->execute([$driver_id, $message, $ticket_no]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Driver not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
