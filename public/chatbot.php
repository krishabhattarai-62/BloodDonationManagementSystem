<?php

require '../config/db.php';

$apiKey = OPENROUTER_API_KEY;

// FETCH ADMIN CONTACT FROM DB

$adminStmt = $pdo->prepare("
    SELECT first_name, last_name, email, contact_number, address 
    FROM users 
    WHERE role = 'admin' 
    LIMIT 1
");
$adminStmt->execute();
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

$adminName = $admin ? $admin['first_name'] . ' ' . $admin['last_name'] : 'Admin';
$adminEmail = $admin ? $admin['email'] : 'N/A';
$adminPhone = $admin ? $admin['contact_number'] : 'N/A';
$adminAddress = $admin ? $admin['address'] : 'N/A';

// =========================================================
// USER MESSAGE
// =========================================================

$message = strtolower(trim($_POST['message'] ?? ''));

// =========================================================
// USER LOCATION
// =========================================================

$userLat = floatval($_POST['latitude'] ?? 0);
$userLng = floatval($_POST['longitude'] ?? 0);

$hasLocation = ($userLat !== 0.0 && $userLng !== 0.0);

// =========================================================
// DETECT NEARBY SEARCH
// =========================================================

$nearbyKeywords = [
    'nearby',
    'near me',
    'close',
    'around me',
    'within',
    'location',
    'km',
    'kilometer',
    'distance'
];

$askingNearby = false;

foreach ($nearbyKeywords as $kw) {
    if (str_contains($message, $kw)) {
        $askingNearby = true;
        break;
    }
}

// =========================================================
// DETECT HELP / CONTACT QUERIES
// =========================================================

$helpKeywords = ['help', 'contact', 'support', 'reach', 'email', 'phone', 'assist', 'problem', 'issue', 'question'];
$askingHelp = false;

foreach ($helpKeywords as $kw) {
    if (str_contains($message, $kw)) {
        $askingHelp = true;
        break;
    }
}

// =========================================================
// EXTRACT SEARCH RADIUS
// =========================================================

$radius = 50;

if (preg_match('/(\d+)\s*km/i', $message, $kmMatch)) {
    $radius = intval($kmMatch[1]);
}

// =========================================================
// VARIABLES
// =========================================================

$bloodInfo = "";
$donorInfo = "";
$nearbyInfo = "";

// =========================================================
// DETECT BLOOD GROUP
// =========================================================

if (preg_match('/\b(ab|a|b|o)[+\-]/i', $message, $match)) {

    $blood = strtoupper($match[0]);

    // =====================================================
    // CASE 1: NEARBY DONOR SEARCH
    // =====================================================

    if ($askingNearby && $hasLocation) {

        $stmt = $pdo->prepare("
            SELECT
                u.first_name,
                u.last_name,
                u.contact_number,
                u.location_name,
                d.units,

                ROUND(
                    6371 * ACOS(
                        LEAST(
                            1.0,
                            GREATEST(
                                -1.0,
                                COS(RADIANS(:lat)) *
                                COS(RADIANS(u.latitude)) *
                                COS(
                                    RADIANS(u.longitude) -
                                    RADIANS(:lng)
                                ) +
                                SIN(RADIANS(:lat2)) *
                                SIN(RADIANS(u.latitude))
                            )
                        )
                    ),
                    1
                ) AS distance_km

            FROM donations d

            JOIN users u
            ON u.id = d.user_id

            WHERE
                d.blood_group = :blood
                AND d.units > 0
                AND u.latitude IS NOT NULL
                AND u.longitude IS NOT NULL

            HAVING distance_km <= :radius

            ORDER BY distance_km ASC

            LIMIT 5
        ");

        $stmt->execute([
            ':lat' => $userLat,
            ':lat2' => $userLat,
            ':lng' => $userLng,
            ':blood' => $blood,
            ':radius' => $radius
        ]);

        $nearbyDonors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($nearbyDonors) {

            $list = "";

            foreach ($nearbyDonors as $i => $d) {
                $num = $i + 1;
                $loc = $d['location_name'] ?? 'Unknown location';
                $list .=
                    "$num. {$d['first_name']} {$d['last_name']} — " .
                    "{$d['contact_number']} | " .
                    "$loc ({$d['distance_km']} km away) | " .
                    "{$d['units']} units\n";
            }

            $nearbyInfo = "Nearby $blood donors within {$radius} km:\n\n$list";

        } else {

            $nearbyInfo = "No $blood donors found within {$radius} km.";
        }
    }

    // =====================================================
    // CASE 2: GENERAL BLOOD AVAILABILITY
    // =====================================================
    else {

        $stmt = $pdo->prepare("
            SELECT SUM(units) as units
            FROM donations
            WHERE blood_group = ?
        ");

        $stmt->execute([$blood]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['units'] > 0) {

            $bloodInfo =
                "Blood group $blood is available with " .
                $row['units'] .
                " units in stock.";

            // =================================================
            // CHECK IF ASKING FOR DONOR DETAILS
            // =================================================

            $donorKeywords = [
                'donor',
                'contact',
                'who',
                'name',
                'number',
                'phone',
                'info',
                'details',
                'person'
            ];

            $askingForDonor = false;

            foreach ($donorKeywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $askingForDonor = true;
                    break;
                }
            }

            // =================================================
            // GET DONOR DETAILS
            // =================================================

            if ($askingForDonor) {

                $stmt2 = $pdo->prepare("
                    SELECT
                        u.first_name,
                        u.last_name,
                        u.contact_number,
                        u.location_name

                    FROM donations d

                    JOIN users u
                    ON u.id = d.user_id

                    WHERE
                        d.blood_group = ?
                        AND d.units > 0

                    LIMIT 5
                ");

                $stmt2->execute([$blood]);

                $donors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                if ($donors) {

                    $donorList = "";

                    foreach ($donors as $i => $donor) {
                        $num = $i + 1;
                        $loc = $donor['location_name'] ?? '';
                        $locText = $loc ? " | $loc" : "";
                        $donorList .=
                            "$num. {$donor['first_name']} {$donor['last_name']} — " .
                            "{$donor['contact_number']}$locText\n";
                    }

                    $donorInfo = "Available donors for $blood:\n\n$donorList";

                } else {

                    $donorInfo = "No donor details found for $blood.";
                }
            }

        } else {

            $bloodInfo = "Blood group $blood is currently NOT available.";
        }
    }
}

// =========================================================
// BUILD AI PROMPT
// =========================================================

$dbData = trim("$nearbyInfo\n$bloodInfo\n$donorInfo");

if ($dbData) {

    $prompt = "
You are a blood donation assistant.

IMPORTANT DATABASE DATA:
$dbData

Answer ONLY using the data above.

Keep replies:
- short
- friendly
- clear

User asked:
$message
";

} else {

    $prompt = "
You are a blood donation assistant.

Only answer blood donation related questions.

Keep replies short and friendly.

User asked:
$message
";
}

// =========================================================
// OPENROUTER FREE MODELS
// =========================================================

$freeModels = [
    "google/gemma-3-4b-it:free",
    "meta-llama/llama-3.1-8b-instruct:free",
    "mistralai/mistral-small-3.2-24b-instruct:free"
];

// =========================================================
// OPENROUTER API REQUEST
// =========================================================

$url = "https://openrouter.ai/api/v1/chat/completions";

$aiResponse = null;

foreach ($freeModels as $model) {

    $data = [
        "model" => $model,
        "messages" => [
            [
                "role" => "system",
                "content" => "You are a helpful blood donation assistant."
            ],
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "max_tokens" => 200,
        "temperature" => 0.3
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey",
            "HTTP-Referer: http://localhost"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        continue;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if (!$response)
        continue;

    $result = json_decode($response, true);

    if (isset($result['error']))
        continue;

    if (
        $httpCode === 200 &&
        isset($result['choices'][0]['message']['content'])
    ) {
        $aiResponse = trim($result['choices'][0]['message']['content']);
        break;
    }
}

// =========================================================
// FINAL OUTPUT
// =========================================================

if ($aiResponse) {

    echo nl2br(htmlspecialchars($aiResponse));

} elseif ($nearbyInfo) {

    echo nl2br(htmlspecialchars($nearbyInfo));

} elseif ($donorInfo) {

    echo nl2br(htmlspecialchars($donorInfo));

} elseif ($bloodInfo) {

    echo nl2br(htmlspecialchars($bloodInfo));

} else {

    // =====================================================
    // SMART FALLBACK RESPONSES
    // =====================================================

    // Help / Contact Us
    if ($askingHelp) {

        echo "
        <strong>Contact and Support</strong><br><br>

        Need help? Reach out to our admin:<br><br>

        Name: <strong>" . htmlspecialchars($adminName) . "</strong><br>
        Email: <strong>" . htmlspecialchars($adminEmail) . "</strong><br>
        Phone: <strong>" . htmlspecialchars($adminPhone) . "</strong><br>
        Address: <strong>" . htmlspecialchars($adminAddress) . "</strong><br><br>

        You can also:<br>
        - <a href='/public/donor_request.php'>Request Blood</a><br>
        - <a href='/public/schedule_donation.php'>Schedule a Donation</a><br>
        - <a href='/public/user_notification.php'>Check Notifications</a>
        ";
    }

    // Nearby donor asked without blood group
    elseif (
        str_contains($message, 'nearby') ||
        str_contains($message, 'near me') ||
        str_contains($message, 'donor')
    ) {
        echo "
        Please specify a blood group.<br><br>

        Example:<br>
        - O+ donor nearby<br>
        - A- blood within 10 km
        ";
    }

    // Greetings
    elseif (
        $message === 'hello' ||
        $message === 'hi' ||
        $message === 'hey'
    ) {
        echo "Hello! How can I help you with blood donation today?";
    }

    // Help command
    elseif ($message === 'help') {
        echo "
        You can ask things like:<br><br>

        - Is A+ blood available?<br>
        - Find O- donors nearby<br>
        - Need AB+ blood within 10 km<br>
        - Show B+ donor contact details<br>
        - How can I contact support?
        ";
    }

    // Unknown queries
    else {
        echo "
        I can help with blood donation queries.<br><br>

        Try asking:<br>
        - Is O+ blood available?<br>
        - Find A- donors nearby<br>
        - Need AB+ blood within 5 km<br>
        - How can I contact support?
        ";
    }
}

?>