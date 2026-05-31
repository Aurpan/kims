<?php
$mysqli = new mysqli("localhost", "root", "", "inventory_mgmt");
if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); }

echo "=== Product Variants ===\n";
$result = $mysqli->query("SELECT * FROM product_variants");
if ($result->num_rows === 0) {
    echo "No variants found!\n";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Product: {$row['product_id']}, SKU: {$row['sku']}, Size: {$row['size']}, Color: {$row['color']}, Stock: {$row['stock']}\n";
    }
}

echo "\n=== Products ===\n";
$result = $mysqli->query("SELECT id, name FROM products");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Name: {$row['name']}\n";
}
?>
