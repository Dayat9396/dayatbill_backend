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
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $service = $_POST['service'] ?? 'pppoe'; // Default ke pppoe
    $profile = $_POST['profile'] ?? '';
    $comment = $_POST['comment'] ?? '';

    // Validasi data penting
    if (!empty($username) && !empty($password) && !empty($profile)) {
        $API = new RouterosAPI();
        $ip = $_SESSION['mikrotik_ip'];
        $user = $_SESSION['mikrotik_user'];
        $pass = $_SESSION['mikrotik_pass'];

        if ($API->connect($ip, $user, $pass)) {
            // Kirim perintah 'add' ke MikroTik dengan semua data
            $API->comm("/ppp/secret/add", [
                "name" => $username,
                "password" => $password,
                "service" => $service,
                "profile" => $profile,
                "comment" => $comment,
            ]);
            
            $response = ['status' => 'success', 'message' => 'Pelanggan baru berhasil ditambahkan.'];
            $API->disconnect();
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal terhubung ke MikroTik.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Username, password, dan profile tidak boleh kosong.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak valid.'];
}

echo json_encode($response);