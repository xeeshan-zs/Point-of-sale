<?php
	include('product.php');
	$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Product Management - POS</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="stylesheet" href="style.css?v=<?= time() ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/css/bootstrap-dialog.min.css" />
    <style>
        /* Specific overrides for this page */
        .product-form-container {
            padding: 30px;
        }
        .product-list-container {
            height: calc(100vh - 40px);
            overflow-y: auto;
            padding: 20px;
        }
        .product-list-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background 0.2s;
            cursor: pointer;
            border-radius: 12px;
        }
        .product-list-item:hover {
            background: rgba(0, 122, 255, 0.05);
        }
        .product-list-img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }
        .product-list-info {
            flex-grow: 1;
        }
        .product-list-actions {
            display: flex;
            gap: 10px;
        }
        .btn-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: none;
            transition: all 0.2s;
        }
        .btn-edit { background: rgba(0, 122, 255, 0.1); color: #007AFF; }
        .btn-edit:hover { background: #007AFF; color: white; }
        .btn-delete { background: rgba(255, 59, 48, 0.1); color: #FF3B30; }
        .btn-delete:hover { background: #FF3B30; color: white; }
        
        .form-label { font-weight: 500; color: var(--text-secondary); }
        .form-control {
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 12px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
            border-color: #007AFF;
        }
        .preview-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-top: 10px;
            display: none;
            border: 2px dashed rgba(0,0,0,0.1);
        }
    </style>
    <script>
        // Handle broken images
        function imgError(image) {
            image.onerror = "";
            image.src = "https://via.placeholder.com/150?text=No+Image";
            return true;
        }
    </script>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
            <!-- Left Column: Form -->
			<div class="col-md-5">
                <div class="glass-panel product-form-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0 fw-bold" id="formTitle">Add New Product</h3>
                        <button class="btn btn-sm btn-secondary rounded-pill px-3" onclick="resetForm()">
                            <i class="fas fa-plus me-1"></i> New
                        </button>
                    </div>

                    <form id="productForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="productId">
                        
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="product_name" id="productName" required placeholder="e.g. Fresh Apple">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="productDesc" rows="2" placeholder="Product details..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Price (PKR)</label>
                                <input type="number" class="form-control" name="price" id="productPrice" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock" id="productStock" required placeholder="0">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="img" id="productImg" accept="image/*" onchange="previewImage(this)">
                            <img id="imgPreview" class="preview-img" src="">
                        </div>

                        <button type="submit" class="checkoutBtn" id="submitBtn">
                            Save Product
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="pos.php" class="text-decoration-none text-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to POS
                        </a>
                    </div>
                </div>
			</div>

            <!-- Right Column: List -->
			<div class="col-md-7">
				<div class="glass-panel product-list-container">
                    <h4 class="fw-bold mb-4 ps-2">Existing Products</h4>
                    
                    <div id="productList">
                        <?php foreach($products as $product): ?>
                        <div class="product-list-item" onclick="loadProduct(<?= $product['id'] ?>)">
                            <img src="images/<?= $product['img'] ?>" class="product-list-img" alt="" onerror="imgError(this)">
                            <div class="product-list-info">
                                <h5 class="m-0 fw-bold"><?= $product['product_name'] ?></h5>
                                <p class="m-0 text-muted small"><?= $product['description'] ?></p>
                                <p class="m-0 text-primary fw-bold mt-1">PKR <?= number_format($product['price'], 2) ?> | Stock: <?= $product['stock'] ?></p>
                            </div>
                            <div class="product-list-actions">
                                <button class="btn-icon btn-edit" onclick="loadProduct(<?= $product['id'] ?>); event.stopPropagation();">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= addslashes($product['product_name']) ?>'); event.stopPropagation();">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
				</div>
			</div>			
		</div>
	</div>

<script src="js/jquery/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/js/bootstrap-dialog.js"></script>



<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imgPreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resetForm() {
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#formTitle').text('Add New Product');
        $('#submitBtn').text('Save Product');
        $('#imgPreview').hide().attr('src', '');
    }

    function loadProduct(id) {
        $.get('product.php?action=get_product&id=' + id, function(data) {
            const product = JSON.parse(data);
            $('#productId').val(product.id);
            $('#productName').val(product.product_name);
            $('#productDesc').val(product.description);
            $('#productPrice').val(product.price);
            $('#productStock').val(product.stock);
            
            if(product.img) {
                $('#imgPreview').attr('src', 'images/' + product.img).show().attr('onerror', 'imgError(this)');
            } else {
                $('#imgPreview').hide();
            }
            
            $('#formTitle').text('Edit Product');
            $('#submitBtn').text('Update Product');
        });
    }

    function deleteProduct(id, name) {
        console.log('Attempting to delete product:', id, name);
        
        if(confirm('Are you sure you want to delete ' + name + '?')) {
            console.log('User confirmed deletion for ID:', id);
            $.ajax({
                url: 'product.php?action=delete_product',
                type: 'POST',
                data: {id: id},
                success: function(response) {
                    console.log('Server raw response:', response);
                    try {
                        const res = JSON.parse(response);
                        console.log('Parsed response:', res);
                        if(res.success) {
                            console.log('Deletion successful, reloading...');
                            location.reload();
                        } else {
                            console.error('Delete failed (server message):', res.message);
                            alert('Failed to delete product: ' + res.message);
                        }
                    } catch(e) {
                        console.error('JSON Parse error:', e);
                        console.error('Response was:', response);
                        alert('Error: Invalid response from server.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Network error: ' + error);
                }
            });
        } else {
            console.log('Deletion cancelled by user.');
        }
    }

    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = $('#productId').val();
        const action = id ? 'update_product' : 'add_product';
        
        $.ajax({
            url: 'product.php?action=' + action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const res = JSON.parse(response);
                if(res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                }
            }
        });
    });
</script>
</body>
</html>
