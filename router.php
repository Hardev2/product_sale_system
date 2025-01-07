<?php

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Map pages to files and their respective titles
$pages = [
    'home' => ['file' => 'index.php', 'title' => 'Dashboard'],
    'tally' => ['file' => 'tally.php', 'title' => 'Tally Sales'],
    'history' => ['file' => 'history.php', 'title' => 'History'],
    'detail' => ['file' => 'daily_sales_detail.php', 'title' => 'Daily Sales Details'],
    'inventory' => ['file' => 'inventory.php', 'title' => 'Inventory'],
    'db' => ['file' => 'src/config/database.php'],
   



    // Add other pages here
];
 
// Include the requested file or show a 404
if (array_key_exists($page, $pages)) {
    $title = $pages[$page]['title']; // Get the title for the current page
    include $pages[$page]['file'];
} else {
    $title = '404 Not Found';
    echo "404 Not Found";
}


?>