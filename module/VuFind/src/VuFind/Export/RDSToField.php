<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToField extends RDSToFormat {

	public function __construct($driver) {
		parent::__construct($driver);
	}

	public function getFormattedRecord() {
		return $this->dataProvider->getFields();
	}
}
?>