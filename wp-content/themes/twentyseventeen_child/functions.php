<?php

//Custom Post type
function movies_init(){
    register_post_type('movie', array(
        'labels'             => array(
            'name'               => 'Movies',
            'singular_name'      => 'Movies',
            'add_new'            => 'Add new',
            'add_new_item'       => 'Add new movie',
            'edit_item'          => 'Edit movie',
            'new_item'           => 'New movie',
            'view_item'          => 'View movie',
            'search_items'       => 'Search movie',
            'not_found'          =>  'Movie not found',
            'not_found_in_trash' => 'No movie in cart',
            'parent_item_colon'  => '',
            'menu_name'          => 'Movies'

        ),
        'public'               => true,
        'menu_icon'            => 'dashicons-format-video',
        'publicly_queryable'   => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'query_var'            => true,
        'rewrite'              => true,
        'capability_type'      => 'post',
        'has_archive'          => true,
        'hierarchical'         => false,
        'menu_position'        => 5,
        'supports'             => array('title','editor','thumbnail')
    ) );
}
add_action('init', 'movies_init');
//end Custom post type

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------





function edit_form_after_title() {
    global $post, $wp_meta_boxes;
    do_meta_boxes( get_current_screen(), 'after_title', $post );
    unset( $wp_meta_boxes['post']['after_title'] );
}
add_action( 'edit_form_after_title', 'edit_form_after_title' );






//--------------------------------------------------------------------------------------------------------------------------------------------------------------------




add_action( 'add_meta_boxes', 'add_subtitle_metaboxes' );
function add_subtitle_metaboxes() {
    add_meta_box('custom_subtitle', 'SubTitle', 'custom_subtitle', 'movie', 'after_title', 'high');
}


function custom_subtitle() {
    global $post;
    $subtitle = get_post_meta($post->ID, '_subtitle', true);
    echo '<input type="text" placeholder="Subtitle" name="_subtitle" value="' . $subtitle  . '" class="widefat" />';
}


function wpt_save_subtitle_meta($post_id, $post) {
    if ( !current_user_can( 'edit_post', $post->ID ))
        return $post->ID;
    $subtitle_meta['_subtitle'] = $_POST['_subtitle'];

    foreach ($subtitle_meta as $key => $value) {
        if( $post->post_type == 'revision' ) return;
        $value = implode(',', (array)$value);
        if(get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else {
            add_post_meta($post->ID, $key, $value);
        }
        if(!$value) delete_post_meta($post->ID, $key);
    }
}
add_action('save_post', 'wpt_save_subtitle_meta', 1, 2);



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------




//Price
add_action( 'add_meta_boxes', 'add_price_metaboxes' );
function add_price_metaboxes() {
    add_meta_box('custom_price', 'price', 'custom_price', 'movie', 'side', 'high');
}


function custom_price() {
    global $post;
    $price = get_post_meta($post->ID, '_price', true);
    echo '<input type="number" placeholder="price" name="_price" value="' . $price  . '" style="width:25%"/>$';
}


function wpt_save_price_meta($post_id, $post) {
    if ( !current_user_can( 'edit_post', $post->ID ))
        return $post->ID;
    $price_meta['_price'] = $_POST['_price'];

    foreach ($price_meta as $key => $value) {
        if( $post->post_type == 'revision' ) return;
        $value = implode(',', (array)$value);
        if(get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else {
            add_post_meta($post->ID, $key, $value);
        }
        if(!$value) delete_post_meta($post->ID, $key);
    }
}
add_action('save_post', 'wpt_save_price_meta', 1, 2);


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------


//Read Custom Post Type As WooCommerce Product
class WCCPT_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {

    public function read( &$product ) {
        $product->set_defaults();
        if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'movie', 'product' ) ) ) { // change birds with your post type
            throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
        }
        $id = $product->get_id();

        $product->set_props( array(
            'name'              => $post_object->post_title,
            'slug'              => $post_object->post_name,
            'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
            'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
            'status'            => $post_object->post_status,
            'description'       => $post_object->post_content,
            'short_description' => $post_object->post_excerpt,
            'parent_id'         => $post_object->post_parent,
            'menu_order'        => $post_object->menu_order,
            'reviews_allowed'   => 'open' === $post_object->comment_status,
        ) );

        $this->read_attributes( $product );
        $this->read_downloads( $product );
        $this->read_visibility( $product );
        $this->read_product_data( $product );
        $this->read_extra_data( $product );
        $product->set_object_read( true );
    }

    public function get_product_type( $product_id ) {
        $post_type = get_post_type( $product_id );
        if ( 'product_variation' === $post_type ) {
            return 'variation';
        } elseif ( in_array( $post_type, array( 'movie', 'product' ) ) ) { // change birds with your post type
            $terms = get_the_terms( $product_id, 'product_type' );
            return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
        } else {
            return false;
        }
    }
}
add_filter( 'woocommerce_data_stores', 'woocommerce_data_stores' );

function woocommerce_data_stores ( $stores ) {
    $stores['product'] = 'WCCPT_Product_Data_Store_CPT';
    return $stores;
}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------

//add price and subtitle taxonomy to movie content
add_filter('the_content','new_post_content', 20,2);
function new_post_content($content){
    global $post;
    if ($post->post_type !== 'movie') {return $content; }

    ob_start();
    ?>

    <?php echo get_post_meta( get_the_ID(), '_subtitle', true ); ?>
    <br><strong>Price </strong><?php echo get_post_meta( get_the_ID(), '_price', true ); ?>$ <br>

    <?php
    return  ob_get_clean() . $content;
}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------

//add to cart button
add_filter('the_content','rei_add_to_cart_button', 20,2);
function rei_add_to_cart_button($content){
    global $post;
    if ($post->post_type !== 'movie') {return $content; }

    ob_start();
    ?>

    <form action="" method="post">
        <input name="add-to-cart" type="hidden" value="<?php echo $post->ID ?>" />
        <input name="quantity" type="number" value="1" min="1"  />
        <input name="submit" type="submit" value="Buy" />
    </form>

    <?php
    return $content . ob_get_clean();
}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------//--------------------------------------------------------------------------------------------------------------------------------------------------------------------

//add wishlist button
//add_filter('the_content','wishlist_button', 20,2);
//function wishlist_button($content){
//    global $post;
//    if ($post->post_type !== 'movie') {return $content; }
//
//    ob_start();
//    ?>
<!---->
<!--    <div class="yith-wcwl-add-to-wishlist add-to-wishlist---><?php //echo $post->ID ?><!--">-->
<!--        <div class="yith-wcwl-add-button show" style="display:block">-->
<!---->
<!---->
<!--            <a href="movie/?add_to_wishlist=--><?php //echo $post->ID ?><!--" rel="nofollow" data-product-id="--><?php //echo $post->ID ?><!--" data-product-type="simple" class="add_to_wishlist">-->
<!--                Add to Wishlist</a>-->
<!--            <img src="http://dev.alien.in.ua/wp-content/plugins/yith-woocommerce-wishlist/assets/images/wpspin_light.gif" class="ajax-loading" alt="loading" width="16" height="16" style="visibility:hidden">-->
<!--        </div>-->
<!---->
<!--        <div class="yith-wcwl-wishlistaddedbrowse hide" style="display:none;">-->
<!--            <span class="feedback">Product added!</span>-->
<!--            <a href="http://dev.alien.in.ua/wishlist/" rel="nofollow">-->
<!--                Browse Wishlist	        </a>-->
<!--        </div>-->
<!---->
<!--        <div class="yith-wcwl-wishlistexistsbrowse hide" style="display:none">-->
<!--            <span class="feedback">The product is already in the wishlist!</span>-->
<!--            <a href="http://dev.alien.in.ua/wishlist/" rel="nofollow">-->
<!--                Browse Wishlist	        </a>-->
<!--        </div>-->
<!---->
<!--        <div style="clear:both"></div>-->
<!--        <div class="yith-wcwl-wishlistaddresponse"></div>-->
<!---->
<!--    </div>-->
<!---->
<!--    --><?php
//    return $content . ob_get_clean();
//}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------

//Create category for movie taxonomy
function movie_category_taxonomies() {
    register_taxonomy(
        'movie-category',
        'movie',
        array(
            'labels' => array(
                'name' => 'Movie Category',
                'add_new_item' => 'Add New Movie Category',
                'new_item_name' => "New Movie Type Category"
            ),
            'show_ui' => true,
            'show_tagcloud' => true,
            'public' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'hierarchical' => true
        )
    );
}
add_action( 'init', 'movie_category_taxonomies', 0 );



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------



/**
 * Set a custom add to cart URL to redirect to
 * @return string
 */
function custom_add_to_cart_redirect() {
    return '/checkout/';
}
add_filter( 'woocommerce_add_to_cart_redirect', 'custom_add_to_cart_redirect' );




//--------------------------------------------------------------------------------------------------------------------------------------------------------------------



// Login redirects
function custom_login() {
    echo header("Location: " . get_bloginfo( 'url' ) . "/movie");
}

add_action('login_head', 'custom_login');

function login_link_url( $url ) {
    $url = get_bloginfo( 'url' ) . "/login";
    return $url;
}
add_filter( 'login_url', 'login_link_url', 10, 2 );


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------


//registration redirect
function my_registration_redirect() {
    return home_url( '/movie' );
}

add_filter( 'registration_redirect', 'my_registration_redirect' );




//--------------------------------------------------------------------------------------------------------------------------------------------------------------------


//add custom user field

add_action ( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action ( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields ( $user )
{
    ?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="skype">skype</label></th>
            <td>
                <input type="text" name="skype" id="skype" value="<?php echo esc_attr( get_the_author_meta( 'skype', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Please enter your skype username.</span>
            </td>
        </tr>
    </table>
    <?php
}

add_action ( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action ( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id )
{
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;
    update_usermeta( $user_id, 'skype', $_POST['skype'] );
}

//add custom user field to register form
add_action('register_form','show_first_name_field');
add_action('register_post','check_fields',10,3);
add_action('user_register', 'register_extra_fields');

function show_first_name_field()
{
    ?>
    <p>
        <label>skype<br/>
            <input id="skype" type="text" tabindex="30" size="25" value="<?php echo $_POST['skype']; ?>" name="skype" />
        </label>
    </p>
    <?php
}

function check_fields ( $login, $email, $errors )
{
    global $skype;
    if ( $_POST['skype'] == '' )
    {
        $errors->add( 'empty_realname', "<strong>ERROR</strong>: Please Enter your skype handle" );
    }
    else
    {
        $skype = $_POST['skype'];
    }
}

function register_extra_fields ( $user_id, $password = "", $meta = array() )
{
    update_user_meta( $user_id, 'skype', $_POST['skype'] );
}





//--------------------------------------------------------------------------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------