<?php

// Set headers to prevent caching
header("Expires: Thu, 1 Jan 1970 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function slow_call() {
  // Sleep for 10 seconds
  sleep(10);

  // Return "hello world"
  return "hello world";
}

// Call the slow_call function and output the result
echo slow_call();

?>