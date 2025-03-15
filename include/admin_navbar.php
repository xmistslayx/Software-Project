<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: teal;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            padding: 0 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .admin-section {
            display: flex;
            align-items: center;
        }


        .admin-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }


        .admin-profile .name {
            margin-right: 25px;
        }

        .sidebar {
            position: fixed;
            height: calc(100vh - 70px);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .menu-category {
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            padding: 15px 20px 5px;
            letter-spacing: 1px;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
        }

        .menu-item:hover {
            background-color: rgba(98, 0, 234, 0.1);
            color: var(--primary);
        }

        .menu-item.active {
            background-color: rgba(98, 0, 234, 0.15);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }

        .submenu {
            list-style: none;
            padding-left: 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .submenu.active {
            max-height: 200px;
        }

        .submenu-item {
            padding: 10px 20px;
            color: var(--text-dark);
            text-decoration: none;
            display: block;
            font-size: 14px;
            cursor: pointer;
        }

        .submenu-item:hover {
            color: var(--primary);
        }

        .badge {
            background-color: var(--danger);
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 11px;
            margin-left: 10px;
        }

        .quick-actions {
            display: flex;
            gap: 15px;
            margin-right: 20px;
        }

        .quick-action-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 16px;
            cursor: pointer;
            position: relative;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="logo-container">
            <div class="toggle-sidebar" id="toggleSidebar">
            </div>
            <div class="logo">
                <h1>LuckyNest</h1>
            </div>
        </div>
    </nav>

    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li class="menu-category">Main</li>
            <li class="menu-item active">
                <a href="admin/dashboard.php">Dashboard</a>
            </li>

            <li class="menu-category">User Management</li>
            <li class="menu-item has-dropdown" onclick="toggleSubmenu(this)">
                <span>PG Guests</span>
            </li>
            <ul class="submenu">
                <a href="TODO">
                    <li class="submenu-item">All Guests</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Guest Profiles</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Emergency Contacts</li>
                </a>
            </ul>

            <li class="menu-item">
                <a href="TODO">Admin Users</a>
            </li>

            <li class="menu-category">Property Management</li>
            <li class="menu-item">
                <a href="../admin/rooms.php">All Rooms</a>
            </li>
            <li class="menu-item">
                <a href="../admin/room_types.php">Room Types</a>
            </li>
            <li class="menu-category">Operations</li>
            <li class="menu-item">
                <a href="../admin/bookings.php">All Bookings</a>
            </li>

            <li class="menu-item has-dropdown" onclick="toggleSubmenu(this)">
                <span>Payments</span>
            </li>
            <ul class="submenu">
                <a href="TODO">
                    <li class="submenu-item">Invoices</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Security Deposits</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Payment History</li>
                </a>
            </ul>

            <li class="menu-item has-dropdown" onclick="toggleSubmenu(this)">
                <span>Food Services</span>
            </li>
            <ul class="submenu">
                <a href="TODO">
                    <li class="submenu-item">Food Menu</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Meal Plans</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Special Requests</li>
                </a>
            </ul>

            <li class="menu-item has-dropdown" onclick="toggleSubmenu(this)">
                <span>Other Services</span>
            </li>
            <ul class="submenu">
                <a href="TODO">
                    <li class="submenu-item">Laundry</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Housekeeping</li>
                </a>
            </ul>

            <li class="menu-item">
                <a href="TODO">Log Visitors</a>
            </li>

            <li class="menu-category">Communication</li>
            <li class="menu-item">
                <a href="TODO">Notifications</a>
            </li>
            <li class="menu-item">
                <a href="TODO">Announcements</a>
            </li>

            <li class="menu-category">Reports</li>
            <li class="menu-item has-dropdown" onclick="toggleSubmenu(this)">
                <span>Reports & Analytics</span>
            </li>
            <ul class="submenu">
                <a href="TODO">
                    <li class="submenu-item">Revenue & Expense Reports</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Occupancy Reports</li>
                </a>
                <a href="TODO">
                    <li class="submenu-item">Food Consumption</li>
                </a>
            </ul>

            <li class="menu-category">Settings</li>
            <li class="menu-item">
                <a href="TODO">System Settings</a>
            </li>
            <li class="menu-item">
                <a href="TODO">Account Settings</a>
            </li>
            <li class="menu-item">
                <a href="logout.php">Logout</a>
            </li>
        </ul>
    </aside>

    <script>
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('sidebar-hidden');
            mainContent.classList.toggle('expanded');
        });

        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            element.classList.toggle('open');
            submenu.classList.toggle('active');

            // this closes other open submenus
            const allSubmenus = document.querySelectorAll('.submenu.active');
            const allDropdowns = document.querySelectorAll('.has-dropdown.open');

            allSubmenus.forEach(menu => {
                if (menu !== submenu) {
                    menu.classList.remove('active');
                }
            });

            allDropdowns.forEach(dropdown => {
                if (dropdown !== element) {
                    dropdown.classList.remove('open');
                }
            });
        }

        // close other submenus when you click outside of them
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.has-dropdown') && !event.target.closest('.submenu')) {
                const allSubmenus = document.querySelectorAll('.submenu.active');
                const allDropdowns = document.querySelectorAll('.has-dropdown.open');

                allSubmenus.forEach(menu => {
                    menu.classList.remove('active');
                });

                allDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('open');
                });
            }
        });

        const adminProfile = document.querySelector('.admin-profile');
        adminProfile.addEventListener('click', function () {
            alert('TODO maybe add something here');
        });

        const notificationBtn = document.querySelector('.quick-action-btn:nth-child(1)');
        notificationBtn.addEventListener('click', function () {
            alert('Notifications would appear here');
        });

        // Message actions (placeholder)
        const messageBtn = document.querySelector('.quick-action-btn:nth-child(2)');
        messageBtn.addEventListener('click', function () {
            alert('Messages would appear here');
        });

        // Settings actions (placeholder)
        const settingsBtn = document.querySelector('.quick-action-btn:nth-child(3)');
        settingsBtn.addEventListener('click', function () {
            alert('Quick settings would appear here');
        });
    </script>
</body>

</html>