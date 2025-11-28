<?php
	include('sale.php');
	$sale_data = getSale($_GET['receipt_id']);
	
	$customer_data = $sale_data['customer'];
	$items = $sale_data['items'];
	$sale = $sale_data['sales'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<!-- Custom Styles -->
	<link rel="stylesheet" href="style.css?v=<?= time() ?>">

	<!-- BootstrapDialog  -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/css/bootstrap-dialog.min.css" integrity="sha512-PvZCtvQ6xGBLWHcXnyHD67NTP+a+bNrToMsIdX/NUqhw+npjLDhlMZ/PhSHZN4s9NdmuumcxKHQqbHlGVqc8ow==" crossorigin="anonymous" />
</head>
<body>
	<div id="receiptContainer" style="width: 620px;padding-bottom: 50px;padding-top: 20px;border: 1px solid #d0d0d0;margin: 0 auto;border-radius: 5px;padding-left: 10px;padding-right: 10px;">
		<h3 style="font-size: 16px;color: #828282;text-align: right;border-bottom: 1px solid #cccccc;padding-bottom: 10px;margin-top: 10px;">Original Receipt</h3>
		<div>
			<table>
				<tbody>
					<tr>
						<td><h3 style="font-size: 23px;text-transform: uppercase;color:#ff5c85;">POS Project</h3></td>
					</tr>
					<tr>
					  <td><span style="font-weight: bold;font-size: 13px;">Address: </span> <span>Mall Road</span></td>
					</tr>
					<tr>
					   <td><span style="font-weight: bold;font-size: 13px;">City: </span> <span>Lahore</span></td>
					</tr>
					<tr>
					  <td><span style="font-weight: bold;font-size: 13px;">Postal: </span> <span>54000</span></td>
					</tr>
					<tr>
						<td style="height: 20px;"></td>
					</tr>
					<tr>
						<td width="50%" style="vertical-align: top;">
							<table>
								<tbody>
									<tr>
										<td>
											<h3 style="font-size: 15px;text-transform: uppercase;">Customer Details:</h3>
										</td>
									</tr>
									<tr>
										<td>
											<span style="font-weight: bold;margin-left: 9px;font-size: 13px;">Name: </span> 
											<span><?= $customer_data['first_name'] ?> <?= $customer_data['last_name'] ?></span>
										</td>
									</tr>
									<tr><td><span style="font-weight: bold;margin-left: 9px;font-size: 13px;">Address: </span> <span><?= $customer_data['address'] ?></span></td></tr>
									<tr><td><span style="font-weight: bold;margin-left: 9px;font-size: 13px;">Contact: </span> <span><?= $customer_data['contact'] ?></span></td></tr>
									<tr>
										<td style="height: 20px;"></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td width="50%" style="vertical-align: top;">
							<table>
								<tbody>
									<tr>
									  <td><h3 style="font-size: 15px;text-transform: uppercase;">Order Details: </h3></td>
									</tr>
									<tr>
									   <td><span style="font-weight: bold;margin-left: 9px;font-size: 13px;">Receipt:</span> #<span><?= $sale['id'] ?></span></td>
									</tr>
									<tr>
									  <td><span style="font-weight: bold;margin-left: 9px;font-size: 13px;">Receipt Date: </span> <span><?= date('M d,Y h:i:s A', strtotime($sale['date_created'])) ?></span></td>
									</tr>
									<tr><td></td></tr>
									<tr><td></td></tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td style="height: 25px;"></td>
					</tr>
				</tbody>
			</table>

			<div>
				<h3 style="font-size: 15px;text-transform: uppercase;">Items</h3>
			</div>
			<table style="width: 100%;">
				<tbody>					
					<tr>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse; width: 10%;font-size: 12px;font-weight: bold;text-align: center;">#</td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse; width: 10%;font-size: 12px;font-weight: bold;text-align: center;"> Name</td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;width: 30%;font-size: 12px;font-weight: bold;text-align: center;">Qty</td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse; width: 30%;font-size: 12px;font-weight: bold;text-align: center;">Price</td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse; width: 30%;font-size: 12px;font-weight: bold;text-align: center;">Amount</td>
					</tr>

					<?php
						$counter = 1;
						foreach($items as $item){
					?>					
					<tr>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 13px;text-align: center;"><?= $counter ?></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 13px;text-align: center;"><?= $item['product'] ?></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 13px;text-align: center;"><?= number_format($item['quantity']) ?></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 13px;text-align: center;">$ <?= number_format($item['unit_price'], 2, '.' , ',') ?></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 13px;text-align: center;">$ <?= number_format($item['sub_total'], 2, '.' , ',') ?></td>
					</tr>
					<?php $counter++; } ?>
					<tr>
						<td style="height: 15px;"></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold; background: #ededed;">TOTAL</td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold;">$ <?= number_format($sale['total_amount'], 2, '.', ',') ?></td>
					</tr>
			
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold; text-transform: uppercase; background: #ededed;"> Tendered </td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold; text-transform: uppercase;">$ <?= number_format($sale['amount_tendered'], 2, '.', ',') ?></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold; background: #ededed;">CHANGE </td>
						<td style="border: 1px solid #bbbbbb; border-collapse: collapse;font-size: 14px;text-align: center;font-weight: bold;">$ <?= number_format($sale['change_amt'], 2, '.', ',') ?></td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
<script>
	window.print();
</script>
</body>
</html>