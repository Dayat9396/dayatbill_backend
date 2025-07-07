<?php
session_start(); // Memulai sesi

header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';

$response = [
    'status' => 'error',
    'message' => 'Permintaan tidak valid.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_POST['ip'] ?? '';
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($ip) && !empty($user) && !empty($pass)) {
        $API = new RouterosAPI();
        
        if ($API->connect($ip, $user, $pass)) {
            // SIMPAN KREDENSIAL KE SESSION
            $_SESSION['mikrotik_ip'] = $ip;
            $_SESSION['mikrotik_user'] = $user;
            $_SESSION['mikrotik_pass'] = $pass;
            
            $response = [
                'status' => 'success',
                'message' => 'Login berhasil!'
            ];
            $API->disconnect();
        } else {
            $response['message'] = 'Koneksi gagal. Periksa kembali IP, username, atau password.';
        }
    } else {
        $response['message'] = 'IP, username, dan password tidak boleh kosong.';
    }
}

echo json_encode($response);