<?php
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request
    $username = $_POST['username'] ?? 'anonim';
    $message = $_POST['message'] ?? '';

    if (!empty($message)) {
        // Tentukan path untuk file log
        $logFile = __DIR__ . '/../complaints.log';
        
        // Format entri log
        $timestamp = date("Y-m-d H:i:s");
        $logEntry = "[$timestamp] | User: $username | Keluhan: $message" . PHP_EOL;
        
        // Tulis ke file log, mode 'a' untuk append (menambahkan di akhir)
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        $response = ['status' => 'success', 'message' => 'Keluhan Anda telah berhasil dikirim.'];

    } else {
        $response['message'] = 'Pesan keluhan tidak boleh kosong.';
    }
}

echo json_encode($response);