<?php
session_start();
// Memeriksa apakah customer sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Memasukkan file koneksi database
require_once '../config.php';

// Mengambil data customer dari session
$customer_name = $_SESSION['customer_name'];

// --- BAGIAN 1: Mengambil data layanan yang dimiliki customer ---
$usage_details = [];
$customer_owned_services = []; // Array untuk menyimpan nama layanan yg sudah dimiliki

$stmt_customer = $conn->prepare("SELECT id, api_key, service_type, daily_request_limit FROM customers WHERE customer_name = ? AND is_active = 1");
if ($stmt_customer) {
    $stmt_customer->bind_param("s", $customer_name);
    $stmt_customer->execute();
    $result_customer = $stmt_customer->get_result();
    $today = date("Y-m-d");

    while ($customer_data = $result_customer->fetch_assoc()) {
        // Simpan nama layanan yg dimiliki
        $customer_owned_services[] = $customer_data['service_type'];
        
        $customer_id = $customer_data['id'];
        $request_count_today = 0;

        $stmt_usage = $conn->prepare("SELECT request_count FROM customer_api_usage WHERE customer_id = ? AND usage_date = ?");
        if ($stmt_usage) {
            $stmt_usage->bind_param("is", $customer_id, $today);
            $stmt_usage->execute();
            $result_usage = $stmt_usage->get_result();
            if ($usage_data = $result_usage->fetch_assoc()) {
                $request_count_today = $usage_data['request_count'];
            }
            $stmt_usage->close();
        }

        $limit = $customer_data['daily_request_limit'];
        $status = ($limit > 0 && $request_count_today >= $limit)
            ? '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Limit Tercapai</span>'
            : '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>';

        $usage_details[] = [
            'service_type' => $customer_data['service_type'],
            'full_api_key' => $customer_data['api_key'],
            'masked_api_key' => substr($customer_data['api_key'], 0, 4) . '...' . substr($customer_data['api_key'], -4),
            'usage' => $request_count_today . ' / ' . $limit,
            'status' => $status,
        ];
    }
    $stmt_customer->close();
}

$all_available_services = [];
$result_all_services = $conn->query("SELECT id, product_name, description FROM products WHERE is_active = 1");
if ($result_all_services) {
    while ($row = $result_all_services->fetch_assoc()) {
        // Cek jika customer belum punya layanan ini
        if (!in_array($row['product_name'], $customer_owned_services)) {
            $all_available_services[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4 md:p-8">
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-0">
                    Welcome, <?php echo htmlspecialchars($customer_name); ?>!
                </h2>
                <a href="logout.php" class="text-sm bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200 w-full md:w-auto text-center">
                    Logout
                </a>
            </div>

            <!-- Ringkasan Layanan Anda -->
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Your Service Summary</h3>
            <div class="overflow-x-auto mb-10">
                <?php if (!empty($usage_details)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <!-- (Isi tabel tetap sama seperti sebelumnya) -->
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Service Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">API Key</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Today's Usage</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($usage_details as $detail): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($detail['service_type']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div class="flex items-center space-x-2">
                                            <code class="api-key font-mono" data-full-key="<?php echo htmlspecialchars($detail['full_api_key']); ?>">
                                                <?php echo htmlspecialchars($detail['masked_api_key']); ?>
                                            </code>
                                            <button class="toggle-vis bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs font-bold py-1 px-2 rounded-md" type="button">Show</button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono"><?php echo htmlspecialchars($detail['usage']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $detail['status']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 rounded-r-lg" role="alert">
                        <p class="font-bold">No Services Found</p>
                        <p>You do not have any active services at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- (BAGIAN BARU) Layanan Lain yang Tersedia -->
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Other Available Services</h3>
        <?php if (!empty($all_available_services)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($all_available_services as $service): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $service['product_name']))); ?></h4>
                            <!-- MODIFIKASI: Gunakan deskripsi dari database -->
                            <p class="text-gray-600 text-sm mt-2 h-16 overflow-hidden"><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                        <!-- MODIFIKASI: Ubah tombol menjadi link ke halaman detail -->
                        <a href="product_detail.php?id=<?php echo $service['id']; ?>" class="text-center mt-4 bg-blue-100 text-blue-800 text-sm font-semibold py-2 px-4 rounded-lg hover:bg-blue-200 transition-colors w-full">
                            Learn More
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-r-lg" role="alert">
                <p class="font-bold">You've Got It All!</p>
                <p>You are already subscribed to all of our available services. Thank you!</p>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <script>
    // (JavaScript untuk tombol Show/Hide tetap sama)
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-vis');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const apiKeyElement = this.previousElementSibling;
                const fullKey = apiKeyElement.getAttribute('data-full-key');
                const maskedKey = fullKey.substring(0, 4) + '...' + fullKey.substring(fullKey.length - 4);
                if (apiKeyElement.textContent.trim() === maskedKey) {
                    apiKeyElement.textContent = fullKey;
                    this.textContent = 'Hide';
                } else {
                    apiKeyElement.textContent = maskedKey;
                    this.textContent = 'Show';
                }
            });
        });
    });
    </script>
</body>
</html>