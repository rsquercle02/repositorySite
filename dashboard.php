<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard1.css">
</head>
<body>

<div class="container" id="content">
    <h1>BFMSI Dashboard</h1>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card" style="background-color: #ff6666;">
            <h2>Total Market</h2>
            <span>50</span>
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-card" style="background-color: #66b3ff;">
            <h2>Total Approved</h2>
            <span>45</span>
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-card" style="background-color: #ffcc66;">
            <h2>Total Failed</h2> 
            <span>5</span>
            <i class="fas fa-ban"></i>
        </div>
        <div class="stat-card" style="background-color: #ffff66;">
            <h2>Users</h2>
            <span>50</span>
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-container">
        <div class="chart-card">
            <h3>Admin Monitoring</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Admin Inspection</h3>
            <canvas id="doughnutChart"></canvas>
        </div>
    </div>

    <!-- Task Table -->
    <div class="task-table-container">
        <div class="filter">
            <label for="filterStatus">Select:</label>
            <select id="filterStatus">
                <option value="select">Select an Option</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Category</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>Market 1</td>
                <td>Pending</td>
                <td>Meat</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Market 2</td>
                <td>Approved</td>
                <td>Meat</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript -->
<script src="dashboard1.js"></script> 
</body>
</html>
