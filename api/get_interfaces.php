<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';
require_once __DIR__ . '/../config_mikrotik.php';

// Cek sesi login admin
if (!isset($_SESSION['mikrotik_ip'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

$API = new RouterosAPI();
$ip = $_SESSION['mikrotik_ip'];
$user = $_SESSION['mikrotik_user'];
$pass = $_SESSION['mikrotik_pass'];

if ($API->connect($ip, $user, $pass)) {
    $interfaceNames = [];

    // 1. Ambil SEMUA interface ethernet
    $ethernets = $API->comm("/interface/ethernet/print");
    if (!empty($ethernets)) {
        foreach ($ethernets as $ethernet) {
            $interfaceNames[] = $ethernet['name'];
        }
    }

    // 2. Ambil SEMUA interface bridge
    $bridges = $API->comm("/interface/bridge/print");
    if (!empty($bridges)) {
        foreach ($bridges as $bridge) {
            $interfaceNames[] = $bridge['name'];
        }
    }
    
    // Siapkan respons dengan data yang sudah digabung dan unik
    $response = [
        'status' => 'success',
        'data' => array_unique($interfaceNames)
    ];

    $API->disconnect();
} else {
    $response = [
        'status' => 'error',
        'message' => 'Gagal terhubung ke MikroTik.'
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);