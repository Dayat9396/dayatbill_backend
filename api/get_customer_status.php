<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';
require_once __DIR__ . '/../config_mikrotik.php'; 

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    if (!empty($username)) {
        $API = new RouterosAPI();

        if ($API->connect(MIKROTIK_IP, MIKROTIK_USER, MIKROTIK_PASS)) {

            $secretInfo = $API->comm("/ppp/secret/print", ["?name" => $username]);
            $isDisabled = false;
            if (!empty($secretInfo) && $secretInfo[0]['disabled'] === 'true') {
                $isDisabled = true;
            }

            $activeInfo = $API->comm("/ppp/active/print", ["?name" => $username]);

            if ($isDisabled) {
                $status_text = "Disabled";
            } elseif (!empty($activeInfo)) {
                $status_text = "Online";
            } else {
                $status_text = "Offline";
            }

            // PERUBAHAN UTAMA: Tambahkan 'interface' ke data
            $data = [
                'status_text' => $status_text,
                'uptime' => $activeInfo[0]['uptime'] ?? 'N/A',
                'address' => $activeInfo[0]['address'] ?? 'N/A',
                'rx_byte' => (int) ($activeInfo[0]['bytes-in'] ?? 0),
                'tx_byte' => (int) ($activeInfo[0]['bytes-out'] ?? 0),
                'interface' => $activeInfo[0]['interface'] ?? '' // <-- DATA BARU
            ];

            $response = [
                'status' => 'success',
                'data' => $data
            ];

            $API->disconnect();
        } else {
            $response['message'] = 'Sistem tidak dapat terhubung ke server utama.';
        }
    } else {
        $response['message'] = 'Username tidak boleh kosong.';
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);