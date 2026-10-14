<?php
require __DIR__ . '/config/database.php';

$conn = getDbConnection();
if (!$conn) {
    echo "Database connection failed. Import the SQL file first and confirm MySQL is running.\n";
    exit(1);
}

$productCount = $conn->query('SELECT COUNT(*) AS total FROM products');
if ($productCount && $productCount->fetch_assoc()['total'] > 0) {
    $conn->query('DELETE FROM product_images');
    $conn->query('DELETE FROM products');
    echo "Existing demo product records reset. Re-seeding product records...\n";
}

$merchantId = null;
$brandId = null;
$categoryId = null;

$merchantResult = $conn->query("SELECT id FROM merchants WHERE email = 'merchant@sohi.com' LIMIT 1");
if ($merchantResult && $merchantResult->num_rows > 0) {
    $merchantId = (int) $merchantResult->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO merchants (name, email, phone, address, commission, status) VALUES ('SOHI Merchant', 'merchant@sohi.com', '01700000000', 'Dhaka, Bangladesh', 10.00, 1)");
    $merchantId = $conn->insert_id;
}

$brandResult = $conn->query("SELECT id FROM brands WHERE slug = 'sohi' LIMIT 1");
if ($brandResult && $brandResult->num_rows > 0) {
    $brandId = (int) $brandResult->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO brands (name, slug, logo, status) VALUES ('SOHI', 'sohi', 'img/logo.png', 1)");
    $brandId = $conn->insert_id;
}

$categoryResult = $conn->query("SELECT id FROM categories WHERE slug = 'women' LIMIT 1");
if ($categoryResult && $categoryResult->num_rows > 0) {
    $categoryId = (int) $categoryResult->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO categories (name, slug, description, status) VALUES ('Women', 'women', 'Women fashion collection', 1)");
    $categoryId = $conn->insert_id;
}

$products = [
    ['name' => 'Classic Denim Jacket', 'slug' => 'classic-denim-jacket', 'sku' => 'SOHI-001', 'price' => 4200.00, 'sale_price' => 3600.00, 'featured' => 1, 'image' => 'img/product/product-1.jpg'],
    ['name' => 'Piqué Biker Jacket', 'slug' => 'piqué-biker-jacket', 'sku' => 'SOHI-002', 'price' => 3900.00, 'sale_price' => 3300.00, 'featured' => 1, 'image' => 'img/product/product-2.jpg'],
    ['name' => 'Multi-pocket Chest Bag', 'slug' => 'multi-pocket-chest-bag', 'sku' => 'SOHI-003', 'price' => 2500.00, 'sale_price' => 2100.00, 'featured' => 0, 'image' => 'img/product/product-3.jpg'],
    ['name' => 'Diagonal Textured Cap', 'slug' => 'diagonal-textured-cap', 'sku' => 'SOHI-004', 'price' => 1800.00, 'sale_price' => 1500.00, 'featured' => 1, 'image' => 'img/product/product-4.jpg'],
    ['name' => 'Flowing Cotton Dress', 'slug' => 'flowing-cotton-dress', 'sku' => 'SOHI-005', 'price' => 5200.00, 'sale_price' => 4700.00, 'featured' => 1, 'image' => 'img/product/product-5.jpg'],
    ['name' => 'Luxury Leather Tote', 'slug' => 'luxury-leather-tote', 'sku' => 'SOHI-006', 'price' => 6100.00, 'sale_price' => 5400.00, 'featured' => 0, 'image' => 'img/product/product-6.jpg'],
];

$stmt = $conn->prepare('INSERT INTO products (category_id, brand_id, merchant_id, name, slug, sku, short_description, description, price, sale_price, featured, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');

foreach ($products as $product) {
    $shortDescription = 'Premium fashion item for everyday style.';
    $description = 'A premium fashion product designed for modern living and standout style.';
    $stmt->bind_param(
        'iiisssssddi',
        $categoryId,
        $brandId,
        $merchantId,
        $product['name'],
        $product['slug'],
        $product['sku'],
        $shortDescription,
        $description,
        $product['price'],
        $product['sale_price'],
        $product['featured']
    );
    $stmt->execute();

    $productId = $stmt->insert_id;

    $imgStmt = $conn->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, 0)');
    $imgStmt->bind_param('is', $productId, $product['image']);
    $imgStmt->execute();
    $imgStmt->close();
}

$stmt->close();

echo "Inserted demo products successfully.\n";
