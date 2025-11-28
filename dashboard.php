<?php
	include('dashboard-bckend.php');
	$widget_data = getSaleWidgetData();
	$recent_orders = getRecentOrders();

	// Default
	$end = date('Y-m-d');
	$start = date('Y-m-d', strtotime($end . '-7 days'));
	$graph_data = getChartData($start, $end);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard - POS</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <style>
        /* Dashboard Specific Styles */
        .dashboard-container {
            padding: 30px;
        }
        
        .widget-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .widget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .widget-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.1;
            color: currentColor;
        }
        
        .widget-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #fff 0%, #ccc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .widget-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .widget-sale { color: #2ecc71; }
        .widget-qty { color: #3498db; }
        .widget-order { color: #9b59b6; }
        
        .glass-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        
        .glass-table th {
            color: var(--text-secondary);
            font-weight: 500;
            padding: 10px 15px;
            border: none;
        }
        
        .glass-table td {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px;
            color: var(--text-primary);
            border: none;
        }
        
        .glass-table tr td:first-child { border-radius: 10px 0 0 10px; }
        .glass-table tr td:last-child { border-radius: 0 10px 10px 0; }
        
        .glass-table tr:hover td {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .chart-container {
            min-height: 400px;
        }
        
        .btn-glass {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        /* Highcharts Dark Theme Overrides */
        .highcharts-background { fill: transparent; }
        .highcharts-title { fill: var(--text-primary) !important; font-family: 'Poppins', sans-serif !important; }
        .highcharts-axis-title { fill: var(--text-secondary) !important; }
        .highcharts-axis-labels { fill: var(--text-secondary) !important; }
        .highcharts-legend-item text { fill: var(--text-secondary) !important; }
        .highcharts-grid-line { stroke: rgba(255,255,255,0.05); }
    </style>
</head>
<body>
	<div class="container-fluid dashboard-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold m-0">Dashboard</h2>
                <p class="text-muted m-0">Overview of your store's performance</p>
            </div>
            <div>
                <a href="pos.php" class="btn btn-glass me-2">
                    <i class="fas fa-cash-register me-2"></i> POS
                </a>
                <a href="add_products.php" class="btn btn-glass">
                    <i class="fas fa-box me-2"></i> Products
                </a>
            </div>
        </div>

        <!-- Widgets -->
		<div class="row g-4 mb-5">
			<div class="col-md-4">
				<div class="widget-card widget-sale">
                    <i class="fas fa-coins widget-icon"></i>
					<div class="widget-value">PKR <?= number_format($widget_data['sale_amt'], 2) ?></div>
					<div class="widget-label">Total Sales</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="widget-card widget-qty">
                    <i class="fas fa-shopping-basket widget-icon"></i>
					<div class="widget-value"><?= number_format($widget_data['qty']) ?></div>
					<div class="widget-label">Quantity Sold</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="widget-card widget-order">
                    <i class="fas fa-receipt widget-icon"></i>
					<div class="widget-value"><?= number_format($widget_data['orders']) ?></div>
					<div class="widget-label">Total Orders</div>
				</div>
			</div>
		</div>

        <!-- Charts and Tables -->
		<div class="row g-4">
            <!-- Recent Orders -->
			<div class="col-lg-5">
				<div class="glass-panel h-100">
					<h4 class="fw-bold mb-4">Recent Orders</h4>
					<?php if(count($recent_orders)){ ?>
                    <div class="table-responsive">
                        <table class="glass-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td class="fw-bold text-success">PKR <?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="small text-muted"><?= date('M d, h:i A', strtotime($order['date_created'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
					<?php } else { ?>
						<div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                            <p>No recent orders found.</p>
                        </div>
					<?php } ?>
				</div>
			</div>

            <!-- Sales Chart -->
			<div class="col-lg-7">
				<div class="glass-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
					    <h4 class="fw-bold m-0">Sales Analytics</h4>
                        <button class="btn btn-sm btn-glass" id="daterange">
                            <i class="far fa-calendar-alt me-2"></i> Select Range
                        </button>
                    </div>
					<div id="container" class="chart-container"></div>
				</div>
			</div>
		</div>
	</div>

    <!-- Scripts -->
    <script src="js/jquery/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        // Highcharts Dark Theme Configuration
        Highcharts.theme = {
            colors: ['#2ecc71', '#3498db', '#9b59b6', '#f1c40f', '#e74c3c', '#34495e', '#f39c12', '#16a085'],
            chart: {
                backgroundColor: 'transparent',
                style: { fontFamily: 'Poppins, sans-serif' }
            },
            title: { style: { color: '#E0E0E0' } },
            legend: { itemStyle: { color: '#E0E0E0' }, itemHoverStyle: { color: '#FFF' } },
            xAxis: {
                gridLineColor: 'rgba(255,255,255,0.05)',
                labels: { style: { color: '#B0B0B0' } },
                lineColor: 'rgba(255,255,255,0.1)',
                tickColor: 'rgba(255,255,255,0.1)'
            },
            yAxis: {
                gridLineColor: 'rgba(255,255,255,0.05)',
                labels: { style: { color: '#B0B0B0' } },
                title: { style: { color: '#E0E0E0' } }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.85)',
                style: { color: '#F0F0F0' },
                borderColor: '#2ecc71'
            }
        };
        Highcharts.setOptions(Highcharts.theme);

        function toDateRange(){
            $('#daterange').daterangepicker({
                opens: 'left',
                maxDate: moment(),
                locale: {
                    format: 'MMMM D, YYYY'
                }
            }, function(start, end, label) {
                let startF = start.format('YYYY-MM-DD');
                let endF = end.format('YYYY-MM-DD');

                $('#daterange').html('<i class="far fa-calendar-alt me-2"></i>' + start.format('MMM D') + ' - ' + end.format('MMM D'));

                $.get('dashboard-bckend.php?action=getGraphData&start='+startF+'&end='+endF,function(response){
                    visualize(response);
                },'json');
            });
        }

        function visualize(graphData){
            Highcharts.chart('container', {
                chart: { type: 'spline' },
                title: { text: null },
                xAxis: {
                    categories: graphData['categories'],
                    labels: { style: { fontSize:'12px' } }
                },
                yAxis: {
                    title: { text: 'Sales Amount (PKR)' },
                    labels: {
                        format: '{value}',
                        style: { fontSize: '12px' }
                    },
                    gridLineWidth: 1
                },
                tooltip: {
                    crosshairs: true,
                    shared: true,
                    valuePrefix: 'PKR ',
                    headerFormat: '<span style="font-size: 14px; font-weight: bold">{point.key}</span><br/>'
                },
                plotOptions: {
                    spline: {
                        marker: {
                            radius: 4,
                            lineColor: '#2ecc71',
                            lineWidth: 2,
                            fillColor: '#fff'
                        },
                        lineWidth: 3,
                        states: {
                            hover: { lineWidth: 4 }
                        }
                    }
                },
                series: [{
                    name: 'Daily Sales',
                    data: graphData['series'],
                    color: '#2ecc71',
                    fillOpacity: 0.1,
                    type: 'area' // Changed to area for better visual
                }],
                credits: { enabled: false }
            });
        }

        // Initialize
        visualize(<?= $graph_data ?>);
        toDateRange();
    </script>
</body>
</html>