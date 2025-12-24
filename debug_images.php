<?php
include 'includes/db_connect.php';

$result = $conn->query("SELECT product_id, product_name, image_path FROM products");

echo "<h1>Image Path Debugger</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Stored Path</th><th>Is URL?</th><th>File Exists? (Relative Check)</th></tr>";

while ($row = $result->fetch_assoc()) {
    $path = $row['image_path'];
    $isUrl = filter_var($path, FILTER_VALIDATE_URL) ? 'YES' : 'NO';

    // Check local file existence assuming relative to root (since this script is in root)
    // Note: Dashboards are in subfolders, so they use "../$path"
    $fileExists = "N/A";
    if ($isUrl === 'NO') {
        if (file_exists($path)) {
            $fileExists = "YES";
        } else {
            $fileExists = "NO (Checked: $path)";
        }
    }

    echo "<tr>";
    echo "<td>" . $row['product_id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "<td>" . $isUrl . "</td>";
    echo "<td>" . $fileExists . "</td>";
    echo "</tr>";
}
echo "</table>";
?>