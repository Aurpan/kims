<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Revenue Report</h1>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/reports/revenue" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Period Total</p>
                    <h4 class="mb-0">৳<?= number_format($periodTotal, 2); ?></h4>
                    <small class="text-muted">
                        <?= $periodTotal > $previousTotal ? '↑' : '↓'; ?>
                        vs previous period
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Daily Average</p>
                    <h4 class="mb-0">৳<?= number_format($avgDaily, 2); ?></h4>
                    <small class="text-muted"><?= count($dailyData); ?> days</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Previous Period</p>
                    <h4 class="mb-0">৳<?= number_format($previousTotal, 2); ?></h4>
                    <small class="text-muted d-block">
                        <?php
                        $change = $previousTotal > 0 ? (($periodTotal - $previousTotal) / $previousTotal) * 100 : 0;
                        echo sprintf('%+.1f%%', $change);
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="card border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Revenue Trend</h5>
        </div>
        <div class="card-body" style="position: relative; height: 400px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Daily Breakdown</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalCheck = 0;
                    foreach (array_reverse($dailyData) as $day):
                        $totalCheck += $day['revenue'];
                    ?>
                        <tr>
                            <td><?= date('M d, Y (l)', strtotime($day['date'])); ?></td>
                            <td class="text-end fw-bold">৳<?= number_format($day['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= $dateLabels; ?>,
        datasets: [{
            label: 'Daily Revenue',
            data: <?= $revenueData; ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '৳' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
