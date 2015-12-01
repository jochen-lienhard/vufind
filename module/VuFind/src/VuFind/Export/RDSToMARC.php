<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToMARC extends RDSToFormat {

    protected $marcFields = array();

	public function __construct($driver) {
		parent::__construct($driver);
	}

	public function getFormattedRecord() {

		$marc = $this->dataProvider->getMARC();

		if (empty($marc)) {
			$marc = $this->buildMARCRecord();
		}

		return $marc;
	}

	protected function buildMARCRecord() {
		return "TODO: implement buildMARCRecord()";
	}
}
?>