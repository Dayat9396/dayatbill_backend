<?php
// Memulai atau melanjutkan sesi PHP yang ada
session_start();

// Memberi tahu browser bahwa responsnya adalah format JSON
header('Content-Type: application/json');
// Meng-include class RouterOS API
require_once __DIR__ . '/../routeros_api.class.php';

// Cek apakah user sudah login dengan memeriksa keberadaan session
if (!isset($_SESSION['mikrotik_ip'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
    ]);
    exit(); // Hentikan skrip jika belum login
}

$API = new RouterosAPI();

// Ambil kredensial dari session yang tersimpan
$ip = $_SESSION['mikrotik_ip'];
$user = $_SESSION['mikrotik_user'];
$pass = $_SESSION['mikrotik_pass'];

// Coba hubungkan ke MikroTik menggunakan data dari session
if ($API->connect($ip, $user, $pass)) {
    // Jalankan perintah untuk mengambil resource
    $resourceData = $API->comm("/system/resource/print");
    
    // Format data agar lebih rapi untuk dikirim ke aplikasi
    $data = [
        'uptime' => $resourceData[0]['uptime'],
        'version' => $resourceData[0]['version'],
        'cpu_load' => $resourceData[0]['cpu-load'] . '%',
        'free_memory' => $resourceData[0]['free-memory'],   // <-- Disesuaikan untuk v6
        'total_memory' => $resourceData[0]['total-memory']  // <-- Disesuaikan untuk v6
    ];

    $response = [
        'status' => 'success',
        'data' => $data
    ];

    $API->disconnect();
} else {
    $response = [
        'status' => 'error',
        'message' => 'Gagal terhubung ke MikroTik dengan sesi tersimpan.'
    ];
}

// Cetak respons dalam format JSON
echo json_encode($response);