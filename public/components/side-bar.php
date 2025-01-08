<div class="side-bar">
    <div class="side_header">
        <img src="public/image/sale_logo.png" alt="">
        <h1>Salesync</h1>
    </div>
    <ul class="nav_menu" id="navMenu">
        <li class="nav_links">
            <a href="router.php?page=home"><i class="fa-solid fa-house"></i>Dashboard</a>
        </li>
        <li class="nav_links">
            <a href="router.php?page=tally"><i class="fa-solid fa-list-ul"></i>Tally</a>
        </li>
        <li class="nav_links">
            <a href="router.php?page=history"><i class="fa-solid fa-clock-rotate-left"></i>History</a>
        </li>
        <li class="nav_links">
            <a href="router.php?page=inventory"><i class="fa-solid fa-warehouse"></i>Inventory</a>
        </li>
        <li class="nav_links dropdown">
            <a href="#" class="dropdown-toggle"><i class="fa-solid fa-box"></i>Creditors</a>
            <ul class="dropdown-menu">
                <li><a href="router.php?page=creditor"><i class="fa-solid fa-square-plus"></i> Add Credit</a></li>
                <li><a href="router.php?page=creditor_list"><i class="fa-solid fa-list-ul"></i> Creditor List</a></li>
            </ul>
        </li>
    </ul>
</div>

<script>
    // Toggle the dropdown menu
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('open');

            // Close other open dropdowns
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                if (dropdown !== parent) {
                    dropdown.classList.remove('open');
                }
            });
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                dropdown.classList.remove('open');
            });
        }
    });

    // Highlight the active link for dropdown items
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('#navMenu a');

    navLinks.forEach(link => {
        if (link.href === currentUrl) {
            link.classList.add('active');
        }
    });
</script>>


