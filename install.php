<?php

require_once __DIR__ . '/includes/db.php';


if (!db_ready()) {
    $host = DB_HOST; $user = DB_USER; $pass = DB_PASS; $dbname = DB_NAME; $port = DB_PORT;
    $bootstrap = @new mysqli($host, $user, $pass, '', $port);
    if (!$bootstrap->connect_error) {
        $bootstrap->query("CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $bootstrap->close();
    }
    $conn = @new mysqli($host, $user, $pass, $dbname, $port);
    if ($conn->connect_error) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
        echo "HerMart installer could not connect to MySQL.\n\n";
        echo "Create includes/config.local.php from includes/config.local.example.php\n";
        echo "and set DB_HOST / DB_USER / DB_PASS / DB_NAME / DB_PORT.\n";
        echo "MySQL said: " . htmlspecialchars($conn->connect_error) . "\n";
        exit;
    }
    $conn->set_charset('utf8mb4');
}

$messages = [];
$safe_db = preg_match('/^[A-Za-z0-9_]+$/', $dbname) ? $dbname : 'hermart';
$messages[] = $conn->query("CREATE DATABASE IF NOT EXISTS `{$safe_db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
    ? "database '{$safe_db}' ready"
    : "database '{$safe_db}' already exists";

$conn->query("USE `{$safe_db}`");

function run_sql_file($conn, $file, $messages) {
    $sql = file_get_contents($file);
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt === '' || strlen($stmt) < 5) continue;
        $r = $conn->query($stmt);
        if (!$r) {
            $messages[] = "ERROR: " . $conn->error . "\nQuery: " . substr($stmt, 0, 80);
        }
    }
    return $messages;
}

$messages = run_sql_file($conn, __DIR__ . '/database/database.sql', $messages);


$pw = password_hash('12345678', PASSWORD_DEFAULT);

$seedUsers = [
    ['Ayesha Rahman',   'customer@hermart.com', '01700000001', 'customer', 'Banani, Dhaka',         '01700000001'],
    ['Sumaiya Hossain', 'sumaiya@hermart.com',  '01700000002', 'customer', 'Agrabad, Chattogram',   '01700000002'],
    ['Rina Akter',      'seller@hermart.com',   '01800000001', 'seller',   'Sadar, Sylhet',         '01800000001'],
    ['Borni Akter',     'borni@gmail.com',      '01751304671', 'seller',   'Pabna Sadar',           '01751304671'],
    ['Fatiha Khatun',   'fatiha@hermart.com',   '01700000003', 'customer', 'Uttara, Dhaka',         '01700000003'],
    ['Nazma Begum',     'nazma@hermart.com',    '01700000004', 'seller',   'Dinajpur Sadar',        '01700000004'],
    ['Selina Akhter',   'selina@hermart.com',   '01700000005', 'seller',   'Rangpur Sadar',         '01700000005'],
    ['Mariam Hossain',  'mariam@hermart.com',   '01700000006', 'seller',   'Rajshahi Sadar',        '01700000006'],
    ['Admin HerMart',   'admin@hermart.com',    '01900000000', 'admin',    'Banani, Dhaka',         '01900000000'],
];

$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password_hash, role, address) VALUES (?, ?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), name=VALUES(name), role=VALUES(role), address=VALUES(address), phone=VALUES(phone)");
foreach ($seedUsers as $u) {
    $stmt->bind_param('ssssss', $u[0], $u[1], $u[2], $pw, $u[3], $u[4]);
    $stmt->execute();
}
$stmt->close();
$messages[] = "seed users installed with password '12345678'";


$biz = [
    [3, 'Rina Nakshi House',         5, 'Sylhet',     'Sylhet',        'Sadar',           'Zindabazar, Sylhet',      '01800000001', 'https://facebook.com/rinanakshi',     'https://shop.rina-hermart.bd', 'Handwoven Nakshi Kantha and traditional Sylheti crafts.', 1, 4.9],
    [4, 'Borni Homemade Pickles',    3, 'Rajshahi',   'Pabna',         'Pabna Sadar',     'Hemayetpur, Pabna',       '01751304671', 'https://facebook.com/bornipickles',   'https://shop.borni-hermart.bd', 'Authentic homemade mango, olive and chilli pickles.', 1, 4.8],
    [6, 'Dinajpur Traditional Foods',3, 'Rangpur',    'Dinajpur',      'Dinajpur Sadar',  'Chirirbandar Road',       '01700000004', 'https://facebook.com/dinajpurfoods',  NULL,                                 'Pahari rice, dried fish and traditional sweets.', 0, 4.4],
    [7, 'Selina Boutique',           2, 'Rangpur',    'Rangpur',       'Rangpur Sadar',   'Shapla Chottor, Rangpur', '01700000005', 'https://facebook.com/selinaboutique', 'https://selina-hermart.bd',         'Three-piece, saree and tailor-made clothing.', 1, 4.7],
    [8, 'Mariam Pahari Crafts',      7, 'Rajshahi',   'Rajshahi',      'Rajshahi Sadar',  'Talaimari, Rajshahi',     '01700000006', 'https://facebook.com/mariamcrafts',   NULL,                                 'Pahari bamboo crafts and indigenous home decor.', 0, 4.5],
    [3, 'Sylhet Jewelry Studio',     8, 'Sylhet',     'Moulvibazar',   'Sreemangal',      'Tea Garden Road',         '01800000001', 'https://facebook.com/sylhetjewelry',  NULL,                                 'Handcrafted traditional silver jewelry.', 1, 4.8],
    [4, 'Pabna Nakshi & Crafts',     5, 'Rajshahi',   'Pabna',         'Ishwardi',        'Ishwardi Bazar',          '01751304671', 'https://facebook.com/pabnakantha',    NULL,                                 'Traditional Nakshi Kantha & embroidered bedsheets.', 0, 4.6],
    [8, 'Rajshahi Handicrafts',      4, 'Rajshahi',   'Rajshahi',      'Boalia',          'Seroil Road',             '01700000006', 'https://facebook.com/rajhandicraft',  'https://raj-hermart.bd',            'Handmade home decor and wooden crafts.', 1, 4.7],
    [6, 'Dinajpur Pahari Honey',     3, 'Rangpur',    'Dinajpur',      'Birampur',        'Birampur Bazar',          '01700000004', 'https://facebook.com/paharihoney',    NULL,                                 'Pure Pahari honey from Sundarbans foothills.', 0, 4.5],
    [7, 'Rangpur Bedding & Decor',   4, 'Rangpur',    'Rangpur',       'Pirganj',         'Pirganj Road',            '01700000005', NULL,                                  NULL,                                 'Bed covers, cushions and rural home decor.', 1, 4.6],
];

$bizStmt = $conn->prepare("INSERT INTO businesses (owner_id, shop_name, category_id, division, district, upazila, address, phone, facebook, online_shop, description, verified, rating)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE shop_name=VALUES(shop_name), description=VALUES(description), verified=VALUES(verified), rating=VALUES(rating)");
foreach ($biz as $b) {
    $bizStmt->bind_param('isissssssssid', $b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8], $b[9], $b[10], $b[11], $b[12]);
    $bizStmt->execute();
}
$bizStmt->close();
$messages[] = "seed businesses installed";


$products = [
    [1, 5, 'Traditional Nakshi Kantha Quilt',  'Hand-stitched multicolor Nakshi Kantha bedspread (queen size).', 4500.00, null, 8,  'NK-001', 'Cotton',     'Multicolor', 'Queen',  'nakshi1.jpg', 'active'],
    [1, 5, 'Sylheti Traditional Saree',        'Handwoven Sylheti cotton saree with traditional motif.',      3200.00, 2800.00, 5, 'SR-002', 'Cotton',     'White',     'Free',   'saree1.jpg', 'active'],
    [2, 3, 'Homemade Mango Pickle',            'Authentic homemade aam achar - 500g jar.',                    350.00, null, 50, 'FD-001', 'Mango',     null,        '500g',   'pickle1.jpg','active'],
    [2, 3, 'Chilli Pickle (Naga)',             'Spicy naga chilli pickle - 400g jar.',                       380.00, null, 40, 'FD-002', 'Chilli',    null,        '400g',   'pickle2.jpg','active'],
    [3, 3, 'Pahari Dried Fish',                'Sun-dried Pahari fish - 250g pack.',                         600.00, null, 20, 'FD-003', 'Fish',      null,        '250g',   'fish1.jpg', 'active'],
    [4, 2, 'Three-Piece Suit (Georgette)',     'Readymade georgette three-piece with embroidery.',           2800.00, null, 12, 'FC-001', 'Georgette', 'Pink',      'M',      'three1.jpg','active'],
    [4, 2, 'Cotton Saree (Hand Block Print)',  'Hand block-printed cotton saree - traditional design.',      1800.00, null, 25, 'FC-002', 'Cotton',    'Blue',      'Free',   'saree2.jpg','active'],
    [5, 7, 'Bamboo Pen Stand Set',             'Handmade bamboo pen stand set - 3 pieces.',                  450.00, null, 30, 'HC-001', 'Bamboo',    'Natural',   null,     'bamboo1.jpg','active'],
    [5, 7, 'Pahari Wall Hanging',              'Traditional Pahari wall hanging made of bamboo and cane.',   1200.00, null, 15, 'HC-002', 'Bamboo',    'Brown',     null,     'wall1.jpg', 'active'],
    [6, 8, 'Traditional Silver Necklace',      'Handcrafted tribal-style silver necklace.',                  5400.00, null, 4,  'JW-001', 'Silver',    'Silver',    null,     'jewel1.jpg','active'],
    [6, 8, 'Silver Earrings (Pair)',           'Handcrafted oxidized silver earrings.',                      950.00,  null, 18, 'JW-002', 'Silver',    'Silver',    null,     'jewel2.jpg','active'],
    [7, 5, 'Embroidered Cushion Cover',        'Hand-embroidered cushion cover - set of 2.',                 850.00,  null, 35, 'NK-003', 'Cotton',    'Red',       '16x16',  'cush1.jpg', 'active'],
    [8, 4, 'Wooden Wall Clock',                'Handcrafted wooden wall clock with rural motifs.',           1800.00, null, 9,  'HD-001', 'Wood',      'Brown',     null,     'clock1.jpg','active'],
    [8, 4, 'Hand-painted Wooden Vase',         'Decorative hand-painted wooden vase.',                       1300.00, null, 14, 'HD-002', 'Wood',      'Multicolor',null,     'vase1.jpg', 'active'],
    [9, 3, 'Pure Pahari Honey (500g)',         'Raw, unprocessed honey from Pahari region.',                 750.00,  null, 22, 'FD-004', 'Honey',     null,        '500g',   'honey1.jpg','active'],
    [10,4, 'Embroidered Bed Sheet (King)',      'Hand-embroidered bedsheet with pillow covers.',              3500.00, null, 7,  'HD-003', 'Cotton',    'White',     'King',   'bed1.jpg',  'active'],
];

$pStmt = $conn->prepare("INSERT INTO products (seller_id, category_id, name, description, price, discount_price, stock, sku, material, color, size, image, status)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), price=VALUES(price), stock=VALUES(stock), status=VALUES(status)");
foreach ($products as $p) {
    $pStmt->bind_param('iissddissssss',
        $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11], $p[12]
    );
    $pStmt->execute();
}
$pStmt->close();
$messages[] = "seed products installed";


$orders = [
    [1, 4850.00, 'Banani, Dhaka',           'Dhaka',   'Banani', '01700000001', 'cod',  'pending', 'delivered', '2025-08-12 10:32:00'],
    [2, 5400.00, 'Agrabad, Chattogram',     'Chattogram','Agrabad','01700000002','bkash','paid',    'shipped',   '2025-09-02 14:11:00'],
    [5, 1450.00, 'Uttara, Dhaka',           'Dhaka',   'Uttara', '01700000003', 'cod',  'pending', 'processing','2025-09-09 09:25:00'],
    [1, 3500.00, 'Banani, Dhaka',           'Dhaka',   'Banani', '01700000001', 'cod',  'pending', 'pending',   '2025-09-12 18:40:00'],
];

$oStmt = $conn->prepare("INSERT INTO orders (order_number, customer_id, total_amount, shipping_address, district, upazila, phone, payment_method, payment_status, order_status, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE order_status=VALUES(order_status), payment_status=VALUES(payment_status)");
$orderIds = [1,2,3,4];
$count = 0;
foreach ($orders as $o) {
    $orderNum = 'HM-' . str_pad((string)((int)$orderIds[$count]), 6, '0', STR_PAD_LEFT);
    $oStmt->bind_param('sidssssssss',
        $orderNum, $o[0], $o[1], $o[2], $o[3], $o[4], $o[5], $o[6], $o[7], $o[8], $o[9]
    );
    $oStmt->execute();
    $count++;
}
$oStmt->close();
$messages[] = "seed orders installed";


$items = [
    [1, 1, 1, 4500.00, 4500.00],
    [1, 3, 2, 1, 350.00, 350.00],
    [2, 10, 6, 1, 5400.00, 5400.00],
    [3, 6, 4, 1, 1450.00, 1450.00],
    [4, 16, 10, 1, 3500.00, 3500.00],
];
$oiStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, product_name, quantity, unit_price, subtotal)
                          VALUES (?, ?, ?, (SELECT name FROM products WHERE id = ?), ?, ?, ?)
                          ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)");
foreach ($items as $it) {
    $oiStmt->bind_param('iiiisid',
        $it[0], $it[1], $it[2], $it[1], $it[3], $it[4], $it[5]
    );
    $oiStmt->execute();
}
$oiStmt->close();
$messages[] = "seed order items installed";


$payStmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, transaction_reference, payment_status, paid_at)
                           VALUES (?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE payment_status=VALUES(payment_status)");
$payments = [
    [1, 'cod', 4850.00, 'COD-' . str_pad('1',8,'0',STR_PAD_LEFT), 'pending', null],
    [2, 'bkash', 5400.00, 'BKH-' . str_pad('2',8,'0',STR_PAD_LEFT), 'paid', '2025-09-02 14:30:00'],
    [3, 'cod', 1450.00, 'COD-' . str_pad('3',8,'0',STR_PAD_LEFT), 'pending', null],
    [4, 'cod', 3500.00, 'COD-' . str_pad('4',8,'0',STR_PAD_LEFT), 'pending', null],
];
foreach ($payments as $p) {
    $payStmt->bind_param('isdsss', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]);
    $payStmt->execute();
}
$payStmt->close();
$messages[] = "seed payments installed";


$delStmt = $conn->prepare("INSERT INTO deliveries (order_id, customer_id, address, district, upazila, tracking_number, status, estimated_delivery, delivered_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status=VALUES(status)");
$deliveries = [
    [1, 1, 'Banani, Dhaka',         'Dhaka',  'Banani',  'TRK-0001', 'delivered',         '2025-08-15', '2025-08-15 14:00:00'],
    [2, 2, 'Agrabad, Chattogram',   'Chattogram','Agrabad','TRK-0002', 'shipped',          '2025-09-05', null],
    [3, 5, 'Uttara, Dhaka',         'Dhaka',  'Uttara',  'TRK-0003', 'processing',        '2025-09-12', null],
    [4, 1, 'Banani, Dhaka',         'Dhaka',  'Banani',  'TRK-0004', 'pending',           '2025-09-16', null],
];
foreach ($deliveries as $d) {
    $delStmt->bind_param('iisssssss', $d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8]);
    $delStmt->execute();
}
$delStmt->close();
$messages[] = "seed deliveries installed";


$revStmt = $conn->prepare("INSERT INTO reviews (customer_id, product_id, seller_id, order_id, rating, review_text, status)
                           VALUES (?, ?, ?, ?, ?, ?, 'active')
                           ON DUPLICATE KEY UPDATE review_text=VALUES(review_text)");
$reviews = [
    [1, 1, 1, 1, 5, 'Absolutely beautiful Nakshi Kantha. Stitching is flawless!'],
    [2, 3, 2, null, 5, 'Best homemade pickle I have ever tasted.'],
    [5, 6, 4, 3, 4, 'Loved the fabric and the fitting. Will order again.'],
    [1, 13, 8, null, 5, 'Wooden clock is a real showpiece. Great craftsmanship.'],
    [2, 10, 6, 2, 5, 'Silver necklace is gorgeous. Delivery was quick.'],
    [1, 4, 2, null, 5, 'The Naga chilli pickle is so flavourful — perfect with rice.'],
    [5, 11, 6, null, 4, 'Beautiful earrings, exactly as shown in the picture.'],
];
foreach ($reviews as $r) {
    $revStmt->bind_param('iiiiss', $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]);
    $revStmt->execute();
}
$revStmt->close();
$messages[] = "seed reviews installed";


$coStmt = $conn->prepare("INSERT INTO custom_orders (customer_id, seller_id, title, description, budget, quantity, preferred_date, location, status)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE status=VALUES(status)");
$customOrders = [
    [1, 1, 'Custom king-size Nakshi Kantha',   'Custom king-size Nakshi Kantha with floral border',     1, 6000.00, '2025-10-15', 'Banani, Dhaka',       'accepted'],
    [5, 4, 'Custom three-piece with dupatta',   'Custom three-piece with matching dupatta for wedding',  2, 8000.00, '2025-10-30', 'Uttara, Dhaka',       'quoted'],
    [2, 6, 'Bridal necklace and earring set',   'Bridal necklace and earring set in silver',             1, 12000.00,'2025-11-12', 'Agrabad, Chattogram', 'pending'],
    [1, 8, 'Custom wooden name plate',          'Custom wooden name plate with family name carved',      1, 2500.00, '2025-10-20', 'Banani, Dhaka',       'in_progress'],
    [5, 2, 'Custom mango pickle bulk order',    'Bulk homemade mango pickle for a wedding',              50,15000.00,'2025-10-10', 'Uttara, Dhaka',       'reviewed'],
];
foreach ($customOrders as $c) {
    $coStmt->bind_param('iissidsss', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8]);
    $coStmt->execute();
}
$coStmt->close();
$messages[] = "seed custom orders installed";


$bkStmt = $conn->prepare("INSERT INTO bookings (customer_id, vendor_id, service_id, service_name, event_type, booking_date, booking_time, location, notes, status)
                          VALUES (?, ?, ?, (SELECT name FROM services WHERE id = ?), ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE status=VALUES(status)");
$bookings = [
    [1, 4, 3, 'Wedding Mehendi',     '2025-10-20', '16:00:00', 'Banani, Dhaka',         'Need mehendi for 12 hands',         'accepted'],
    [5, 2, 2, 'Birthday Party',      '2025-10-05', '13:00:00', 'Uttara, Dhaka',         'Vegetarian menu for 30 guests',     'pending'],
    [2, 1, 1, 'Custom Tailoring',    '2025-09-30', '11:00:00', 'Agrabad, Chattogram',   'Bridal blouse work',                'accepted'],
    [1, 8, 6, 'Baby Sitting',         '2025-10-12', '09:00:00', 'Banani, Dhaka',         'For 2-year-old, 4 hours',           'completed'],
    [5, 5, 5, 'Home Cleaning',        '2025-10-25', '10:00:00', 'Uttara, Dhaka',         'Full house deep cleaning',          'pending'],
];
foreach ($bookings as $b) {
    $bkStmt->bind_param('iiiissssss', $b[0], $b[1], $b[2], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8]);
    $bkStmt->execute();
}
$bkStmt->close();
$messages[] = "seed bookings installed";


$notifStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, ?, ?, ?, ?)");
$notifications = [
    [1, 'order', 'Order #1 has been delivered', 'Your Nakshi Kantha order has reached you. Please leave a review.', 'pages/orders.php'],
    [1, 'custom', 'Your custom order was accepted', 'Rina accepted your custom Nakshi Kantha request.', 'pages/customer-profile.php'],
    [1, 'booking', 'Mehendi booking confirmed', 'Selina confirmed your mehendi booking for Oct 20.', 'pages/customer-profile.php'],
    [2, 'order', 'Order #2 shipped', 'Your silver necklace is on the way.', 'pages/orders.php'],
    [5, 'order', 'Order #3 is being processed', 'Selina is preparing your three-piece.', 'pages/orders.php'],
];
foreach ($notifications as $n) {
    $notifStmt->bind_param('issss', $n[0], $n[1], $n[2], $n[3], $n[4]);
    $notifStmt->execute();
}
$notifStmt->close();
$messages[] = "seed notifications installed";

header('Content-Type: text/plain; charset=utf-8');
echo "HerMart installation complete.\n\n";
foreach ($messages as $m) echo "• $m\n";
echo "\nDemo logins (password = 12345678):\n";
echo "  Customer → customer@hermart.com\n";
echo "  Customer → sumaiya@hermart.com\n";
echo "  Customer → fatiha@hermart.com\n";
echo "  Seller   → seller@hermart.com\n";
echo "  Seller   → borni@gmail.com\n";
echo "  Admin    → admin@hermart.com\n";
