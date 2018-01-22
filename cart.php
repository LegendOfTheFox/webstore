<?php
session_start();
$product_ids = array();
//session_destroy();

//check if Add to Cart button has been submitted
if(filter_input(INPUT_POST, 'add_to_cart')){
    if(isset($_SESSION['shopping_cart'])){
        //keep track of how many products are in the shopping cart
        $count = count($_SESSION['shopping_cart']);
        //create sequential array for matching array keys to products ids
        $product_ids = array_column($_SESSION['shopping_cart'], 'product_id');
        
       // pre_r($product_ids);
        
        if(!in_array(filter_input(INPUT_GET, 'product_id'), $product_ids)){
           $_SESSION['shopping_cart'][$count] = array
            (
                'product_id' => filter_input(INPUT_GET, 'product_id'),
                'product_name' => filter_input(INPUT_POST, 'product_name'),
                'product_price' => filter_input(INPUT_POST, 'product_price'),
                'product_quantity' => filter_input(INPUT_POST, 'product_quantity')
            );
        }
        else{//product already exists increase the quantity
            //match array key to id of the product being added to the cart
            for($i = 0; $i < count($product_ids); $i++){
                if($product_ids[$i] == filter_input(INPUT_GET, 'product_id')){
                    //add item quantity to the existing products in the array
                    $_SESSION['shopping_cart'][$i]['product_quantity'] += filter_input(INPUT_POST, 'product_quantity');
                }
            }
        }
        
    }
    else{
        //if shopping cart doesnt exist, create first product with array key 0
        //create array using submitted form data, start from key 0 and fill it with values
        $_SESSION['shopping_cart'][0] = array
            (
                'product_id' => filter_input(INPUT_GET, 'product_id'),
                'product_name' => filter_input(INPUT_POST, 'product_name'),
                'product_price' => filter_input(INPUT_POST, 'product_price'),
                'product_quantity' => filter_input(INPUT_POST, 'product_quantity')
            );
    }
}


if(filter_input(INPUT_GET, 'action') == 'delete'){
    //loop through all products in the shopping cart until it matches with GET id variable
    foreach($_SESSION['shopping_cart'] as $key => $product){
        if($product['product_id'] == filter_input(INPUT_GET, 'product_id')){
            //remove product from the shopping cart when it matches with the GET ID
            unset($_SESSION['shopping_cart'][$key]);
        }
        //reset session array keys so they match with $product_ids numeric array
        $_SESSION['shopping_cart'] = array_values($_SESSION['shopping_cart']);
    }
}

//This is for debugging array, uncomment to view cart array details
//pre_r($_SESSION);

function pre_r($array){
    echo '<pre>';
    echo print_r($array);
    echo '</pre>';
}
?>


    <!DOCTYPE html>

    <html>

    <head>
        <title>Shopping Cart</title>
        <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/cart.css" type="text/css" />
        <meta charset="utf-8" />

    </head>

    <body>
        <main role="main">
            <div class="container">
                <div class="row">
                    <?php
            ob_start();
            // connect
            require_once('db.php');
            // Select the products
            $query = 'SELECT * FROM tbl_products ORDER by product_id ASC';
            //Prepare the connection
            $cmd = $conn->prepare($query);
            //Bind the parameters
            $cmd->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            //Execute the query
            $cmd->execute();
            //Store the results in an Array
            $result = $cmd->fetchAll();

            //Print the names for testing
            foreach ($result as $product) {
                ?>
                        <div class="col-sm-4 col-md-3 col-lg-4">
                            <form method="post" action="cart.php?action=add&product_id=<?php echo $product['product_id']; ?>">
                                <div class="products">
                                    <img src="<?php echo $product['product_image']; ?>" class="img-responsive" width="200" height="200" />
                                    <h4 class="text-info">
                                        <?php echo $product['product_name']; ?>
                                    </h4>
                                    <h4>
                                        <?php echo $product['product_price']; ?>
                                    </h4>
                                    <input type="text" name="product_quantity" class="form-control" value="1" />
                                    <input type="hidden" name="product_name" value="<?php echo $product['product_name']; ?>" />
                                    <input type="hidden" name="product_price" value="<?php echo $product['product_price']; ?>" />
                                    <input type="submit" name="add_to_cart" class="btn btn-info" value="Add to Cart" />
                                </div>
                            </form>
                        </div>
                        <?php
            }

            //Disconnect from the database
            $conn = null;

        ?>
                            <div style="clear:both"></div>
                            <br />
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th colspan="5">
                                            <h3>Order Details</h3>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th width="40%">Product Name</th>
                                        <th width="10">Quantity</th>
                                        <th width="20">Price</th>
                                        <th width="15">Total</th>
                                        <th width="5">Action</th>
                                    </tr>
                                    <?php
                            if(!empty($_SESSION['shopping_cart'])):
                        
                                $total=0;
                        
                                foreach($_SESSION['shopping_cart'] as $key => $product):
                        ?>
                                        <tr>
                                            <td>
                                                <?php echo $product['product_name']; ?>
                                            </td>
                                            <td>
                                                <?php echo $product['product_quantity']; ?>
                                            </td>
                                            <td>$
                                                <?php echo $product['product_price']; ?>
                                            </td>
                                            <td>$
                                                <?php echo number_format($product['product_quantity'] * $product['product_price'], 2); ?>
                                            </td>
                                            <td>
                                                <a href="cart.php?action=delete&product_id=<?php echo $product['product_id']; ?>">
                                                    <div class="btn btn-danger">Remove</div>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                            $total = $total +($product['product_quantity'] * $product['product_price']);
                        endforeach;
                        ?>
                                            <tr>
                                                <td colspan="3" align="right">Total</td>
                                                <td align="right">$
                                                    <?php echo number_format($total,2); ?>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <!-- Show checkout button when cart is empty only-->
                                                <td colspan="5">
                                                    <?php
                                    if(isset($_SESSION['shopping_cart'])):
                                    if(count($_SESSION['shopping_cart']) > 0):
                                ?>
                                                        <a href="#" class="button">
                                                            <div class="btn btn-primary">Checkout</div>
                                                        </a>
                                                        <?php endif; endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                            endif;
                        ?>
                                </table>
                            </div>
                </div>
            </div>

        </main>
        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
    </body>

    </html>
