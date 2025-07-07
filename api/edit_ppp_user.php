<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';

// Cek sesi login
if (!isset($_SESSION['mikrotik_ip'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

// Hanya proses jika metodenya POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil semua data yang dibutuhkan dari request
    $userId = $_POST['id'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $newProfile = $_POST['profile'] ?? '';
    $newComment = $_POST['comment'] ?? '';

    // Validasi data penting
    if (!empty($userId) && !empty($newProfile)) {
        $API = new RouterosAPI();
        $ip = $_SESSION['mikrotik_ip'];
        $user = $_SESSION['mikrotik_user'];
        $pass = $_SESSION['mikrotik_pass'];

        if ($API->connect($ip, $user, $pass)) {
            // Siapkan array data yang akan diubah
            $updateData = [
                ".id" => $userId,
                "profile" => $newProfile,
                "comment" => $newComment,
            ];
            
            // Hanya tambahkan password ke array jika diisi (tidak kosong)
            if (!empty($newPassword)) {
                $updateData["password"] = $newPassword;
            }
            
            // Kirim perintah 'set' ke MikroTik
            $API->comm("/ppp/secret/set", $updateData);
            
            $response = ['status' => 'success', 'message' => 'Data pelanggan berhasil diubah.'];
            $API->disconnect();
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal terhubung ke MikroTik.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'ID user dan profile tidak boleh kosong.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak valid.'];
}

echo json_encode($response);