<?php
require_once __DIR__ . '/../../config/database.php';

$conn = getDbConnection();
if (!$conn) {
    header('Location: ../products.php?status=error&message=' . urlencode('Database connection failed.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../products.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $conn->query("DELETE FROM product_images WHERE product_id = $id");
        $conn->query("DELETE FROM product_variants WHERE product_id = $id");
        $conn->query("DELETE FROM products WHERE id = $id");
        header('Location: ../products.php?status=success&message=' . urlencode('Product deleted successfully.'));
        exit;
    }

    header('Location: ../products.php?status=error&message=' . urlencode('Invalid product id.'));
    exit;
}

if ($action !== 'save') {
    header('Location: ../products.php?status=error&message=' . urlencode('Invalid action.'));
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$name = trim((string) ($_POST['name'] ?? ''));
$slug = trim((string) ($_POST['slug'] ?? ''));
$sku = trim((string) ($_POST['sku'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$brandId = (int) ($_POST['brand_id'] ?? 0);
$merchantId = (int) ($_POST['merchant_id'] ?? 0);
$price = (float) ($_POST['price'] ?? 0);
$salePrice = isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float) $_POST['sale_price'] : 0.00;
$costPrice = isset($_POST['cost_price']) && $_POST['cost_price'] !== '' ? (float) $_POST['cost_price'] : 0.00;
$weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? (float) $_POST['weight'] : 0.00;
$shortDescription = trim((string) ($_POST['short_description'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$imageUrl = trim((string) ($_POST['image_url'] ?? ''));
$featured = isset($_POST['featured']) ? 1 : 0;
$status = isset($_POST['status']) ? 1 : 0;

if ($name === '' || $categoryId <= 0 || $brandId <= 0 || $merchantId <= 0 || $price <= 0) {
    header('Location: ../product-form.php' . ($id ? '?id=' . $id : '') . '&status=error&message=' . urlencode('Please fill in all required fields.'));
    exit;
}

if ($slug === '') {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    if ($slug === '') {
        $slug = 'product-' . time();
    }
}

if ($sku === '') {
    $sku = 'SOHI-' . strtoupper(substr(md5($name . time()), 0, 8));
}

if ($id > 0) {
    $stmt = $conn->prepare(
        'UPDATE products SET category_id = ?, brand_id = ?, merchant_id = ?, name = ?, slug = ?, sku = ?, short_description = ?, description = ?, price = ?, sale_price = ?, cost_price = ?, weight = ?, featured = ?, status = ?, updated_at = NOW() WHERE id = ?'
    );
    $stmt->bind_param(
        'iiiissssdddiisi',
        $categoryId,
        $brandId,
        $merchantId,
        $name,
        $slug,
        $sku,
        $shortDescription,
        $description,
        $price,
        $salePrice,
        $costPrice,
        $weight,
        $featured,
        $status,
        $id
    );
    $stmt->execute();
    $stmt->close();

    if ($imageUrl !== '') {
        $existing = $conn->query("SELECT id FROM product_images WHERE product_id = $id ORDER BY sort_order ASC, id ASC LIMIT 1");
        $row = $existing ? $existing->fetch_assoc() : null;

        if ($row) {
            $imgStmt = $conn->prepare('UPDATE product_images SET image = ? WHERE id = ?');
            $imgStmt->bind_param('si', $imageUrl, $row['id']);
            $imgStmt->execute();
            $imgStmt->close();
        } else {
            $imgStmt = $conn->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, 0)');
            $imgStmt->bind_param('is', $id, $imageUrl);
            $imgStmt->execute();
            $imgStmt->close();
        }
    }

    header('Location: ../products.php?status=success&message=' . urlencode('Product updated successfully.'));
    exit;
}

$stmt = $conn->prepare(
    'INSERT INTO products (category_id, brand_id, merchant_id, name, slug, sku, short_description, description, price, sale_price, cost_price, weight, featured, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
);
$stmt->bind_param(
    'iiiissssdddiis',
    $categoryId,
    $brandId,
    $merchantId,
    $name,
    $slug,
    $sku,
    $shortDescription,
    $description,
    $price,
    $salePrice,
    $costPrice,
    $weight,
    $featured,
    $status
);
$stmt->execute();
$productId = $conn->insert_id;
$stmt->close();

if ($imageUrl !== '') {
    $imgStmt = $conn->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, 0)');
    $imgStmt->bind_param('is', $productId, $imageUrl);
    $imgStmt->execute();
    $imgStmt->close();
}

header('Location: ../products.php?status=success&message=' . urlencode('Product created successfully.'));
exit;
