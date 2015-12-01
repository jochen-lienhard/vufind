<?php

namespace VuFind\Export;
use VuFind\Export\DataProvider\RDSDataProviderProxy;
use VuFind\Export\DataProvider\RDSDataProviderIndex;

abstract class RDSToFormat {

	protected $dataProvider;
	protected $driver;

	public function __construct($driver) {
	    $sourceIdentifier = $driver->getSourceIdentifier();
	    
	    switch ($sourceIdentifier) {
	        case 'RDSIndex':
	            $this->dataProvider = new RDSDataProviderIndex($driver->getRawData(), $driver);
	            break;
	        case 'RDSProxy':
	            $this->dataProvider = new RDSDataProviderProxy($driver->getRawData(), $driver);
	            break;
	    }
      $this->driver = $driver;
	}

	public function getDataProvider() {
	    return $this->dataProvider;
	}

	public function setDataProvider($dataProvider) {
	    $this->dataProvider = $dataProvider;
	}
	
	abstract public function getRecord();
}

?>