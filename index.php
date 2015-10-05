<?php
require_once 'vendor/autoload.php';
session_start();

require_once 'ga_data.php';

// If the user has already authorized this app then get an access token
// else redirect to ask the user to authorize access to Google Analytics.
if ( !isset($_SESSION['access_token']) && !$_SESSION['access_token']) {
    
    $redirect_uri = 'fetch-data-ga/oauth2callback.php';
    header('Location: ' . $redirect_uri);
} 

// Create the client object and set the authorization configuration
// from the client_secretes.json you downloaded from the developer console.
$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// Set the access token on the client.
$client->setAccessToken($_SESSION['access_token']);

// Create an authorized analytics service object.
$analytics = new Google_Service_Analytics($client);

$ga_data = new ga_data($analytics);

$account = $ga_data->getAllAccounts();
$properties = $ga_data->getPropertiesOfAccount($account['id']);
$showTable = false;

if (isset($_POST) && count($_POST)) {
    $metric = 'ga:sessions,ga:users,ga:bounceRate,ga:pageviews';
    $startDay = '2014-05-01';
    $endDay = 'today';
    $showTable = true;

    $fetchData = $ga_data->fetchDataFromProperty($_POST['profileId'], $startDay, $endDay, $metric);

}   



// function getAllAccount(&$analytics)
// {
// 	// Get the list of accounts for the authorized user.
//     $accounts = $analytics->management_accounts->listManagementAccounts();

//     foreach ($accounts->getItems() as $account) {
//     	echo "<p>Name: ".$account->getName()." - ID: ".$account->getId()." </p>";
//     	getPropertiesOfAccount($analytics, $account->getId());
//     }	
// }

// function getPropertiesOfAccount($analytics, $accountID)
// {
// 	// Get the list of properties for the authorized user.
//     $properties = $analytics->management_webproperties->listManagementWebproperties($accountID);
//     if (count($properties->getItems()) > 0) {	
//   //   	echo "<pre>";
// 		// print_r($properties->getItems());
// 		// echo "</pre>";
//     	foreach ($properties->getItems() as $proper) {
//     		$profile = $analytics->management_profiles->listManagementProfiles($accountID, $proper->getId());
//     		echo "<p style='padding-left:20px; font-size:14px'>Name: ".$proper->getName()." - ID: ".$proper->getId()." </p>";
    		
// 			foreach ($profile->getItems() as $item) {
// 				echo $item->getId() .'<br>';
// 			}
//     		fetchDataFromProperty($analytics,  $item->getId());
//     	}
//     }
// }


// function getFirstprofileId(&$analytics) {
    
//     // Get the user's first view (profile) ID.
    
//     // Get the list of accounts for the authorized user.
//     $accounts = $analytics->management_accounts->listManagementAccounts();
    
//     if (count($accounts->getItems()) > 0) {
//         $items = $accounts->getItems();
//         $firstAccountId = $items[0]->getId();
        
//         // Get the list of properties for the authorized user.
//         $properties = $analytics->management_webproperties->listManagementWebproperties($firstAccountId);
        
//         if (count($properties->getItems()) > 0) {
//             $items = $properties->getItems();
//             $firstPropertyId = $items[0]->getId();
            
//             // Get the list of views (profiles) for the authorized user.
//             $profiles = $analytics->management_profiles->listManagementProfiles($firstAccountId, $firstPropertyId);
            
//             if (count($profiles->getItems()) > 0) {
//                 $items = $profiles->getItems();
                
//                 // Return the first view (profile) ID.
//                 return $items[0]->getId();
//             } 
//             else {
//                 throw new Exception('No views (profiles) found for this user.');
//             }
//         } 
//         else {
//             throw new Exception('No properties found for this user.');
//         }
//     } 
//     else {
//         throw new Exception('No accounts found for this user.');
//     }
// }

// function fetchDataFromProperty(&$analytics, $properID)
// {
// 	$metric = 'ga:sessions,ga:bounces, ga:pageviews';
// 	$results =  $analytics->data_ga->get('ga:'.$properID, '2014-05-01', 'today', $metric);	

// 	if (count($results->getRows()) > 0) {
// 		echo "<pre>";
// 		print_r($results->getRows());
// 		echo "</pre>";
// 	}

// }

// function getResults(&$analytics, $profileId) {
    
//     // Calls the Core Reporting API and queries for the number of sessions
//     // for the last seven days.
//     return $analytics->data_ga->get('ga:' . $profileId, '7daysAgo', 'today', 'ga:sessions,ga:bounces, ga:pageviews');
// }

// function printResults(&$results) {
    
//     // Parses the response from the Core Reporting API and prints
//     // the profile name and total sessions.
//     if (count($results->getRows()) > 0) {
        
//         // Get the profile name.
//         $profileName = $results->getProfileInfo()->getProfileName();
        
//         // Get the entry for the first entry in the first row.
//         $rows = $results->getRows();
//         $sessions = $rows[0][0];
//         //$sessions = $rows[0][1];
        
//         // Print the results.
//         print "<p>First view (profile) found: $profileName</p>";
//         //print "<p>Total sessions: $sessions</p>";
//         print_r($rows);
//     } 
//     else {
//         print "<p>No results found.</p>";
//     }
// }

?>

<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Google Analytic API</title>

        <!-- Bootstrap CSS -->
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="container" style="padding:50px 0">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    
                    <form action="" method="POST" class="form-horizontal" role="form">
                        <div class="form-group">
                            <legend>Google Analytic API</legend>
                        </div>
                        <div class="form-group">
                            <label>Account (ID: <?php echo $account['id'] ?>)</label>
                            <input class="form-control" value="<?=$account['name']?>" />
                        </div>
                        <div class="form-group">
                            <label>Property</label>
                            <select class="form-control" name="profileId">
                                <?php if (count($properties)) : ?>
                                    <?php foreach ($properties as $property) : ?>
                                        <option value="<?php echo $property['profileId']?>"><?php echo $property['propertyName']?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        
                
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" name="query" class="btn btn-primary">Query</button>
                            </div>
                        </div>

                    
                        <div class="form-group">
                            <legend>Result of Query</legend>
                        </div>

                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Bounces</th>
                                    <th>Bounce Rate</th>
                                    <th>Page View</th>
                                </tr>
                            </thead>
                            <?php if ($showTable === true) : ?>
                            <tbody>
                                <tr>
                                    <td><?php echo $fetchData[0]?></td>
                                    <td><?php echo $fetchData[1]?></td>
                                    <td><?php echo round($fetchData[2],2)?>%</td>
                                    <td><?php echo $fetchData[3]?></td>
                                </tr>
                            </tbody>
                            <?php endif ?>
                        </table>

                    </form>

                </div>
            </div>
        </div>
        

        <!-- jQuery -->
        <script src="//code.jquery.com/jquery.js"></script>
        <!-- Bootstrap JavaScript -->
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    </body>
</html>