<?php
require_once 'vendor/autoload.php';
session_start();

// Create the client object and set the authorization configuration
// from the client_secretes.json you downloaded from the developer console.
$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// If the user has already authorized this app then get an access token
// else redirect to ask the user to authorize access to Google Analytics.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    
    // Set the access token on the client.
    $client->setAccessToken($_SESSION['access_token']);
    
    // Create an authorized analytics service object.
    $analytics = new Google_Service_Analytics($client);
    
    // Get the first view (profile) id for the authorized user.
    $profile = getFirstProfileId($analytics);
    
    getAllAccount($analytics);

    // Get the results from the Core Reporting API and print the results.
    $results = getResults($analytics, $profile);
    //printResults($results);
} 
else {
    $redirect_uri = 'http://localhost/fetch-data-ga/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}


function getAllAccount(&$analytics)
{
	// Get the list of accounts for the authorized user.
    $accounts = $analytics->management_accounts->listManagementAccounts();

    foreach ($accounts->getItems() as $account) {
    	echo "<p>Name: ".$account->getName()." - ID: ".$account->getId()." </p>";
    	getPropertiesOfAccount($analytics, $account->getId());
    }	
}

function getPropertiesOfAccount($analytics, $accountID)
{
	// Get the list of properties for the authorized user.
    $properties = $analytics->management_webproperties->listManagementWebproperties($accountID);
    if (count($properties->getItems()) > 0) {	
  //   	echo "<pre>";
		// print_r($properties->getItems());
		// echo "</pre>";
    	foreach ($properties->getItems() as $proper) {
    		$profile = $analytics->management_profiles->listManagementProfiles($accountID, $proper->getId());
    		echo "<p style='padding-left:20px; font-size:14px'>Name: ".$proper->getName()." - ID: ".$proper->getId()." </p>";
    		
			foreach ($profile->getItems() as $item) {
				echo $item->getId() .'<br>';
			}
    		fetchDataFromProperty($analytics,  $item->getId());
    	}
    }
}


function getFirstprofileId(&$analytics) {
    
    // Get the user's first view (profile) ID.
    
    // Get the list of accounts for the authorized user.
    $accounts = $analytics->management_accounts->listManagementAccounts();
    
    if (count($accounts->getItems()) > 0) {
        $items = $accounts->getItems();
        $firstAccountId = $items[0]->getId();
        
        // Get the list of properties for the authorized user.
        $properties = $analytics->management_webproperties->listManagementWebproperties($firstAccountId);
        
        if (count($properties->getItems()) > 0) {
            $items = $properties->getItems();
            $firstPropertyId = $items[0]->getId();
            
            // Get the list of views (profiles) for the authorized user.
            $profiles = $analytics->management_profiles->listManagementProfiles($firstAccountId, $firstPropertyId);
            
            if (count($profiles->getItems()) > 0) {
                $items = $profiles->getItems();
                
                // Return the first view (profile) ID.
                return $items[0]->getId();
            } 
            else {
                throw new Exception('No views (profiles) found for this user.');
            }
        } 
        else {
            throw new Exception('No properties found for this user.');
        }
    } 
    else {
        throw new Exception('No accounts found for this user.');
    }
}

function fetchDataFromProperty(&$analytics, $properID)
{
	$metric = 'ga:sessions,ga:bounces, ga:pageviews';
	$results =  $analytics->data_ga->get('ga:'.$properID, '2009-12-12', 'today', $metric);	

	if (count($results->getRows()) > 0) {
		echo "<pre>";
		print_r($results->getRows());
		echo "</pre>";
	}

}

function getResults(&$analytics, $profileId) {
    
    // Calls the Core Reporting API and queries for the number of sessions
    // for the last seven days.
    return $analytics->data_ga->get('ga:' . $profileId, '7daysAgo', 'today', 'ga:sessions,ga:bounces, ga:pageviews');
}

function printResults(&$results) {
    
    // Parses the response from the Core Reporting API and prints
    // the profile name and total sessions.
    if (count($results->getRows()) > 0) {
        
        // Get the profile name.
        $profileName = $results->getProfileInfo()->getProfileName();
        
        // Get the entry for the first entry in the first row.
        $rows = $results->getRows();
        $sessions = $rows[0][0];
        //$sessions = $rows[0][1];
        
        // Print the results.
        print "<p>First view (profile) found: $profileName</p>";
        //print "<p>Total sessions: $sessions</p>";
        print_r($rows);
    } 
    else {
        print "<p>No results found.</p>";
    }
}
