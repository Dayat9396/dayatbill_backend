<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../routeros_api.class.php';
require_once __DIR__ . '/../config_mikrotik.php'; // Panggil file konfigurasi

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $API = new RouterosAPI();

        // Gunakan kredensial admin dari file config untuk terhubung
        if ($API->connect(MIKROTIK_IP, MIKROTIK_USER, MIKROTIK_PASS)) {

            // Cari ppp secret berdasarkan username DAN password yang diinput pelanggan
            $result = $API->comm("/ppp/secret/print", [
                "?name" => $username,
                "?password" => $password,
            ]);

            // Jika hasilnya tidak kosong (ditemukan 1), maka login berhasil
            if (!empty($result)) {
                $response = [
                    'status' => 'success',
                    'message' => 'Login pelanggan berhasil!',
                    'data' => [ // Kirim kembali data pelanggan
                        'username' => $result[0]['name'],
                        'profile' => $result[0]['profile'],
                        'comment' => $result[0]['comment'] ?? ''
                    ]
                ];
            } else {
                $response['message'] = 'Login gagal. Username atau password salah.';
            }

            $API->disconnect();
        } else {
            $response['message'] = 'Sistem tidak dapat terhubung ke server utama.';
        }
    } else {
        $response['message'] = 'Username dan password tidak boleh kosong.';
    }
}

echo json_encode($response);