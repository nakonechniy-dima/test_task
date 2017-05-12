<?php
/*
Template Name: Login
*/

if($_POST) {

    global $wpdb;

    //We shall SQL escape all inputs
    $username = $wpdb->escape($_REQUEST['username']);
    $password = $wpdb->escape($_REQUEST['password']);


    if($remember) $remember = "true";
    else $remember = "false";

    $login_data = array();
    $login_data['user_login'] = $username;
    $login_data['user_password'] = $password;


    $user_verify = wp_signon( $login_data, false );

    if ( is_wp_error($user_verify) )
    {
        header("Location: " . home_url() . "/login/error/");
        // Note, I have created a page called "Error" that is a child of the login page to handle errors. This can be anything, but it seemed a good way to me to handle errors.
    } else {
        echo "<script type='text/javascript'>window.location='". home_url() . '/movie' . "'</script>";
        exit();
    }

} else {

    // No login details entered - you should probably add some more user feedback here, but this does the bare minimum

   // echo "Invalid login details";

} ?>
<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
<link href='<?php echo get_stylesheet_directory_uri();?>/form-style.css' rel='stylesheet' type='text/css'>

<div class="login-block">
    <h1>Login</h1>

<form id="login" name="form" action="<?php echo home_url(); ?>/login/" method="post">
        <input id="username" type="text" placeholder="Username" name="username">
        <input id="password" type="password" placeholder="Password" name="password">
        <input id="submit" type="submit" name="submit" value="Submit">
</form>




