<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" 
                    alt="Sales & Marketing Funnel System" 
                    class="sidebar-logo">
            </div>

            <button id="sidebarToggle" class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        
        <div class="sidebar-menu">
            <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <a href="#"><i class="fas fa-building"></i> <span>Tenants</span></a>
            <a href="#"><i class="fas fa-users"></i> <span>Users & Roles</span></a>
            <a href="#"><i class="fas fa-filter"></i> <span>Funnels Overview</span></a>
            <a href="#"><i class="fas fa-clipboard-list"></i> <span>Automation Logs</span></a>
            <a href="#"><i class="fas fa-file-invoice-dollar"></i> <span>Billing & Subscriptions</span></a>
            <a href="#"><i class="fas fa-chart-line"></i> <span>Analytics & Reports</span></a>
        </div>

        <div class="account-info-wrapper">
            <div class="account-info">
                <div class="account-details">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>

                <div class="account-menu">
                    <button class="dots-btn" onclick="toggleAccountMenu(event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>

                    <div id="accountDropdown" class="account-dropdown">
                        <a href="#" class="dropdown-link">Manage Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-btn">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <h1>Welcome, Super Admin</h1>
            <div class="header-right">
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-cards">
            <div class="card">
                <h3>Total Tenants</h3>
                <p>12</p>
            </div>
            <div class="card">
                <h3>Active Subscriptions (MRR)</h3>
                <p>$25,600</p>
            </div>
            <div class="card">
                <h3>Total Users</h3>
                <p>180</p>
            </div>
            <div class="card">
                <h3>Churn Rate</h3>
                <p>5%</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts">
            <div class="chart">
                <h3>Monthly Revenue</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="chart">
                <h3>Active Tenants by Plan</h3>
                <!-- Still a placeholder for now as requested only one static chart, but adding canvas for consistency if needed later -->
                <canvas id="tenantsChart"></canvas>
            </div>
        </div>

        <!-- Tables -->
        <div class="actions">
            <button><i class="fas fa-plus"></i> Add New Tenant</button>
            <button><i class="fas fa-download"></i> Export Reports</button>
        </div>

        <h3>Recent Activity</h3>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>2026-02-14 09:00</td><td>Admin</td><td>Created Tenant A</td></tr>
                <tr><td>2026-02-14 10:15</td><td>Admin</td><td>Added User X</td></tr>
                <tr><td>2026-02-14 11:30</td><td>Admin</td><td>Updated Funnel</td></tr>
            </tbody>
        </table>

        <h3>Platform Metrics</h3>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Emails Sent</td><td>2,560</td></tr>
                <tr><td>SMS Sent</td><td>1,200</td></tr>
                <tr><td>Funnels Created</td><td>45</td></tr>
                <tr><td>API Usage</td><td>3,400</td></tr>
            </tbody>
        </table>
        
    </div>

    <script>

        function toggleAccountMenu(event) {
            event.stopPropagation(); // Prevent window click from firing
            const dropdown = document.getElementById("accountDropdown");

            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
            }
        }

        // Close dropdown when clicking anywhere outside
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("accountDropdown");
            const menu = document.querySelector(".account-menu");

            if (!menu.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Static Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [12000, 19000, 3000, 5000, 2000, 30000],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Active Tenants by Plan Chart
        const ctxTenants = document.getElementById('tenantsChart').getContext('2d');
        const tenantsChart = new Chart(ctxTenants, {
            type: 'bar',
            data: {
                labels: ['Basic', 'Pro', 'Enterprise'],
                datasets: [{
                    label: 'Active Tenants',
                    data: [8, 15, 4],
                    backgroundColor: [
                        '#3B82F6',
                        '#2563EB',
                        '#1E40AF'
                    ],
                    borderColor: [
                        '#3B82F6',
                        '#2563EB',
                        '#1E40AF'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
