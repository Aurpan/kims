<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    public function list(): void
    {
        Auth::requireLogin();

        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;

        $productModel = new Product();
        $result = $productModel->searchFiltered($search, $category, $page, $perPage);

        $categories = $productModel->getCategories();

        $this->render('products/list', [
            'page_title' => 'Products',
            'flash' => $this->getFlash(),
            'products' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'perPage' => $result['perPage'],
            'categories' => $categories,
            'search' => $search,
            'selected_category' => $category
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();

        $productModel = new Product();
        $categories = $productModel->getCategories();

        $this->render('products/form', [
            'page_title' => 'Create Product',
            'product' => null,
            'categories' => $categories,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products/create');
        }

        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $base_price = $_POST['base_price'] ?? '';
        $sourcing_price = $_POST['sourcing_price'] ?? '';
        $description = $_POST['description'] ?? '';

        $errors = $this->validate(
            compact('name', 'category', 'base_price'),
            [
                'name' => 'required|min:2|max:255',
                'category' => 'required|min:2|max:100',
                'base_price' => 'required|numeric'
            ]
        );

        if (isset($errors['category'])) {
            $errors['category'] = 'You must select a category or create one';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/products/create');
            return;
        }

        $image_url = null;
        if (!empty($_FILES['image']['name'])) {
            $image_url = $this->uploadImage($_FILES['image']);
            if (!$image_url) {
                $_SESSION['errors'] = ['image' => 'Failed to upload image. Ensure it is JPG/PNG/GIF under 5MB.'];
                $_SESSION['old_input'] = $_POST;
                $this->redirect('/products/create');
                return;
            }
        }

        $productModel = new Product();
        $productModel->create([
            'name' => $name,
            'category' => $category,
            'base_price' => (int) $base_price,
            'sourcing_price' => $sourcing_price !== '' ? (int) $sourcing_price : null,
            'description' => $description,
            'image_url' => $image_url
        ]);

        $this->setFlash('success', 'Product created successfully!');
        $this->redirect('/products');
    }

    public function show(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        $product = $productModel->getWithVariants($id);

        if (!$product) {
            $this->abort(404, 'Product not found');
        }

        $this->render('products/show', [
            'page_title' => htmlspecialchars($product['name']),
            'product' => $product
        ]);
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        $product = $productModel->find($id);

        if (!$product) {
            $this->abort(404, 'Product not found');
        }

        $categories = $productModel->getCategories();

        $this->render('products/form', [
            'page_title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function update(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        if (!$productModel->find($id)) {
            $this->abort(404, 'Product not found');
        }

        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $base_price = $_POST['base_price'] ?? '';
        $sourcing_price = $_POST['sourcing_price'] ?? '';
        $description = $_POST['description'] ?? '';

        $errors = $this->validate(
            compact('name', 'category', 'base_price'),
            [
                'name' => 'required|min:2|max:255',
                'category' => 'required|min:2|max:100',
                'base_price' => 'required|numeric'
            ]
        );

        if (isset($errors['category'])) {
            $errors['category'] = 'You must select a category or create one';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/products/edit/$id");
            return;
        }

        $current_product = $productModel->find($id);
        $image_url = $current_product['image_url'];

        if (!empty($_FILES['image']['name'])) {
            $new_image_url = $this->uploadImage($_FILES['image']);
            if ($new_image_url) {
                $image_url = $new_image_url;
            }
        }

        $productModel->update($id, [
            'name' => $name,
            'category' => $category,
            'base_price' => (int) $base_price,
            'sourcing_price' => $sourcing_price !== '' ? (int) $sourcing_price : null,
            'description' => $description,
            'image_url' => $image_url
        ]);

        $this->setFlash('success', 'Product updated successfully!');
        $this->redirect('/products');
    }

    public function delete(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        if (!$productModel->find($id)) {
            $this->abort(404, 'Product not found');
        }

        $productModel->deactivate($id);

        $this->setFlash('success', 'Product deleted successfully!');
        $this->redirect('/products');
    }

    public function variants(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        $product = $productModel->getWithVariants($id);

        if (!$product) {
            $this->abort(404, 'Product not found');
        }

        $variantModel = new ProductVariant();
        $lowStockVariants = $variantModel->getLowStock();
        $lowStockIds = array_column($lowStockVariants, 'id');
        $totalStock = $productModel->getTotalStock($id);

        $this->render('products/variants', [
            'page_title' => 'Manage Variants - ' . htmlspecialchars($product['name']),
            'flash' => $this->getFlash(),
            'product' => $product,
            'lowStockVariantIds' => $lowStockIds,
            'totalStock' => $totalStock,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function storeVariant(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $product_id = (int) ($_GET['id'] ?? 0);

        if ($product_id <= 0) {
            $this->abort(404, 'Product not found');
        }

        $productModel = new Product();
        if (!$productModel->find($product_id)) {
            $this->abort(404, 'Product not found');
        }

        $size = $_POST['size'] ?? '';
        $sku = $_POST['sku'] ?? '';
        $stock = $_POST['stock'] ?? '0';
        $variant_price = $_POST['variant_price'] ?? '';

        $errors = $this->validate(
            compact('size', 'sku', 'stock'),
            [
                'size' => 'required|min:1|max:50',
                'sku' => 'required|min:1|max:100',
                'stock' => 'required|numeric'
            ]
        );

        $variantModel = new ProductVariant();

        if ($variantModel->findBySku($sku)) {
            $errors['sku'] = 'SKU already exists';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/products/$product_id/variants");
            return;
        }

        $variantModel->create([
            'product_id' => $product_id,
            'size' => $size,
            'sku' => $sku,
            'stock' => (int) $stock,
            'reorder_point' => 2,
            'variant_price' => !empty($variant_price) ? $variant_price : null
        ]);

        $this->setFlash('success', 'Variant created successfully!');
        $this->redirect("/products/$product_id/variants");
    }

    public function deleteVariant(): void
    {
        Auth::requireLogin();

        $variant_id = (int) ($_GET['variantId'] ?? 0);

        if ($variant_id <= 0) {
            $this->abort(404, 'Variant not found');
        }

        $variantModel = new ProductVariant();
        $variant = $variantModel->find($variant_id);

        if (!$variant) {
            $this->abort(404, 'Variant not found');
        }

        $product_id = $variant['product_id'];

        $variantModel->delete($variant_id);

        $this->setFlash('success', 'Variant deleted successfully!');
        $this->redirect("/products/$product_id/variants");
    }

    public function updateStock(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $variant_id = (int) ($_GET['variantId'] ?? 0);

        if ($variant_id <= 0) {
            $this->abort(404, 'Variant not found');
        }

        $variantModel = new ProductVariant();
        $variant = $variantModel->find($variant_id);

        if (!$variant) {
            $this->abort(404, 'Variant not found');
        }

        $stock = (int) ($_POST['stock'] ?? 0);
        $product_id = $variant['product_id'];

        $variantModel->setStock($variant_id, $stock);

        $this->setFlash('success', 'Stock updated successfully!');
        $this->redirect("/products/$product_id/variants");
    }

    private function uploadImage(array $file): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $uploadDir = PUBLIC_PATH . '/uploads/products/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid('prod_') . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/uploads/products/' . $filename;
        }

        return null;
    }
}
