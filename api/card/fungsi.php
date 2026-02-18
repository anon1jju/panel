<?php

function apply_curl_proxy_settings(&$ch, $proxyAddress, $proxyUserPwd)
{
    curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUserPwd);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/ca.crt');
}

function get_random_user_data($randuser)
{
    $apiUrl = 'https://randomuser.me/api/?nat=us';
    $ch = curl_init();
    //apply_curl_proxy_settings($ch, $proxyAddress, $proxyUserPwd);
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: ' . $randuser]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => 'Gagal menghubungi API randomuser.me: ' . curl_error($ch)];
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data['results'][0])) {
        return ['error' => 'Gagal memproses data JSON dari randomuser.me.'];
    }
    $user = $data['results'][0];
    $phone = preg_replace('/[^0-9]/', '', $user['phone']);
    $formattedUser = [
        'first_name' => $user['name']['first'],
        'last_name' => $user['name']['last'],
        'email' => $user['email'],
        'phone' => $phone,
        'address1' => $user['location']['street']['name'],
        'city' => $user['location']['city'],
        'state' => $user['location']['state'],
        'postal_code' => $user['location']['postcode'],
    ];
    return $formattedUser;
}

// Set header agar output dianggap sebagai JSON oleh sistem/browser

function check_bin($bin, $randuser)
{
    // Membersihkan input: hanya ambil 6 digit pertama
    $bin = substr(preg_replace('/\D/', '', $bin), 0, 6);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://data.handyapi.com/bin/' . $bin);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: HAS-0YF7mK6RJG34H2jNL5XWYwn6', // Ganti dengan key aktifmu
        'User-Agent: ' . $randuser
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return $response; // Mengembalikan string JSON asli dari API
    } else {
        // Mengembalikan pesan error dalam format JSON yang valid
        return json_encode([
            "status" => "error",
            "http_code" => $httpCode,
            "message" => "Gagal mendapatkan data BIN"
        ]);
    }
}


function random_user_agent()
{
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; B131DL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G988U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFTRWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.2.5 like Chrome/110.0.5481.154 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/111.0.1661.62";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_1_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_7_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 15_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 6.0.1; SM-G900V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.5 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-F721U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; moto e) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S908U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S906U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S908U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.34";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A146U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A136U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_8 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 12_5_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B206 Safari/7534.48.3";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A426U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/111.0.5563.101 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SM-J737V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G781V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) GSA/256.2.518341946 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-A505U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36 Edg/86.0.622.51";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_8_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S908U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; moto g stylus) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-S115DL) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G986U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/111.0.1661.54";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) GSA/258.1.520699392 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A536V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 DuckDuckGo/7 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-G970U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G990U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.3 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-A102U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 15_7_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S515DL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-S205DL) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.5 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.3 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFTRWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.4.3 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G996U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.39";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 7.0; SM-T813) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36 EdgA/111.0.1661.59";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S901U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S906U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36 Edg/108.0.1462.54";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; SM-N960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 5.1.1; KFSUWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/108.2.18 like Chrome/108.0.5359.220 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.5 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.4 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-A135U1 Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/111.0.5563.116 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; Redmi Note 9 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-A326U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/19.0 Chrome/102.0.5005.125 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; LM-Q730) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; moto g stylus 5G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3.1 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; Pixel 6 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; GN2200) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; moto e6 (XT2005DL)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S916U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/111.0.5563.116 Mobile DuckDuckGo/5 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.46";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; moto g stylus 5G (2022)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/99.0.4844.47 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/111.0.1661.50 Version/16.0 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-J337V) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.1 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-N970U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G990U2) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.2 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S901U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SM-G950U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S901U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Mobile/15E148 DuckDuckGo/7 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; LM-V405) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S908U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 DuckDuckGo/7 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G996U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 15_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/111.0 Firefox/111.0";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; moto g(7) optimo maxx(XT1955DL)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A716V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A426U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-G970U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 12_5_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_7_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/111.2 Mobile/15E148 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-G975U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 7.1.1; SM-T350) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/111.0.1661.62 Version/16.0 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; motorola edge 5G UW (2021)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-G965U1) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A516U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; Redmi Note 9 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G991U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-N970U1) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.48";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-F711U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Android 10; Mobile; rv:109.0) Gecko/112.0 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-A025V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.88 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 5.1.1; KFFOWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/108.2.18 like Chrome/108.0.5359.220 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; SM-T510) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; LM-X410(FG)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-G970U1) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/112.0.5615.46 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G996U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36 EdgA/111.0.1661.59";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; Z5158) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-S916U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFONWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.4.3 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G950U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.5 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-T733) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/602.2.14 (KHTML, like Gecko) Version/10.0.1 Safari/602.2.14";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-S134DL) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G991U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/112.0.5615.48 Mobile DuckDuckGo/5 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/112.0.5615.46 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFMAWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.4.3 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A515U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A326U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36 Edg/108.0.1462.46";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.0 Mobile/14G60 Safari/602.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A037U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/112.0.5615.46 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 7.0; LGL64VL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) GSA/259.0.522115267 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; rv:109.0) Gecko/20100101 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-G970U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:108.0) Gecko/20100101 Firefox/108.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11; SM-T510) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G955U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-N986U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.57";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36 OPR/97.0.0.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G986U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFMUWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.6.7 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:78.0) Gecko/20100101 Firefox/78.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.68";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 11) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/112.0.5615.136 Mobile DuckDuckGo/5 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 7.1.1; SAMSUNG SM-T560NU) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/16.0 Chrome/92.0.4515.166 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G996U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/113.0";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36 Edg/113.0.1774.35";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-A716V) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.5615.138 Safari/537.36 CCleaner/112.0.21002.140";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-A215U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:109.0) Gecko/20100101 Firefox/111.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; SM-A102U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G781U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36 Edg/113.0.1774.35 Unique/92.7.9686.87";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/113.0.5672.69 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 7.1.1; SAMSUNG SM-T560NU) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-A136U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36 Edg/113.0.1774.42";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFONWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.7.1 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-N986U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/20.0 Chrome/106.0.5249.126 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-A025V) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/16.2 Chrome/92.0.4515.166 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 9; KFTRWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/110.7.1 like Chrome/110.0.5481.212 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 OPR/98.0.0.0";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/112.0.5615.135 Mobile DuckDuckGo/5 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; motorola edge 5G UW (2021)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/113.0.5672.69 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 13; SM-G996U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 4.4.3; KFTHWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/81.2.16 like Chrome/81.0.4044.138 Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Safari/605.1.15";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.100";
    $userAgentArray[] = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_7_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6.4 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 16_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/113.0.5672.121 Mobile/15E148 Safari/604.1";
    $userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36 Edg/113.0.1774.50";
    $userAgentArray[] = "Mozilla/5.0 (Linux; Android 12; SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36";
    $userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Safari/605.1.15";

    $getArrayKey = array_rand($userAgentArray);
    return $userAgentArray[$getArrayKey];
}
