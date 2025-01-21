<?php

function slow_call() {
  // Sleep for 10 seconds
  sleep(10);

  // Return "hello world"
  return "hello world";
}

// Call the slow_call function and output the result
echo slow_call();

?>