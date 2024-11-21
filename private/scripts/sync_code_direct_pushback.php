<?php

print "Starting Quicksilver Script \n\n";

# If you only wanted this to execute on Dev (master):
if ($_ENV['PANTHEON_ENVIRONMENT'] == "autopilot") {
  print "Autopilot branch, quicksilver";
  print "ENV: <pre>";
  print_r($_ENV);
  print "</pre>";
  print "POST: <pre>";
  print_r($_POST);
  print "</pre>";
  return;
}

if ( function_exists('pantheon_get_secret') && !empty($gh_token = pantheon_get_secret('github-token')) ) {
  print "The secret 'github-token' is set, attempting to push.";
} else {
  print "The secret 'github-token' is not set or pantheon_get_secret() is unavailable.";
  return;
}

/*
*
* Since Pantheon is really authoritative, in the sense that 
* Pantheon is running the code, we'll try to automatically
* push back to Github master. In most cases this should be safe
* if commits to Github master branch are always being pushed to Pantheon.
* extend this logic as necessary to fit your needs.
*
*/

if ($_ENV['PANTHEON_ENVIRONMENT'] == "dev") {
  $git_branch = "master";
} else { #multidev case
  $git_branch = $_ENV['PANTHEON_ENVIRONMENT'];
}

$github_remote="https://danny2p:$gh_token@github.com/danny2p/pantheon-wp-ap.git";
print "github_remote";
exec("git pull $github_remote");
exec("git push --set-upstream $github_remote $git_branch");
print "\n Pushed to remote repository.";
