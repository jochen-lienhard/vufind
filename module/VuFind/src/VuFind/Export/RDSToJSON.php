<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToJSON extends RDSToFormat {

	public function __construct($driver) {
		parent::__construct($driver);
	}

	public function getRecord() {
		$raw = $this->dataProvider->getRAW();
		return json_encode($raw);
	}
	
}
?>