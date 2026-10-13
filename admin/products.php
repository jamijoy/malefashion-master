<?php
require_once __DIR__ . '/../config/database.php';

$conn = getDbConnection();
$products = getAllProductsForAdmin();
$message = $_GET['message'] ?? '';
$status = $_GET['status'] ?? 'success';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { background: #f5f7fb; }
        .page-wrap { max-width: 1240px; margin: 40px auto; }
        .table td, .table th { vertical-align: middle; }
        .product-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Products</h2>
                <small class="text-muted">Manage product catalog</small>
            </div>
            <a href="product-form.php" class="btn btn-primary">Add Product</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($status === 'error' ? 'danger' : 'success') ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No products found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= (int) $product['id'] ?></td>
                                        <td>
                                            <img src="../<?= htmlspecialchars($product['product_image']) ?>" alt="" class="product-thumb" onerror="this.src='../img/product/product-1.jpg'">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                                            <?php if (!empty($product['featured'])): ?>
                                                <span class="badge bg-warning text-dark ms-1">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['sku']) ?></td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($product['brand_name'] ?? '—') ?></td>
                                        <td><?= formatMoney($product['sale_price'] ?: $product['price']) ?></td>
                                        <td>
                                            <?php if (!empty($product['status'])): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="product-form.php?id=<?= (int) $product['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="actions/product.php" method="post" onsubmit="return confirm('Delete this product?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
