<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';

// Cek apakah admin sudah login melalui session
if (!isset($_SESSION['mikrotik_ip'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

$API = new RouterosAPI();
$ip = $_SESSION['mikrotik_ip'];
$user = $_SESSION['mikrotik_user'];
$pass = $_SESSION['mikrotik_pass'];

// Coba hubungkan ke MikroTik
if ($API->connect($ip, $user, $pass)) {
    // Ambil semua user yang terdaftar di secret
    $secrets = $API->comm("/ppp/secret/print");
    // Ambil semua user yang sedang aktif/online
    $activeUsers = $API->comm("/ppp/active/print");

    // Buat array untuk menandai user mana saja yang sedang online
    $onlineUsers = [];
    foreach ($activeUsers as $active) {
        $onlineUsers[$active['name']] = true;
    }

    // Olah dan gabungkan data untuk dikirim ke aplikasi
    $usersData = [];
    foreach ($secrets as $secret) {
        $username = $secret['name'];
        $usersData[] = [
            'id' => $secret['.id'],
            'username' => $username,
            'service' => $secret['service'],
            'profile' => $secret['profile'],
            'comment' => $secret['comment'] ?? '', // Menampilkan comment/nama pelanggan
            'is_disabled' => ($secret['disabled'] === 'true'), // Status akun diisolir/tidak
            'is_online' => isset($onlineUsers[$username]) // Status koneksi online/tidak
        ];
    }

    // Siapkan respons sukses beserta data
    $response = [
        'status' => 'success',
        'data' => $usersData
    ];

    $API->disconnect();
} else {
    // Siapkan respons jika gagal terhubung
    $response = [
        'status' => 'error',
        'message' => 'Gagal terhubung ke MikroTik dengan sesi tersimpan.'
    ];
}

// Kirim hasil akhir dalam format JSON
echo json_encode($response, JSON_PRETTY_PRINT);