<?php

function getDbConnection()
{
    static $connection = null;

    if ($connection !== null) {
        return $connection;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'sohi_attire';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    $port = (int) (getenv('DB_PORT') ?: 3306);

    $connection = @new mysqli($host, $username, $password, $database, $port);

    if ($connection->connect_error) {
        error_log('Database connection failed: ' . $connection->connect_error);
        return null;
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}

function getActiveProducts($limit = 12, $featuredOnly = false)
{
    $conn = getDbConnection();

    if (!$conn) {
        return [];
    }

    $sql = "
        SELECT p.*, COALESCE(pi.image, 'img/product/product-1.jpg') AS product_image
        FROM products p
        LEFT JOIN product_images pi
            ON pi.product_id = p.id AND pi.sort_order = 0
        WHERE p.status = 1";

    if ($featuredOnly) {
        $sql .= " AND p.featured = 1";
    }

    $sql .= " ORDER BY p.created_at DESC, p.id DESC";

    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $limit);
    } else {
        $stmt = $conn->prepare($sql);
    }

    if (!$stmt || !$stmt->execute()) {
        error_log('Product query failed: ' . ($stmt ? $stmt->error : $conn->error));
        return [];
    }

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    if (isset($stmt)) {
        $stmt->close();
    }

    return $products;
}

function formatMoney($amount)
{
    return '৳' . number_format((float) $amount, 2);
}

function productImage($product)
{
    $fallback = 'img/product/product-1.jpg';

    if (!empty($product['product_image'])) {
        return $product['product_image'];
    }

    return $fallback;
}

function getAllCategories()
{
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    $result = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllBrands()
{
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    $result = $conn->query("SELECT * FROM brands WHERE status = 1 ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllMerchants()
{
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    $result = $conn->query("SELECT * FROM merchants WHERE status = 1 ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllProductsForAdmin()
{
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }

    $sql = "
        SELECT p.*, c.name AS category_name, b.name AS brand_name, m.name AS merchant_name,
               COALESCE(pi.image, 'img/product/product-1.jpg') AS product_image
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN merchants m ON m.id = p.merchant_id
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.sort_order = 0
        ORDER BY p.created_at DESC, p.id DESC";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductById($id)
{
    $conn = getDbConnection();
    if (!$conn) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    return $product ?: null;
}
