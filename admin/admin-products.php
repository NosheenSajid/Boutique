<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

requireAdminLogin();

/* Resolve an Unsplash photo page to its direct image URL */
function resolveProductImageUrl(string $imageUrl): string
{
    $parts = parse_url($imageUrl);

    if (
        !$parts ||
        !isset($parts['scheme'], $parts['host']) ||
        !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
    ) {
        return $imageUrl;
    }

    if (strtolower($parts['host']) !== 'unsplash.com'
        && strtolower($parts['host']) !== 'www.unsplash.com') {
        return $imageUrl;
    }

    $ch = curl_init($imageUrl);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_USERAGENT => 'Clothing Store product image resolver',
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    if (!is_string($html) || $html === '') {
        return $imageUrl;
    }

    preg_match_all('/<meta\b[^>]*>/i', $html, $matches);

    foreach ($matches[0] ?? [] as $tag) {
        if (
            preg_match(
                '/\b(?:property|name)=["\']([^"\']+)["\']/i',
                $tag,
                $propertyMatch
            ) !== 1
        ) {
            continue;
        }

        if (
            preg_match(
                '/\bcontent=["\']([^"\']+)["\']/i',
                $tag,
                $contentMatch
            ) !== 1
        ) {
            continue;
        }

        $property = strtolower(trim($propertyMatch[1]));
        $content = html_entity_decode(
            $contentMatch[1],
            ENT_QUOTES,
            'UTF-8'
        );

        if (
            in_array($property, ['og:image', 'twitter:image'], true) &&
            $content !== ''
        ) {
            return $content;
        }
    }

    if (
        preg_match(
            '/https?:\/\/images\.unsplash\.com\/[^"\'<> ]+/i',
            $html,
            $imageMatch
        ) === 1
    ) {
        return html_entity_decode(
            $imageMatch[0],
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return $imageUrl;
}

/* Upload and validate a product image */
function storeProductUpload(array $upload): ?string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (
        ($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK ||
        !is_uploaded_file($upload['tmp_name'] ?? '')
    ) {
        throw new RuntimeException(
            'The image upload could not be completed.'
        );
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))
        ->file($upload['tmp_name']);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException(
            'Upload a JPG, PNG, WEBP, or GIF image.'
        );
    }

    if ((int) ($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException(
            'Product images must be 5 MB or smaller.'
        );
    }

    $filename = bin2hex(random_bytes(16))
        . '.'
        . $allowedMimeTypes[$mimeType];

    $directory = __DIR__ . '/../uploads/products';

    if (
        !is_dir($directory) &&
        !mkdir($directory, 0755, true) &&
        !is_dir($directory)
    ) {
        throw new RuntimeException(
            'The product upload directory is not available.'
        );
    }

    if (
        !move_uploaded_file(
            $upload['tmp_name'],
            $directory . '/' . $filename
        )
    ) {
        throw new RuntimeException(
            'The product image could not be saved.'
        );
    }

    return BASE_URL . 'uploads/products/' . $filename;
}

/* Find the product being edited */
$editingProduct = null;
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($productId) {
    $stmt = $pdo->prepare(
        'SELECT id, category_id, name, slug, description, price,
                sale_price, stock, sku, main_image, status
         FROM products
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute(['id' => $productId]);
    $editingProduct = $stmt->fetch() ?: null;
}

/* Process form submissions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($token)) {
        flash('error', 'Security token expired. Please try again.');
        redirect('admin/admin-products.php');
    }

    /* Delete product */
    if ($action === 'delete') {
        $deleteId = (int) ($_POST['product_id'] ?? 0);

        if ($deleteId > 0) {
            $stmt = $pdo->prepare(
                'DELETE FROM products WHERE id = :id'
            );

            $stmt->execute(['id' => $deleteId]);

            flash('success', 'Product removed from the catalog.');
        }

        redirect('admin/admin-products.php');
    }

    /* Add or update product */
    if ($action === 'save') {
        $saveId = (int) ($_POST['product_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));

        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $name);

        $description = trim(
            (string) ($_POST['description'] ?? '')
        );

        $price = (float) ($_POST['price'] ?? 0);

        $salePriceInput = trim(
            (string) ($_POST['sale_price'] ?? '')
        );

        $salePrice = $salePriceInput === ''
            ? null
            : (float) $salePriceInput;

        $stock = filter_var(
            $_POST['stock'] ?? null,
            FILTER_VALIDATE_INT
        );

        $sku = trim((string) ($_POST['sku'] ?? ''));
        $image = trim((string) ($_POST['main_image'] ?? ''));
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        $errors = [];

        try {
            $uploadedImage = storeProductUpload(
                $_FILES['product_image'] ?? []
            );

            if ($uploadedImage !== null) {
                $image = $uploadedImage;
            } elseif ($image !== '') {
                $image = resolveProductImageUrl($image);
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($name === '') {
            $errors[] = 'Product name is required.';
        }

        if (!is_numeric($_POST['price'] ?? null) || $price < 0) {
            $errors[] = 'Enter a valid, non-negative price.';
        }

        if (
            $salePriceInput !== '' &&
            (
                !is_numeric($salePriceInput) ||
                $salePrice === null ||
                $salePrice < 0 ||
                $salePrice > $price
            )
        ) {
            $errors[] =
                'Sale price must be between zero and the regular price.';
        }

        if ($stock === false || $stock === null || $stock < 0) {
            $errors[] = 'Stock must be a non-negative whole number.';
            $stock = 0;
        }

        /* Check for duplicate slug */
        $slugStmt = $pdo->prepare(
            'SELECT id FROM products
             WHERE slug = :slug AND id <> :id
             LIMIT 1'
        );

        $slugStmt->execute([
            'slug' => $slug,
            'id' => $saveId,
        ]);

        if ($slugStmt->fetch()) {
            $errors[] = 'That product slug is already in use.';
        }

        /* Check for duplicate SKU */
        if ($sku !== '') {
            $skuStmt = $pdo->prepare(
                'SELECT id FROM products
                 WHERE sku = :sku AND id <> :id
                 LIMIT 1'
            );

            $skuStmt->execute([
                'sku' => $sku,
                'id' => $saveId,
            ]);

            if ($skuStmt->fetch()) {
                $errors[] = 'That SKU is already in use.';
            }
        }

        if ($errors) {
            flash('error', implode(' ', $errors));

            redirect(
                $saveId > 0
                    ? 'admin/admin-products.php?id=' . $saveId
                    : 'admin/admin-products.php'
            );
        }

        $data = [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => $stock,
            'sku' => $sku !== '' ? $sku : null,
            'main_image' => $image !== '' ? $image : null,
            'status' => $status,
        ];

        if ($saveId > 0) {
            $data['id'] = $saveId;

            $stmt = $pdo->prepare(
                'UPDATE products SET
                    category_id = :category_id,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    price = :price,
                    sale_price = :sale_price,
                    stock = :stock,
                    sku = :sku,
                    main_image = :main_image,
                    status = :status,
                    updated_at = NOW()
                 WHERE id = :id'
            );

            $stmt->execute($data);

            flash('success', 'Product updated successfully.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO products (
                    category_id, name, slug, description, price,
                    sale_price, stock, sku, main_image, status
                 ) VALUES (
                    :category_id, :name, :slug, :description, :price,
                    :sale_price, :stock, :sku, :main_image, :status
                 )'
            );

            $stmt->execute($data);

            flash('success', 'Product added to the catalog.');
        }

        redirect('admin/admin-products.php');
    }
}

/* Load categories and catalog */
$categories = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name ASC'
)->fetchAll();

$products = $pdo->query(
    'SELECT p.id, p.name, p.price, p.sale_price, p.stock,
            p.sku, p.main_image, p.status,
            c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC'
)->fetchAll();

$error = flash('error');
$success = flash('success');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-products.css">

<section class="products-page">

    <!-- Page header -->
    <div class="products-header">
        <div>
            <h1>Products</h1>

            <p class="products-subtitle">
                Add, edit, publish, and manage your product catalog.
            </p>
        </div>

        
    </div>

    <!-- Notifications -->
    <?php if ($error): ?>
        <div class="products-alert products-alert-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="products-alert products-alert-success" role="status">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Add product button -->
    <?php if (!$editingProduct): ?>
        <div class="products-toolbar">
            <button
                type="button"
                id="show-product-form"
                class="products-btn products-btn-dark"
            >
                + Add Product
            </button>
        </div>
    <?php endif; ?>

    <!-- Product form modal -->
    <div
        id="product-form-modal"
        class="product-modal <?= $editingProduct ? '' : 'hidden' ?>"
        role="dialog"
        aria-modal="true"
        aria-labelledby="product-modal-title"
    >
        <div class="product-modal-panel">

            <div class="product-modal-header">
                <h2 id="product-modal-title">
                    <?= $editingProduct ? 'Edit Product' : 'Add Product' ?>
                </h2>

                <button
                    type="button"
                    id="close-product-form"
                    class="product-modal-close"
                    aria-label="Close product form"
                >
                    &times;
                </button>
            </div>

            <form
                method="post"
                enctype="multipart/form-data"
                class="product-form"
                id="product-form"
            >
                <?= csrfField() ?>

                <input type="hidden" name="action" value="save">

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) ($editingProduct['id'] ?? 0) ?>"
                >

                <div class="product-form-group">
                    <label for="product-name">Product Name *</label>

                    <input
                        id="product-name"
                        type="text"
                        name="name"
                        required
                        maxlength="255"
                        value="<?= htmlspecialchars(
                            (string) ($editingProduct['name'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Enter product name"
                    >
                </div>

                <div class="product-form-group">
                    <label for="product-slug">Product Slug</label>

                    <input
                        id="product-slug"
                        type="text"
                        name="slug"
                        value="<?= htmlspecialchars(
                            (string) ($editingProduct['slug'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="product-name"
                    >

                    <p class="product-form-hint">
                        Leave blank to generate it from the product name.
                    </p>
                </div>

                <div class="product-form-grid">

                    <div class="product-form-group">
                        <label for="product-price">Price *</label>

                        <input
                            id="product-price"
                            type="number"
                            name="price"
                            min="0"
                            step="0.01"
                            required
                            value="<?= htmlspecialchars(
                                (string) ($editingProduct['price'] ?? '0'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="0.00"
                        >
                    </div>

                    <div class="product-form-group">
                        <label for="product-sale-price">Sale Price</label>

                        <input
                            id="product-sale-price"
                            type="number"
                            name="sale_price"
                            min="0"
                            step="0.01"
                            value="<?= htmlspecialchars(
                                (string) ($editingProduct['sale_price'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Optional"
                        >
                    </div>

                    <div class="product-form-group">
                        <label for="product-stock">Stock *</label>

                        <input
                            id="product-stock"
                            type="number"
                            name="stock"
                            min="0"
                            step="1"
                            required
                            value="<?= htmlspecialchars(
                                (string) ($editingProduct['stock'] ?? '0'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                    </div>

                    <div class="product-form-group">
                        <label for="product-sku">SKU</label>

                        <input
                            id="product-sku"
                            type="text"
                            name="sku"
                            value="<?= htmlspecialchars(
                                (string) ($editingProduct['sku'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Product SKU"
                        >
                    </div>

                </div>

                <div class="product-form-group">
                    <label for="product-category">Category</label>

                    <select id="product-category" name="category_id">
                        <option value="0">Uncategorised</option>

                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int) $category['id'] ?>"
                                <?= (int) ($editingProduct['category_id'] ?? 0) === (int) $category['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars(
                                    (string) $category['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Product image -->
                <div class="product-form-group">

                    <label for="main-image-input">
                        Image URL
                    </label>

                    <input
                        id="main-image-input"
                        type="url"
                        name="main_image"
                        value="<?= htmlspecialchars(
                            (string) ($editingProduct['main_image'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="https://example.com/product.jpg"
                    >

                    <p class="product-form-hint">
                        Enter a direct image URL, or upload an image below.
                        Uploaded images take priority over this URL.
                    </p>

                    <label
                        for="product-image-input"
                        class="product-field-label"
                        style="margin-top: 18px;"
                    >
                        Upload Product Image
                    </label>

                    <input
                        id="product-image-input"
                        class="product-file-input"
                        type="file"
                        name="product_image"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                    >

                    <p class="product-form-hint">
                        JPG, PNG, WEBP or GIF. Maximum size: 5 MB.
                    </p>

                    <div
                        id="main-image-preview"
                        class="product-image-preview"
                    >
                        <?php if (!empty($editingProduct['main_image'])): ?>
                            <img
                                src="<?= htmlspecialchars(
                                    (string) $editingProduct['main_image'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="Product preview"
                            >
                        <?php else: ?>
                            Image Preview
                        <?php endif; ?>
                    </div>

                </div>

                <div class="product-form-group">
                    <label for="product-description">
                        Description
                    </label>

                    <textarea
                        id="product-description"
                        name="description"
                        rows="4"
                        placeholder="Describe your product..."
                    ><?= htmlspecialchars(
                        (string) ($editingProduct['description'] ?? ''),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>
                </div>

                <label class="product-publish-option">
                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        <?= (int) ($editingProduct['status'] ?? 1) === 1
                            ? 'checked'
                            : '' ?>
                    >

                    Publish on storefront
                </label>

                <div class="product-form-actions">
                    <button
                        type="submit"
                        class="products-btn products-btn-dark"
                    >
                        <?= $editingProduct
                            ? 'Update Product'
                            : 'Add Product' ?>
                    </button>

                    <button
                        type="button"
                        id="cancel-product-form"
                        class="products-btn"
                    >
                        Cancel
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Product catalog -->
    <div class="products-panel">

        <h2>
            Product Catalog
            <span class="products-count">
                (<?= number_format(count($products)) ?> products)
            </span>
        </h2>

        <div class="products-table-wrapper">

            <table class="products-table">

                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($products as $product): ?>

                        <tr>
                            <td>
                                <?php if (!empty($product['main_image'])): ?>
                                    <img
                                        src="<?= htmlspecialchars(
                                            (string) $product['main_image'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            (string) $product['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        class="product-thumb"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="product-no-image">
                                        No Image
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="product-name">
                                    <?= htmlspecialchars(
                                        (string) $product['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>

                                <div class="product-category">
                                    <?= htmlspecialchars(
                                        (string) (
                                            $product['category_name']
                                            ?? 'Uncategorised'
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>
                            </td>

                            <td class="product-price">
                                <?= formatMoney(
                                    $product['sale_price'] !== null
                                        ? $product['sale_price']
                                        : $product['price']
                                ) ?>
                            </td>

                            <td class="product-stock">
                                <?= number_format((int) $product['stock']) ?>
                            </td>

                            <td>
                                <?php if ((int) $product['status'] === 1): ?>
                                    <span class="product-status product-status-published">
                                        Published
                                    </span>
                                <?php else: ?>
                                    <span class="product-status product-status-hidden">
                                        Hidden
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="product-actions">

                                    <a
                                        href="<?= htmlspecialchars(
                                            BASE_URL,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>admin/admin-products.php?id=<?= (int) $product['id'] ?>"
                                        class="product-action-link"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        class="delete-product-form"
                                    >
                                        <?= csrfField() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $product['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="product-action-link product-action-delete"
                                        >
                                            Remove
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="6" class="products-empty">
                                No products yet. Click "Add Product" to
                                create your first product.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>
<script src="<?= BASE_URL ?>assets/javascript/admin-products.js"></script>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>