<?php
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $id ? getProductById($id) : null;
$categories = getAllCategories();
$brands = getAllBrands();
$merchants = getAllMerchants();

$default = [
    'name' => '',
    'slug' => '',
    'sku' => '',
    'category_id' => '',
    'brand_id' => '',
    'merchant_id' => '',
    'price' => '',
    'sale_price' => '',
    'cost_price' => '',
    'weight' => '',
    'short_description' => '',
    'description' => '',
    'image_url' => '',
    'featured' => 0,
    'status' => 1,
];

if ($product) {
    $default['name'] = $product['name'];
    $default['slug'] = $product['slug'];
    $default['sku'] = $product['sku'];
    $default['category_id'] = $product['category_id'];
    $default['brand_id'] = $product['brand_id'];
    $default['merchant_id'] = $product['merchant_id'];
    $default['price'] = $product['price'];
    $default['sale_price'] = $product['sale_price'];
    $default['cost_price'] = $product['cost_price'];
    $default['weight'] = $product['weight'];
    $default['short_description'] = $product['short_description'];
    $default['description'] = $product['description'];
    $default['featured'] = (int) $product['featured'];
    $default['status'] = (int) $product['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit Product' : 'Add Product' ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { background: #f6f8fb; }
        .form-wrap { max-width: 980px; margin: 40px auto; }
    </style>
</head>
<body>
    <div class="form-wrap">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= $id ? 'Edit Product' : 'Add Product' ?></h2>
                <small class="text-muted">Uses the existing products and product_images schema</small>
            </div>
            <a href="products.php" class="btn btn-outline-secondary">Back to list</a>
        </div>

        <form action="actions/product.php" method="post">
            <input type="hidden" name="action" value="save">
            <?php if ($id): ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($default['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($default['slug']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($default['sku']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image URL</label>
                            <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($default['image_url']) ?>" placeholder="img/product/product-1.jpg">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (string) $default['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select" required>
                                <option value="">Select brand</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= (int) $brand['id'] ?>" <?= (string) $default['brand_id'] === (string) $brand['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brand['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Merchant</label>
                            <select name="merchant_id" class="form-select" required>
                                <option value="">Select merchant</option>
                                <?php foreach ($merchants as $merchant): ?>
                                    <option value="<?= (int) $merchant['id'] ?>" <?= (string) $default['merchant_id'] === (string) $merchant['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($merchant['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Price</label>
                            <input type="number" min="0" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars((string) $default['price']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" min="0" step="0.01" name="sale_price" class="form-control" value="<?= htmlspecialchars((string) $default['sale_price']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cost Price</label>
                            <input type="number" min="0" step="0.01" name="cost_price" class="form-control" value="<?= htmlspecialchars((string) $default['cost_price']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight</label>
                            <input type="number" min="0" step="0.01" name="weight" class="form-control" value="<?= htmlspecialchars((string) $default['weight']) ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="3"><?= htmlspecialchars($default['short_description']) ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($default['description']) ?></textarea>
                        </div>

                        <div class="col-md-12 d-flex gap-3 align-items-center">
                            <label class="form-check-label">
                                <input type="checkbox" name="featured" value="1" <?= !empty($default['featured']) ? 'checked' : '' ?>> Featured
                            </label>
                            <label class="form-check-label">
                                <input type="checkbox" name="status" value="1" <?= !empty($default['status']) ? 'checked' : '' ?>> Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary"><?= $id ? 'Update Product' : 'Save Product' ?></button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
