<?php
include 'src/config/database.php'; // Include your database connection file

// Variables to store form data
$id = '';
$name = '';
$price = '';
$quantity = '';
$is_editing = false;

// Handle form submission (Insert or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update product
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $update_query = "UPDATE products SET name='$name', price='$price', quantity='$quantity' WHERE id='$id'";
        if (mysqli_query($conn, $update_query)) {
            header("Location: router.php?page=inventory");
            exit;
        } else {
            $error_message = "Error updating product: " . mysqli_error($conn);
        }
    } else {
        // Insert new product
        if (!empty($name) && is_numeric($price) && is_numeric($quantity)) {
            $insert_query = "INSERT INTO products (name, price, quantity) VALUES ('$name', '$price', '$quantity')";
            if (mysqli_query($conn, $insert_query)) {
                header("Location: router.php?page=inventory");
                exit;
            } else {
                $error_message = "Error adding product: " . mysqli_error($conn);
            }
        } else {
            $error_message = "All fields are required, and price/quantity must be numeric!";
        }
    }
}

// Handle edit request
if (isset($_GET['edit_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $product = mysqli_fetch_assoc($product_query);

    if ($product) {
        $is_editing = true;
        $name = $product['name'];
        $price = $product['price'];
        $quantity = $product['quantity'];
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM products WHERE id='$delete_id'";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: router.php?page=inventory&deleted=1");
        exit;
    } else {
        $error_message = "Error deleting product: " . mysqli_error($conn);
    }
}

// Fetch product list
$product_list = mysqli_query($conn, "SELECT * FROM products ORDER BY name ASC");
?>

<?php include 'public/components/header.php' ?>
<body>
    <div class="container">
        <?php include 'public/components/side-bar.php'; ?>
        <div class="hero">
            <div class="content">
                <h1><?php echo $is_editing ? "Edit Product" : "Add Product"; ?></h1>
                
                <?php if (isset($success_message)): ?>
                    <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
                <?php elseif (isset($error_message)): ?>
                    <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>

                <form action="router.php?page=inventory" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    
                   <div class="form-container">
                    <div class="form-row">
                            <div class="form-col">
                                <label for="name">Product Name:</label>
                                <input type="text" id="name"  placeholder="Type here.." name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="price">Price (₱):</label>
                                <input type="number" step="0.01" id="price" placeholder="Type here.." name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" placeholder="Type here.." name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                                <div class="form-col">
                                        <button type="submit"><i class="fa-solid fa-square-plus"></i><?php echo $is_editing ? "Update Product" : "Add Product"; ?></button>
                                    <?php if ($is_editing): ?>
                                        <a href="router.php?page=inventory" class="btn-cancel"><i class="fa-solid fa-xmark"></i>Cancel</a>
                                    <?php endif; ?>
                                </div>
                        </div>
                   </div>
                </form>

               
                <?php if (mysqli_num_rows($product_list) > 0): ?>
                    <table  id="myTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($product_list)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td>
                                       <div class="action-btn">
                                            <a href="router.php?page=inventory&edit_id=<?php echo $row['id']; ?>" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i>Edit</a>
                                            <a href="router.php?page=inventory&delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa-solid fa-trash"></i>Delete</a>
                                       </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

             <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
             <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
                <script>
                $(document).ready( function () {
                    $('#myTable').DataTable();
                });
                </script>
</body>
</html>
