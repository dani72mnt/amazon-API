<?php
/**
 * @package Amazon_API
 * @version 1.0.0
 */
/*
Plugin Name: Amazon API
Description: This product designed by <a href="https://instagram.com/vishar.web">Vishar Team</a>. In order to extract, display, calculate the seller's profit and sell products from another website (example: Amazon).
Author: Danial Montazeri
Version: 1.0.0
Author URI: https://instagram.com/vishar.web
 */

// Plugin main file (amazon_api.php)
require_once plugin_dir_path( __FILE__ ) . 'includes/class-amazon-api-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/controller-amazon-api-orders.php';

// Call the create_amazon_api_orders_table method during plugin activation
register_activation_hook( __FILE__, array( 'AmazonApiOrders', 'create_amazon_api_orders_table' ) );

// Add a link to the "Amazon API settings" page as settings
function amazon_api_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=amazon_api_settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'amazon_api_settings_link');

// Init Session
function wpse16119876_init_session()
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
}
// Start session on init hook.
add_action('init', 'wpse16119876_init_session');

/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', 'theme_external_styles');
function theme_external_styles()
{
	wp_enqueue_style( 'alertify-css' , plugin_dir_url(__DIR__) . 'amazon-api/assets/css/alertify.rtl.min.css' );
	wp_enqueue_style('style_css_vishar', plugin_dir_url(__DIR__) . 'amazon-api/assets/css/style.css', array());
	
    wp_enqueue_script( 'alertify-js', plugin_dir_url(__DIR__) . 'amazon-api/assets/js/alertify.min.js' );
	wp_enqueue_script('main_js_vishar', plugin_dir_url(__DIR__) . 'amazon-api/assets/js/main.js', array('jquery'));

    // Set AJAX URL
    wp_localize_script( 'main_js_vishar', 'vishar_plugin_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

// Product link form shortcode
function product_link_form_shortcode() {
    ob_start();
	$first_name = '';
	$last_name  = '';
	$email      = '';
	if(is_user_logged_in()){
		// Get current user object
		$current_user = wp_get_current_user();

		// Retrieve user information
		$first_name = $current_user->first_name;
		$last_name  = $current_user->last_name;
		$email      = $current_user->user_email;
	}
    ?>
<div class="container-fluid" id="getProductLinkPage">
    <form id="product-link-form" action="#" method="post">
        <div class="row">
            <legend>User Information</legend>
            <div class="form-group col-md-3 mb-3">
                <label class="form-label" for="name">Name</label>
                <input class="name" type="text" class="form-control" name="name" id="name" <?= ($first_name ? 'value="'. $first_name .'"' : '') ?> required>
            </div>

            <div class="form-group col-md-3 mb-3">
                <label class="form-label" for="last_name">Last Name</label>
                <input class="last_name" type="text" class="form-control" name="last_name" id="last_name" <?= ($last_name ? 'value="'. $last_name .'"' : '') ?> required>
            </div>

            <div class="form-group col-md-3 mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="email" type="email" class="form-control" name="email" id="email" <?= ($email ? 'value="'. $email .'"' : '') ?> required>
            </div>

        </div>
        <?php if(! is_user_logged_in()){ ?>
            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="password" type="password" class="form-control" name="password" id="password" required>
                    <div id="emailHelp" class="form-text">
                        Password must be at least 8 characters long and contain uppercase and lowercase English letters, and numbers.
                    </div>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label class="form-label" for="password_repeate">Repeat Password</label>
                    <input class="password_repeate" type="password" class="form-control" name="password_repeate" id="password_repeate" required>
                </div>
            </div>
        <?php } ?>

        <div class="row">
            <legend>Product Information</legend>
            <div class="form-group col-md-3 mb-3">
                <label class="form-label" for="product_link">Product Link</label>
                <input class="product_link" type="url" class="form-control" name="product_link" id="product_link" required>
            </div>

            <div class="form-group col-md-3 mb-3">
                <label class="form-label" for="website">Select Website</label>
                <select id="website" name="website" class="website form-select" aria-label="Default select example" required>
                    <option selected disabled>Please select one of the following websites</option>
                    <option value="1">Amazon</option>
                    <option value="2">Amazon UAE</option>
                    <option value="3">AliExpress</option>
                    <option value="4">Test</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3 mt-2">
                <button id="getLinkFormSubmit" type="submit" class="btn btn-primary w-100">Submit</button>
            </div>
        </div>


    </form>
</div>
<div class="container-fluid" id="responseSection"></div>

    <?php
    return ob_get_clean();
}
add_shortcode('product_link_form', 'product_link_form_shortcode');

/**
 * Create Product Link Form Page
*/
function create_product_link_form_page() {
    $page_slug = 'product-link-form';
	$page_title = 'Submit Product Link';

    // Check if the page exists
    $page = get_page_by_path($page_slug);

    // If the page doesn't exist, create it
    if (!$page) {
        // Set the page attributes
        $page_attributes = array(
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );

        // Insert the page into the database
        $page_id = wp_insert_post($page_attributes);

        // Add the shortcode to the page content
        $shortcode = '[product_link_form]';
		$description = '<p>To order a product from one of the websites <b>Amazon, Amazon UAE, or AliExpress</b>, please fill out the form below completely.</p>';
		$page_content = $description;
        $page_content .= '<br><hr>';
        $page_content .= $shortcode;
        $page_content .= '<br>';

        // Update the page content
        $page_attributes['ID'] = $page_id;
        $page_attributes['post_content'] = $page_content;
        wp_update_post($page_attributes);

        // Optionally, set a custom template for the page
        // $template = 'path/to/your/custom-template.php';
        // update_post_meta($page_id, '_wp_page_template', $template);
    }
}
// Hook the function to a specific action
add_action('init', 'create_product_link_form_page');

// Login User
function user_login($first_name, $last_name, $user_email, $password, $existing_user){
	$user = wp_authenticate($existing_user->user_login, $password);
    if (is_wp_error($user)) {
		$response = array(
            'status' => 'warning',
            'message' => 'Incorrect password!',
        );
        wp_send_json($response);
        exit;
    }

	// Save the first name and last name
    update_user_meta($user->ID, 'first_name', $first_name);
    update_user_meta($user->ID, 'last_name', $last_name);

    // Set the user as logged in
    wp_set_current_user($user->ID, $user->user_login);
    wp_set_auth_cookie($user->ID);
}

// Register User
function user_register($first_name, $last_name, $user_email, $password){
	// User data is valid, proceed with registration
	$user_data = array(
		'user_login' => $user_email,
		'first_name' => $first_name,
		'last_name'  => $last_name,
		'user_email' => $user_email,
		'user_pass'  => $password,
	);

	// Register the new user
	$user_id = wp_insert_user($user_data);
	if (is_wp_error($user_id)) {
		$response = array(
            'status' => 'warning',
            'message' => 'Error in user registration. Please try again!'
        );
        wp_send_json($response);
		exit;
	}
}

// chdir to Current Path
function chdir_to_current_path(){
	$current_path = ABSPATH;
	$current_path = str_replace('/', '', $current_path);
	$current_path .= '\wp-content\plugins\amazon-api';
	chdir($current_path);
}

// store product id
function store_product_id_in_database($product_id) {
    $option_name = 'vishar_product_id';

    // Update the product ID in the 'vishar_product_id' option
    update_option($option_name, $product_id);
}

// get product id
function get_product_id_from_database() {
    $option_name = 'vishar_product_id';

    // Get the product ID from the 'vishar_product_id' option
    $product_id = get_option($option_name);

    return $product_id;
}

// Save product image to library
function save_image_to_library($image_url, $title) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);

    if (!$image_data) {
        return false; // Unable to fetch image data from the URL
    }

    $filename = basename($image_url);
    $filename = sanitize_file_name($title) . '-' . sanitize_file_name($filename); // Sanitize the file name

    // Check if an image with the same filename already exists in the media library
    $existing_attachment_id = attachment_url_to_postid($upload_dir['url'] . '/' . $filename);

    if ($existing_attachment_id) {
        return $existing_attachment_id; // Return the existing attachment ID
    }

    // Save the image to the uploads directory
    $file_path = $upload_dir['path'] . '/' . $filename;
    file_put_contents($file_path, $image_data);

    // Prepare the attachment data
    $filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $filetype['type'],
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'inherit'
    );

    // Insert the image into the media library
    $attachment_id = wp_insert_attachment($attachment, $file_path);

    // Generate metadata for the attachment
    $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attach_data);

    // Return the attachment ID of the saved image
    return $attachment_id;
}

// Set product attributes
function wcproduct_set_attributes($post_id, $attributes) {
    $i = 0;
    // Loop through the attributes array
    foreach ($attributes as $name => $value) {
        $product_attributes[$i] = array (
            'name' => htmlspecialchars( stripslashes( $name ) ), // set attribute name
            'value' => $value, // set attribute value
            'position' => 1,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 0
        );

        $i++;
    }
    // Now update the post with its new attributes
    update_post_meta($post_id, '_product_attributes', $product_attributes);
}

// Create product
function add_product($image, $title, $price, $weight, $dimensions) {

    $product_id = get_product_id_from_database();
	$product = wc_get_product($product_id);
	$image_id = save_image_to_library($image, $title);
	$dollar_price = get_option('dollar_price', 1.0);
	$price = $price * $dollar_price;

	if (!$product) {
		// Create a new product
		$product = new WC_Product_Simple();
		$product->set_name($title);
		$product->set_regular_price($price); 
		$product->set_image_id($image_id);
		
		// Save the updated product data
		$product->set_status('publish');
		$product->save();

		// get the prodcut id
		$product_id = $product->get_id();

		// store product id on daatabase
		store_product_id_in_database($product_id);
		
		// Add attributes
		$product_attributes = array('weight' => $weight, 'dimensions' => $dimensions);
    	wcproduct_set_attributes($product_id, $product_attributes);

		return $product_id;

	}else{
		// Update the product data
		$product->set_props(array(
			'image_id' => $image_id,
			'name' => $title,
			'regular_price' => $price
		));

		// Save the updated product data
		$product->set_status('publish');
		$product->save();

		// Set the image as the product thumbnail
		set_post_thumbnail($product_id, $image_id);

		$product_attributes = array('weight' => $weight, 'dimensions' => $dimensions);
    	wcproduct_set_attributes($product_id, $product_attributes);

		return $product_id;
	}
}

// Save the product id a session
function create_product_session($product_id, $image, $title, $price, $weight, $dimensions){

	// Start the session (if not already started)
	if (!session_id()) {
		session_start();
	}

	// Create an array to store the data
	$productData = array(
		'product_id' => $product_id,
		'image' => $image,
		'title' => $title,
		'price' => $price,
		'weight' => $weight,
		'dimensions' => $dimensions
	);

	// Save the data to a session variable
	$_SESSION['product_data'] = $productData;
}

// Read JSON File
function read_JSON_file($file, $user_id){
	
	// Open the file
	$handle = fopen($file, 'r');

	if ($handle) {

		// Read the contents of the file
		$contents = fread($handle, filesize($file));

		// Close the file
		fclose($handle);
		
		// Delete the file
		unlink($file);

		// Decode the JSON string into an associative array
		$data = json_decode($contents, true);

		if (isset($data['image']) && isset($data['title']) && isset($data['price'])) {

			// Extract the values
			$image = $data['image'];
			$title = $data['title'];
			$price = $data['price'];
			$weight = (isset($data['weight']) ? $data['weight'] : '');
			$dimensions = (isset($data['dimensions']) ? $data['dimensions'] : '');

			// Create a product
			$product_id = add_product($image, $title, $price, $weight, $dimensions);

			// Save the data to a session variable
			create_product_session($product_id, $image, $title, $price, $weight, $dimensions);

			$output = '
			<button type="button" class="btn btn-primzary d-none" data-bs-toggle="modal" data-bs-target="#productModal">
				View the product
			</button>

			<div class="modal fade" id="productModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">'. $title .'</h5>
						</div>
						<div class="modal-body row">
							<div class="col-md-6">
								<img src="'. $image .'" alt="Product Image" class="img-fluid">
							</div>
							<div class="col-md-6">
								<p><b>Price:</b> '. $price .'</p>
								<p><b>Weight:</b> '. ($weight ? $weight : '...') .'</p>
								<p><b>Dimensions:</b> '. ($dimensions ? $dimensions : '...') .'</p>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button id="confirmAndContinue" type="button" class="btn btn-primary">Confirm</button>
						</div>
					</div>
				</div>
			</div>';

			$output .= "<script>jQuery('#productModal').modal('show');</script>";

			$response = array(
				'status' => 'success',
				'output' => $output
			);
			wp_send_json($response);
			exit;
			
		} else {
			$response = array(
				'status' => 'warning',
				'message' => 'Error! please try again later.'
			);
			wp_send_json($response);
			exit;
		}
		
	} else {
		$response = array(
			'status' => 'warning',
			'message' => 'Error! please try again later.'
		);
		wp_send_json($response);
		exit;
	}	
}

// Extract Product From Link
function get_product_data($product_link, $website, $user_id){
	
	$output = '';
	$data = array();
	$url = $product_link;

	// Define Selectors
	$image = ''; $title = ''; $price = ''; $weight = ''; $dimensions = '';

	if ($website == 1) {
		// Case: Amazon 
		$image = '#landingImage';
		$title = '#productTitle::text';
		$price = '.a-price .a-offscreen';
		$weight = '.po-item_weight .po-break-word';
		$dimensions = 'th:contains("Package Dimensions"), th:contains("Product Dimensions") + td::text';
	} elseif ($website == 2) {
		// Case: UAE Amazon
		$image = '#landingImage';
		$title = '#productTitle::text';
		$price = '.a-price .a-offscreen';
		$weight = '.po-item_weight .po-break-word';
		$dimensions = 'th:contains("Package Dimensions"), th:contains("Product Dimensions") + td::text';
	} elseif ($website == 3) {
		// Case: AlịExpress
		$image = '.navbar-brand img.logo';
		$title = '.home-header-content h4::text';
		$price = '';
		$weight = '';
		$dimensions = '';
	} else {
		// Default case (optional): Handle the case when $website is not 1, 2, or 3
		// $response = array(
		// 	'status' => 'warning',
		// 	'message' => 'Error! please try again later.'
		// );
		// wp_send_json($response);
		// exit;

		$url = 'https://erythrogen.com/en/';
		$image = '.navbar-brand img.logo';
		$title = '.home-header-content h4::text';
		$price = '.po-item_weight .po-break-word';
		$weight = '.po-item_weight .po-break-word';
		$dimensions = '.po-item_weight .po-break-word';
	}
	
	chdir_to_current_path();
	$command = escapeshellcmd('scrapy crawl quotes -a start_url=' . escapeshellarg($url). ' -a image=' . escapeshellarg($image). ' -a price=' . escapeshellarg($price). ' -a weight=' . escapeshellarg($weight). ' -a dimensions=' . escapeshellarg($dimensions). ' -a title=' . escapeshellarg($title). ' -a user_id=' . escapeshellarg($user_id));
	$output = shell_exec($command);
	
	$file = 'scraped_data_'. $user_id .'.json';

	// Check if the file exists
	if (file_exists($file)) {

		read_JSON_file($file, $user_id);

	} else {
		$response = array(
			'status' => 'warning',
			'message' => 'Error! please try again later.'
		);
		wp_send_json($response);
		exit;
	}
}

// Get product Data From Product Link
function get_product_from_link(){
	if(!$_POST['name'] || !$_POST['last_name'] || !$_POST['email'] || !$_POST['product_link'] || !$_POST['website'] ){
		$response = array(
			'status' => 'warning',
			'message' => 'Error! please try again later.'
		);
		wp_send_json($response);
		exit;
	}
	
	// Sanitize user input
	$first_name = sanitize_text_field($_POST['name']);
	$last_name = sanitize_text_field($_POST['last_name']);
	$user_email = sanitize_email($_POST['email']);
	$product_link = $_POST['product_link'];
	$website = $_POST['website'];

	if (!is_user_logged_in()) {
		$password = $_POST['password'];
		$password_repeated = $_POST['password_repeate'];

		if (empty($password) || empty($password_repeated)) {
			$response = array(
				'status' => 'warning',
				'message' => 'Please enter the password and its confirmation!'
			);
			wp_send_json($response);
			exit;
		}
		if ($password !== $password_repeated) {
			$response = array(
				'status' => 'warning',
				'message' => 'Password and its confirmation should match!'
			);
			wp_send_json($response);
			exit;
		}
		if (strlen($password) < 6) {
			$response = array(
				'status' => 'warning',
				'message' => 'Password must be at least 6 characters long!'
			);
			wp_send_json($response);
			exit;
		}
		// Perform additional password strength validation checks here
		if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/u', $password)) {
			$response = array(
				'status' => 'warning',
				'message' => 'Password must contain uppercase and lowercase English letters and numbers!'
			);
			wp_send_json($response);
			exit;
		}
		// Check email format
		if (!is_email($user_email)) {
			$response = array(
				'status' => 'warning',
				'message' => 'Please enter a valid email address!'
			);
			wp_send_json($response);
			exit;
		}
		// Check if the user with the provided email already exists
		$existing_user = get_user_by('email', $user_email);

		if ($existing_user) {
			user_login($first_name, $last_name, $user_email, $password, $existing_user);
		}
		else{
			user_register($first_name, $last_name, $user_email, $password);
		}

	}else{
		$user = wp_get_current_user();

		// Save the first name and last name
		update_user_meta($user->ID, 'first_name', $first_name);
		update_user_meta($user->ID, 'last_name', $last_name);
	}

	$user = wp_get_current_user();
	get_product_data($product_link, $website, $user->ID);
	
}
add_action('wp_ajax_get_product_from_link', 'get_product_from_link');
add_action('wp_ajax_nopriv_get_product_from_link', 'get_product_from_link');

// Add product to cart
function add_product_to_cart() {
    // Check if the session variable exists
    if (isset($_SESSION['product_data'])) {
        // Retrieve the data from the session
        $productData = $_SESSION['product_data'];

        // Access individual data elements
        $product_id = $productData['product_id'];

        // Check if the product is already in the cart
        $cart_item_key = WC()->cart->find_product_in_cart($product_id);

        if ($cart_item_key) {
            // Product is already in the cart, update its quantity to 1
            WC()->cart->set_quantity($cart_item_key, 1);
        } else {
            // Product is not in the cart, add it with quantity 1
            WC()->cart->add_to_cart($product_id, 1);
        }

        // Prepare the response data
        $response = array(
            'status' => 'success',
            'redirect_url' => wc_get_cart_url()
        );

        // Send the response as JSON
        wp_send_json($response);
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Session does not exist.'
        );

        // Send the response as JSON
        wp_send_json($response);
    }
}
add_action('wp_ajax_add_product_to_cart', 'add_product_to_cart');
add_action('wp_ajax_nopriv_add_product_to_cart', 'add_product_to_cart');

 /**
 * Amazon API Settings
 */
// Add a new admin menu item for "Amazon API Settings"
function add_amazon_api_settings_page() {
    add_menu_page(
        'Amazon API',
        'Amazon API',
        'manage_options',
        'amazon_api_settings',
        'amazon_api_settings_page_callback'
    );
}
add_action('admin_menu', 'add_amazon_api_settings_page');

// Create the callback function for the "Amazon API Settings" page
function amazon_api_settings_page_callback() {
    ?>
    <div class="wrap">
        <h1>Amazon API Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('amazon_api_settings');
            do_settings_sections('amazon_api_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the settings and fields
function register_amazon_api_settings() {
    // Register a new settings field for "Dollar Price"
    register_setting('amazon_api_settings', 'dollar_price');

    // Add a new section for the settings page
    add_settings_section(
        'amazon_api_settings_section',
        'Dollar Price Settings',
        'amazon_api_settings_section_callback',
        'amazon_api_settings'
    );

    // Add the field for "Dollar Price"
    add_settings_field(
        'dollar_price',
        'Dollar Price',
        'dollar_price_field_callback',
        'amazon_api_settings',
        'amazon_api_settings_section'
    );
}
add_action('admin_init', 'register_amazon_api_settings');

// Create the callback functions for the section and field
function amazon_api_settings_section_callback() {
    echo 'Enter the dollar price.';
}

function dollar_price_field_callback() {
    $dollar_price = get_option('dollar_price', 1.0);
    ?>
    <input type="number" name="dollar_price" value="<?php echo esc_attr($dollar_price); ?>" step="0.01" min="0">
    <?php
}

// Save the field value when the form is submitted
function save_amazon_api_settings() {
    if (isset($_POST['dollar_price'])) {
        $dollar_price = floatval($_POST['dollar_price']);
        update_option('dollar_price', $dollar_price);
    }
}
add_action('admin_post_save_amazon_api_settings', 'save_amazon_api_settings');

/**
 * Amazon API Order Data
 */
function handle_custom_data_after_purchase( $order_id ) {
    // Get the order object
    $order = wc_get_order( $order_id );
	
	if (isset($_SESSION['product_data'])) {
		// Retrieve the data from the session
		$productData = $_SESSION['product_data'];

		// Access individual data elements
		$product_id = $productData['product_id'];
		
		$items = $order->get_items();
		
		foreach ( $items as $item ) {
			if ( $item->get_product_id() == $product_id ) {
				// The purchased product matches $product_id
				// Call the custom data handler function
				handle_amazon_data_after_purchase( $order_id );
				break; // Exit the loop since we found the product
			}
		}
    }
}
// Hook the function to the 'woocommerce_thankyou' action
add_action( 'woocommerce_thankyou', 'handle_custom_data_after_purchase' );

// Create a sub-page under "Amazon API Settings"
add_action('admin_menu', 'amazon_api_orders_submenu_page');
function amazon_api_orders_submenu_page() {
    add_submenu_page(
        'amazon_api_settings', // Parent slug (slug of the Amazon API Settings page)
        'Amazon API Orders', // Page title
        'Records', // Menu title (changed from "Orders" to "Records")
        'manage_options', // Capability required to access the page
        'amazon-api-orders', // Menu slug
        'amazon_api_orders_page_content' // Callback function to display the page content
    );
}

// Callback function to render the sub-page content
function amazon_api_orders_page_content() {
    $orders = amazon_get_all_orders();

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Amazon API Records</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Details</th>
                    <th scope="col">Price</th>
                    <th scope="col">Total</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_number = 1;
                foreach ($orders as $order) : ?>
                    <tr>
                        <td><?php echo $row_number; ?></td>
                        <td><?php echo get_user_profile_link($order['user_id']); ?></td>
						<td>
                            <?php
                            // Check if the image link is valid
                            $image_url = esc_url($order['image']);
                            if (!empty($image_url) && filter_var($image_url, FILTER_VALIDATE_URL)) {
                                // Display the image thumbnail
                                echo '<img src="' . $image_url . '" alt="Product Thumbnail" width="50" height="50" />';
                            } else {
                                // Display a placeholder thumbnail if no image is available
                                echo '<img src="' . esc_url('URL_TO_PLACEHOLDER_IMAGE') . '" alt="No Image" width="50" height="50" />';
                            }
                            ?>
                        </td>
                        <td><?php echo $order['title']; ?></td>
                        <td><?php echo $order['weight'] . ' | ' . $order['dimensions']; ?></td>
                        <td><?php echo number_format($order['price']); ?></td>
                        <td><?php echo number_format($order['total_price']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td><?php echo get_combined_address($order); ?></td>
                    </tr>
                <?php
                    $row_number++;
                endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Helper function to get the username and link to the user profile by user ID
function get_user_profile_link($user_id) {
    $user_data = get_userdata($user_id);
    if ($user_data) {
        $user_name = $user_data->user_login;
        $user_profile_url = get_edit_user_link($user_id);
        return '<a href="' . esc_url($user_profile_url) . '">' . $user_name . '</a>';
    }
    return '';
}

// Helper function to combine address details
function get_combined_address($order) {
    $address = $order['address_1'];
    if (!empty($order['address_2'])) {
        $address .= ', ' . $order['address_2'];
    }
    $address .= ', ' . $order['city'] . ', ' . $order['state'] . ', ' . $order['country'] . ', ' . $order['postcode'];
    return $address;
}
