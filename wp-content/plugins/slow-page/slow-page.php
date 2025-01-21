<?php
/**
 * Plugin Name: Slow Page Test
 * Description: This plugin creates a page that loads slowly for testing purposes.
 * Version: 1.0
 * Author: Bard
 */

function slow_call() {
  // Sleep for 10 seconds
  sleep(10);

  // Return "hello world"
  return "hello world";
}

function slow_page_content() {
  echo slow_call();
}

function create_slow_page() {
  $page_id = get_page_by_path('slow-page');
  if (!$page_id) {
    $page = array(
      'post_title'    => 'Slow Page',
      'post_content'  => '[slow_page]',
      'post_status'   => 'publish',
      'post_type'     => 'page',
      'post_name'     => 'slow-page'
    );
    wp_insert_post($page);
  }
}
register_activation_hook(__FILE__, 'create_slow_page');

add_shortcode('slow_page', 'slow_page_content');
?>