<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';
require_once __DIR__ . '/../config_mikrotik.php';

if (!isset($_SESSION['mikrotik_ip'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interfaceName = $_POST['interface'] ?? '';

    if (!empty($interfaceName)) {
        $API = new RouterosAPI();
        $ip = $_SESSION['mikrotik_ip'];
        $user = $_SESSION['mikrotik_user'];
        $pass = $_SESSION['mikrotik_pass'];

        if ($API->connect($ip, $user, $pass)) {
            // Mengambil data traffic untuk interface tertentu selama 1 detik
            $API->write('/interface/monitor-traffic', false);
            $API->write('=interface=' . $interfaceName, false);
            $API->write('=once=');
            
            $trafficData = $API->read();

            if (!empty($trafficData)) {
                $response = [
                    'status' => 'success',
                    'data' => [
                        'tx_speed' => (int) $trafficData[0]['tx-bits-per-second'],
                        'rx_speed' => (int) $trafficData[0]['rx-bits-per-second']
                    ]
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'Interface tidak ditemukan atau tidak ada traffic.'];
            }

            $API->disconnect();
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal terhubung ke MikroTik.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Nama interface tidak boleh kosong.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak valid.'];
}

echo json_encode($response, JSON_PRETTY_PRINT);