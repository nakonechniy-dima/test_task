<?php
/*
Template Name: Register
*/

// require_once(ABSPATH . WPINC . '/registration.php');
global $wpdb, $user_ID;

if ($user_ID) {

    header( 'Location:' . home_url() );

} else {

    $errors = array();

    if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

        $username = $wpdb->escape($_REQUEST['username']);
        if ( strpos($username, ' ') !== false ) {
            $errors['username'] = "Sorry, no spaces allowed in usernames";
        }
        if(empty($username)) {
            $errors['username'] = "Please enter a username";
        } elseif( username_exists( $username ) ) {
            $errors['username'] = "Username already exists, please try another";
        }

        $email = $wpdb->escape($_REQUEST['email']);
        if( !is_email( $email ) ) {
            $errors['email'] = "Please enter a valid email";
        } elseif( email_exists( $email ) ) {
            $errors['email'] = "This email address is already in use";
        }

        if(0 === preg_match("/.{6,}/", $_POST['password'])){
            $errors['password'] = "Password must be at least six characters";
        }

        if(0 !== strcmp($_POST['password'], $_POST['password_confirmation'])){
            $errors['password_confirmation'] = "Passwords do not match";
        }

        if($_POST['terms'] != "Yes"){
            $errors['terms'] = "You must agree to Terms of Service";
        }

        if(0 === count($errors)) {

            $password = $_POST['password'];
            $new_user_id = wp_create_user( $username, $password, $email );
            $success = 1;

            header( 'Location:' . get_bloginfo('url') . '/login/?success=1&u=' . $username );

        }

    }
}

?>

<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
<link href='<?php echo get_stylesheet_directory_uri();?>/form-style.css' rel='stylesheet' type='text/css'>
<div class="login-block">
    
    <h1>Registration</h1>

<form id="wp_signup_form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

    <label for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="username">

    <label for="email">Email address</label>
    <input type="text" name="email" id="email" placeholder="email">

    <label>Skype</label>
    <input id="skype" type="text" placeholder="skype" value="" name="skype" >

    <label for="password">Password</label>
    <input type="password" name="password" id="password" placeholder="password">

    <label for="password_confirmation">Confirm Password</label>
    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="confitm password">

    <input name="terms" id="terms" type="checkbox" value="Yes">
    <label for="terms">I agree to the Terms of Service</label>

    <input type="submit" id="submitbtn" name="submit" value="Sign Up" />

</form>

