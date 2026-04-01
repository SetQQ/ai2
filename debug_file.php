<?php
// ทดสอบว่า PHP อ่าน teachers.php จริงๆ หรือเปล่า
$file = 'c:/xampp/htdocs/ai2/teachers.php';
$lines = file($file);
echo "<h2>Line 48 of teachers.php:</h2>";
echo "<pre>" . htmlspecialchars($lines[47]) . "</pre>";
echo "<h2>File last modified:</h2>";
echo date('Y-m-d H:i:s', filemtime($file));
echo "<h2>Current PHP time:</h2>";
echo date('Y-m-d H:i:s');
?>
