<?php
	include('product.php');
	$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PHP Project - Point of Sale System</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<!-- Custom Styles -->
	<link rel="stylesheet" href="style.css?v=<?= time() ?>">

	<!-- BootstrapDialog  -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/css/bootstrap-dialog.min.css" integrity="sha512-PvZCtvQ6xGBLWHcXnyHD67NTP+a+bNrToMsIdX/NUqhw+npjLDhlMZ/PhSHZN4s9NdmuumcxKHQqbHlGVqc8ow==" crossorigin="anonymous" />
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-8">
				<div class="searchInputContainer">
					<input type="text" id="searchInput" placeholder="Search product...">
					<!-- 
						1. Create container of search results 
						2. Design the search result entry
						3. Implement the js file
					-->
					<div id="searchResultContainerMain">		
					</div>
				</div>

				<div class="searchResultContainer">
					<div class="row">
						<?php foreach($products as $product){ ?>
						<div class="col-4 productColContainer" data-pid="<?= $product['id'] ?>">
							<div class="productResultContainer">
								<img src="images/<?= $product['img'] ?>" class="productImage" alt="">
								<div class="productInfoContainer">
									<div class="row">
										<div class="col-md-8">
											<p class="productName"><?= $product['product_name'] ?></p>
										</div>
										<div class="col-md-4">
											<p class="productPrice">PKR <?= $product['price'] ?></p>
										</div>
									</div>
								</div> 								
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="col-4 posOrderContainer">
				<div class="pos_header">
					<div class="setting alignRight">
                        <a href="add_products.php" target="_blank" style="margin-right: 15px;">
							<i class="fas fa-box"></i> Products
						</a>
						<a href="dashboard.php" target="_blank" id="showDashboardBtn">
							Show Dashboard
						</a>
					</div>
					<p class="logo"> POS</p>
					<p class="timeAndDate">XXX X,XXXX XX:XX:XX XX</p>					 
				</div>
				<div class="pos_items_container">
					<div class="pos_items">
						<p class="itemNoData">No data</p>
					</div>
					<div class="item_total_container">
						<p class="item_total">
							<span class="item_total--label">TOTAL</span>
							<span class="item_total--value">PKR 0.00</span>
						</p>
					</div>			
				</div>
				<div class="checkoutBtnContainer">
					<a href="javascript:void(0);" class="checkoutBtn"> CHECKOUT </a>
				</div>
			</div>			
		</div>
	</div>

<!-- Create a global js variable to hold products -->
<script>
	let productsJson = <?= json_encode($products) ?>;
	var products = {};

	// Loop through products
	productsJson.forEach((product) => {
		products[product.id] = {
			name: product.product_name,
			stock: product.stock,
			price: product.price
		}
	});


	// Live search feature
    var typingTimer;               // Timer identifier
    var doneTypingInterval = 500;  //Time in ms (5 milliseconds interval / a delay after event is triggered)

    // Add event listener
    // Once user click keyboard key, this will be run
 	document.addEventListener('keyup', function(ev){
 		let el = ev.target;

 		// If searchInput is the element
 		if(el.id === 'searchInput'){
 			// Get the value
 			let searchTerm = el.value;

 			// Use clearTimeout to stop running setTimeout
 			// This will clear the timeout, to avoid calling / searching database everytime we type a key 
 			clearTimeout(typingTimer);

 			// Set timeout
 			// This is the function that calls the searchDb , which pulls the search in database
 			// After 500 milliseconds, it will be triggered.
 			typingTimer = setTimeout(function(){
 				// Call the function, and pass the searchTerm as parameter
 				searchDb(searchTerm);
 			}, doneTypingInterval);
 		}
 	});



 	function searchDb(searchTerm){ 			
		let searchResult = document.getElementById('searchResultContainerMain');

		// Check if searchterm is not empty.
		// If not empty, trigger this function
 		if(searchTerm.length){ 			
 			// Set container of result to block
 			searchResult.style.display = 'block';
	 		$.ajax({
	 			type: 'GET',
	 			data: {search_term: searchTerm},
	 			url: 'live-search.php',
	 			success: function(response){
	 				// If there is no length, we show no data found
	 				if(response.length === 0){
	 					searchResult.innerHTML = '<p class="nodatafound">no data found</p>';
	 				} else {
	 					let html = '';
	 					let searchResults = response.data;

	 					searchResults.forEach((row) => {
	 						html += `	 					
								<div class="row searchResultEntry" data-pid=${row['id']}>
									<div class="col-3">
										<img class="searchResultImg" src="images/${row['img']}" alt="">
									</div>
									<div class="col-6">
										<p class="searchResultProductName">${row['product_name']}</p>
										<p class="searchResultProductPrice">PKR ${row['price']}</p>
									</div>
								</div>`;
	 					});
 						searchResult.innerHTML = html
	 				}
	 			},
	 			dataType: 'json'
	 		})
 		} else { // Display set to none - hide searchresutl container
 			searchResult.style.display = 'none';
 		}
 	}
</script>

<script src="script.js?v=<?= time() ?>"></script>

<!-- Jquery -->
<script src="js/jquery/jquery-3.5.1.min.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<!-- Boostrap Dialog -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/js/bootstrap-dialog.js" integrity="sha512-AZ+KX5NScHcQKWBfRXlCtb+ckjKYLO1i10faHLPXtGacz34rhXU8KM4t77XXG/Oy9961AeLqB/5o0KTJfy2WiA==" crossorigin="anonymous"></script>
</body>
</html>