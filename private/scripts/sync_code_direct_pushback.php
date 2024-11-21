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


$git_token = pantheon_get_secret('git-token');
if (empty($git_token)) {
  print "The secret 'git-token' is not set.";
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

$github_remote="https://danny2p:$git_token@github.com/danny2p/dp-d91.git";
exec("git pull $github_remote");
exec("git push --set-upstream $github_remote");
print "\n Pushed to remote repository.";
