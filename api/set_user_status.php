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
    $userId = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? ''; // 'true' untuk disable, 'false' untuk enable

    if (!empty($userId) && $status !== '') {
        $API = new RouterosAPI();
        $ip = $_SESSION['mikrotik_ip'];
        $user = $_SESSION['mikrotik_user'];
        $pass = $_SESSION['mikrotik_pass'];

        if ($API->connect($ip, $user, $pass)) {
            // Kirim perintah 'set' ke MikroTik
            $API->comm("/ppp/secret/set", [
                ".id" => $userId,
                "disabled" => $status,
            ]);
            
            $message = ($status === 'true') ? 'Pelanggan berhasil diisolir (disabled).' : 'Pelanggan berhasil diaktifkan kembali.';
            $response = ['status' => 'success', 'message' => $message];
            $API->disconnect();
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal terhubung ke MikroTik.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'ID user dan status tidak boleh kosong.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak valid.'];
}

echo json_encode($response);