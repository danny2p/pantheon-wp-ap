<?php

print "Starting Quicksilver Script \n\n";

# If you want this to be selective, based on branch/multidev, you can do something like this
if ($_ENV['PANTHEON_ENVIRONMENT'] == "autopilot") {

  $pantheon_site = $_ENV['PANTHEON_SITE_NAME'];
  $pantheon_env = $_ENV['PANTHEON_ENVIRONMENT'];
  $pantheon_site_uuid = $_ENV['PANTHEON_SITE'];
  $platform_domain = "https://" . $pantheon_env . "." . $pantheon_site . ".pantheonsite.io";
  $site_dashboard = "https://dashboard.pantheon.io/sites/" . $pantheon_site_uuid . "#" . $pantheon_env;

  if ( function_exists('pantheon_get_secret') && !empty($slack_webhook = pantheon_get_secret('slack-webhook')) ) {
    print "The secret 'slack-webhook' is set, attempting to notify slack.";
    // Prepare the slack payload as per:
    // https://api.slack.com/incoming-webhooks
    $text = "------------- :lightningbolt-vfx: " . ucwords($pantheon_env) . " Deployment :lightningbolt-vfx: ------------- \n";
    $text .= "\nHey QA Team, Autopilot just ran for the site: $pantheon_site \n\n"; 
    $text .= "Site Link: $platform_domain \n\n";
    $text .= "Dashboard: $site_dashboard \n\n";

    $payload = json_encode([
        'text' => $text
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $slack_webhook);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Watch for messages with `terminus workflows watch --site=SITENAME`
    print("\n==== Posting to Slack ====\n");
    $result = curl_exec($ch);
    print("RESULT: $result");
    // $payload_pretty = json_encode($post,JSON_PRETTY_PRINT); // Uncomment to debug JSON
    // print("JSON: $payload_pretty"); // Uncomment to Debug JSON
    print("\n===== Post Complete! =====\n");
    curl_close($ch);
  } else {
    print "The secret 'slack-webhook' is not set or pantheon_get_secret() is unavailable.";
    return;
  }
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
