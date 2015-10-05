<?php

class ga_data
{
	protected $analytics;

	public function __construct($analytics)
	{
		$this->analytics = $analytics;
	}

	public function getAllAccounts()
	{
		$accounts = $this->analytics->management_accounts->listManagementAccounts();

		if ( count($accounts->getItems()) )
		{
			$accs = ['name' => $accounts->getItems()[0]->getName(), 'id' => $accounts->getItems()[0]->getId()];
		}

		return $accs;
	}

	public function getPropertiesOfAccount($accountID)
	{
		// Get the list of properties for the authorized user.
	    $properties = $this->analytics->management_webproperties->listManagementWebproperties($accountID);

	    if (count($properties->getItems()) > 0) {	
	    	foreach ($properties->getItems() as $proper) {
	    		$profile = $this->analytics->management_profiles->listManagementProfiles($accountID, $proper->getId());

	    		$profile_item = $profile->getItems();

	    		$data[] = [
	    			'propertyName'	=> $proper->getName(),
	    			'profileId'		=> $profile_item[0]->getId()
	    		];
	    	}

	    	return $data;
	    } else {
	    	return NULL;
	    }
	}

	public function fetchDataFromProperty($profileId, $startDay, $endDay = 'today', $metric)
	{
		$results =  $this->analytics->data_ga->get('ga:'.$profileId, $startDay, $endDay, $metric);	

		if (count($results->getRows()) > 0) {
			return $results->getRows()[0];
		}

	}
}