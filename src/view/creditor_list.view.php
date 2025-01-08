<?php
include 'src/config/database.php';

// Handle insert
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);

    $insert_query = "INSERT INTO creditors (name, contact_info) VALUES ('$name', '$contact_info')";
    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Creditor added successfully'); window.location.href='router.php?page=creditor_list';</script>";
    } else {
        echo "<script>alert('Error adding creditor');</script>";
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $creditor_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM creditors WHERE id = '$creditor_id'";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Creditor deleted successfully'); window.location.href='router.php?page=creditor_list';</script>";
    } else {
        echo "<script>alert('Error deleting creditor');</script>";
    }
}


// Fetch all creditors
$creditors = mysqli_query($conn, "SELECT id, name, contact_info FROM creditors ORDER BY name");
?>
<?php include 'public/components/header.php' ?>
<body>
    <div class="container">
    <?php include 'public/components/side-bar.php' ?>
        <div class="hero">
            <div class="content">
            <h1><?php echo isset($title) ? $title : 'Default Title'; ?></h1>
            <form method="POST">
                <div class="form-container">
                    <div class="form-row">
                        <div class="form-col">
                            <label for="name">Creditor Name:</label>
                            <input type="text" name="name" placeholder="Type here.." required><br>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="contact_info">Contact Info:</label>
                            <input type="number" name="contact_info" placeholder="Type here.." required><br>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                             <button type="submit" name="submit"><i class="fa-solid fa-square-plus"></i> Add Creditor</button>
                        </div>
                    </div>
                </div>
            </form>
                <table id="myTable">
                    <thead>
                        <tr>
                            <th>Creditor Name</th>
                            <th>Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($creditor = mysqli_fetch_assoc($creditors)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($creditor['name']); ?></td>
                                <td><?php echo htmlspecialchars($creditor['contact_info']); ?></td>
                                <td>
                                    <div class="action-btn">
                                        <a href="router.php?page=creditor_details&creditor_id=<?php echo $creditor['id']; ?>" class="btn-view"><i class="fa-solid fa-eye"></i>View</a>
                                        <a href="router.php?page=creditor_list&delete_id=<?php echo $creditor['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this creditor?');"><i class="fa-solid fa-trash"></i>Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
