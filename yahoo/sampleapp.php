<?php

// Include the YOS library.
require dirname(__FILE__).'/Yahoo.php';

// debug settings
error_reporting(E_ALL); # do not show notices as library is php4 compatable
ini_set('display_errors', true);
YahooLogger::setDebug(true);
YahooLogger::setDebugDestination('CONSOLE');

// use memcache to store oauth credentials via php native sessions
//ini_set('session.save_handler', 'files');
//session_save_path('/tmp/');
session_start();

// Make sure you obtain application keys before continuing by visiting:
// https://developer.yahoo.com/dashboard/createKey.html
define('OAUTH_CONSUMER_KEY', 'dj0yJmk9eFZZdVRuS3JOc2lBJmQ9WVdrOVVqQnFOMDF1TjJrbWNHbzlNVE13TXpNM01EVTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD0wOA--');
define('OAUTH_CONSUMER_SECRET', '09a3d4ee3580d6a56bd65d19df51df1d8dd27236');
define('OAUTH_DOMAIN', 'tiddu.com');
define('OAUTH_APP_ID', 'R0j7Mn7i');

if(array_key_exists("logout", $_GET)) {
  // if a session exists and the logout flag is detected
  // clear the session tokens and reload the page.
  YahooSession::clearSession();
  header("Location: sampleapp.php");
}

// check for the existance of a session.
// this will determine if we need to show a pop-up and fetch the auth url,
// or fetch the user's social data.
$hasSession = YahooSession::hasSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);

if($hasSession == FALSE) {
  // create the callback url,
  $callback = YahooUtil::current_url();

  // pass the credentials to get an auth url.
  // this URL will be used for the pop-up.
  $auth_url = YahooSession::createAuthorizationUrl(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, $callback);
}
else {
  // pass the credentials to initiate a session
  $session = YahooSession::requireSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);

  
  // if a session is initialized, fetch the user's profile information
  if($session) {
    // Get the currently sessioned user.
    $user = $session->getSessionedUser();

    // Load the profile for the current user.
    $profile = $user->getProfile();
  }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
  <head>
    <title>YOS Social Platform Sample Application</title>

   
  </head>
  <body>
    <?php
      if($hasSession == FALSE) {
        // if a session does not exist, output the
        // login / share button linked to the auth_url.
        echo sprintf("<a href=\"%s\" id=\"yloginLink\"><img src=\"http://l.yimg.com/a/i/ydn/social/updt-spurp.png\"></a>\n", $auth_url);
      }
      else if($hasSession && $profile) {
        // if a session does exist and the profile data was
        // fetched without error, print out a simple usercard.
        echo sprintf("<img src=\"%s\"/><p><h2>Hi <a href=\"%s\" target=\"_blank\">%s!</a></h2></p>\n", $profile->image->imageUrl, $profile->profileUrl, $profile->nickname);

        if($profile->status->message != "") {
          $statusDate = date('F j, y, g:i a', strtotime($profile->status->lastStatusModified));
          echo sprintf("<p><strong>&#8220;</strong>%s<strong>&#8221;</strong> on %s</p>", $profile->status->message, $statusDate);
        }

        echo "<p><a href=\"?logout\">Logout</a></p>";
      }
    ?>
  </body>
</html>