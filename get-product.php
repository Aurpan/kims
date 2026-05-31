<?php
$mysqli = new mysqli("localhost", "root", "", "inventory_mgmt");
if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); }
$result = $mysqli->query("SELECT id FROM products LIMIT 1");
$row = $result->fetch_assoc();
echo $row["id"];
?>
