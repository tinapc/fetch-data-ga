<?php
require_once 'vendor/autoload.php';
// Start a session to persist credentials.
session_start();

// Create the client object and set the authorization configuration
// from the client_secrets.json you downloaded from the Developers Console.
$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->setRedirectUri('http://localhost/fetch-data-ga/oauth2callback.php');
//$client->setAccessType('offline');
//$client->setApprovalPrompt('force');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// Handle authorization flow from the server.
if (! isset($_GET['code'])) {
	$auth_url = $client->createAuthUrl();
	header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	$redirect_uri = 'http://localhost/fetch-data-ga/index.php';
	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}