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

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $setInsolir = ($_POST['is_insolir'] ?? 'false') === 'true'; // Konversi string "true" ke boolean
    $setBlokir = ($_POST['is_blokir'] ?? 'false') === 'true';  // Konversi string "true" ke boolean

    if (!empty($username)) {
        $API = new RouterosAPI();
        $ip = $_SESSION['mikrotik_ip'];
        $user = $_SESSION['mikrotik_user'];
        $pass = $_SESSION['mikrotik_pass'];

        if ($API->connect($ip, $user, $pass)) {
            // Langkah 1: Cari IP address pelanggan yang sedang aktif
            $activeUser = $API->comm("/ppp/active/print", ["?name" => $username,]);

            if (empty($activeUser)) {
                $response = ['status' => 'error', 'message' => "Aksi gagal: Pelanggan '$username' sedang tidak online."];
            } else {
                $customerIp = $activeUser[0]['address'];

                // Fungsi bantuan untuk menambah/menghapus IP dari address list
                function manageAddressList($api, $listName, $ip, $shouldBeInList) {
                    $find = $api->comm("/ip/firewall/address-list/print", ["?list" => $listName, "?address" => $ip]);
                    if ($shouldBeInList && empty($find)) {
                        // Jika harusnya ada tapi tidak ada, tambahkan
                        $api->comm("/ip/firewall/address-list/add", ["list" => $listName, "address" => $ip, "comment" => "Added by DAYATBILL"]);
                    } elseif (!$shouldBeInList && !empty($find)) {
                        // Jika harusnya tidak ada tapi ada, hapus
                        $api->comm("/ip/firewall/address-list/remove", [".id" => $find[0]['.id']]);
                    }
                }

                // Langkah 2: Eksekusi aksi berdasarkan input
                manageAddressList($API, 'pelanggan_insolir', $customerIp, $setInsolir);
                manageAddressList($API, 'pelanggan_blokir', $customerIp, $setBlokir);
                
                $response = ['status' => 'success', 'message' => "Aksi untuk pelanggan '$username' berhasil diterapkan."];
            }

            $API->disconnect();
        } else {
            $response['message'] = 'Gagal terhubung ke MikroTik.';
        }
    } else {
        $response['message'] = 'Username pelanggan tidak boleh kosong.';
    }
}

echo json_encode($response);