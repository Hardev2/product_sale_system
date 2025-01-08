<?php

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Map pages to files and their respective titles
$pages = [
    'home' => ['file' => 'index.php', 'title' => 'Dashboard'],
    'tally' => ['file' => 'tally.php', 'title' => 'Tally Sales'],
    'history' => ['file' => 'history.php', 'title' => 'History'],
    'detail' => ['file' => 'daily_sales_detail.php', 'title' => 'Daily Sales Details'],
    'inventory' => ['file' => 'inventory.php', 'title' => 'Inventory'],
    'creditor' => ['file' => 'creditor.php', 'title' => 'Creditor Management'],
    'creditor_list' => ['file' => 'creditor_list.php', 'title' => 'Creditor List'],
    'creditor_details' => ['file' => 'creditor_details.php', 'title' => 'Creditor Details'],
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