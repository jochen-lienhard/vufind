<?php

namespace VuFind\Export\DataProvider;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSDataProviderProxy implements RDSDataProvider {

	protected $fields;
	protected $recordDriver;

	protected $mediatypeByOpenurlGenre = array();

	public function __construct($indexFields, $recordDriver) {
		$this->fields = $indexFields;
		$this->recordDriver = $recordDriver;

		$this->mediatypeByOpenurlGenre = array(
				RDSDataProvider::MEDIATYPE_ARTICLE 	=> array('article'),
				RDSDataProvider::MEDIATYPE_BOOK 	=> array('book'),
				RDSDataProvider::MEDIATYPE_JOURNAL 	=> array('journal')
		);

	}

	public function getMediatype()
	{
		$mediatypes = array();

		$currentOpenurlgenres = $this->getField('openurlgenre');
		foreach ($this->mediatypeByOpenurlGenre as $mediatype => $openurlgenres) {
			if (count(array_intersect($openurlgenres, $currentOpenurlgenres)) > 0) {
				$mediatypes[] = $mediatype;
			}
		}

		$resultMediatype = 0;
		foreach ($mediatypes as $mediatype) {
			$resultMediatype += $mediatype;
		}
		
		if ($resultMediatype == 0) {
			$resultMediatype = RDSDataProvider::MEDIATYPE_UNKNOWN;
		}
		
		return $resultMediatype;
	}

	public function getID() {
		return isset($this->fields['id']) ? $this->fields['id'] : '';
	}

	public function getTitle($type = RDSDataProvider::TITLE_FULL) {
		return $this->getField('title');
	}

	# code duplication form RDSProxyRecord !
	public function getAuthor($type = RDSDataProvider::AUTHORS_SHORT) {
		$result = array();
		if(isset($this->fields['authors'])) {
			$result = $this->fields['authors'];
			for($i = 0; $i < count($result); $i++) {
				$result[$i] = preg_replace('|<sup>[^<]*</sup>|u', '', $result[$i]);
			}
		}

		return $result;
	}

	# code duplication form RDSProxyRecord !
	public function getPublishingYear() {
		return $this->getField('source', 'dates','published','year');

		if(isset($this->fields['dates'])) {
    		$dates = $this->fields['dates'];
    	} elseif(isset($this->fields['source']) && isset($this->fields['source']['dates'])) {
    		$dates = $this->fields['source']['dates'];
    	} else {
    		return '';
    	}

    	if(isset($dates['published'])) {
    		return $dates['published']['year'];
    	} else {
    		return '';
    	}
	}

	public function getLanguages() {
		return $this->getField('languages');
	}

	# code duplication form RDSProxyRecord !
	# !modification: getISBN() -> getISBNs()
	public function getISBNs(){
		$isbns = array();
    	if(isset($this->fields['pisbn'])) {
    		$isbns[] = new ISBN_('print', $this->fields['pisbn']);
    	} elseif(isset($this->fields['source']) && isset($this->fields['source']['pisbn'])) {
    		$isbns[] = new ISBN_('print', $this->fields['source']['pisbn']);
    	}
        if(isset($this->fields['eisbn'])) {
        	$isbns[] = new ISBN_('electronic', $this->fields['eisbn']);
    	} elseif(isset($this->fields['source']) && isset($this->fields['source']['eisbn'])) {
    		$isbns[] = new ISBN_('electronic', $this->fields['source']['eisbn']);
    	}

    	return $isbns;
	}

	# code duplication form RDSProxyRecord !
	# ! modification: getISSN() -> getISSNs()
	public function getISSNs() {
		$issns = array();
		if(isset($this->fields['pissn'])) {
			$issns[] = new ISSN('print', $this->fields['pissn']);
		} elseif(isset($this->fields['source']) && isset($this->fields['source']['pissn'])) {
			$issns[] = new ISSN('print', $this->fields['source']['pissn']);
		}
		if(isset($this->fields['eissn'])) {
			$issns[] = new ISSN('electronic', $this->fields['eissn']);
		} elseif(isset($this->fields['source']) && isset($this->fields['source']['eissn'])) {
			$issns[] = new ISSN('electronic', $this->fields['source']['eisbn']);
		}

		return $issns;
	}

	public function getPublishingPlace() {
		return array('');
	}

	public function getPublisher() {
	   return array();
	}

	public function getPages() {
		$startPages = $this->getField('startpage');

		$totalPages = $this->getField('numpages');
		if (empty($totalPages)) {
		    $totalPages = $this->getField('source','numpages');
		} 
		
		$endPage = null;
		if (!empty($startPages) && !empty($totalPages)) {
			$endPage = $startPages[0] + $totalPages[0] - 1;
		}

		return array(new Page('', $totalPages[0], $startPages[0], $endPage));
	}

	public function getFootnotes($type) {
	    return array();
	}

	public function getEdition() {
	    return array();
	}

	# code duplication form RDSProxyRecord !
	public function getVolume() {
		$volumes = $this->getField('volume');

		if (empty($volume)) {
		    $volumes = $this->getField('source','volume');
		}

		return $volumes;
	}

	public function getIssue() {
		$issues = $this->getField('issue');

		if (empty($issues)) {
			$issues = $this->getField('source','issue');
		}

		return $issues;
	}

	# code duplication form RDSProxyRecord !
	# ! modified
	public function getAbstract() {
		return $this->getField('abstracts','main');
	}

	public function getSchool() {
		return '';
	}

	# code duplication form RDSProxyRecord !
	public function getKeywords() {
		return $this->getSubjects('general');
	}


	public function getPersistentLink() {
		return null;
	}

	# code duplication form RDSProxyRecord !
	public function getDOI(){
		return $this->getField('doi');
	}

	# code duplication form RDSProxyRecord !
	# ! modified !
	public function getDataSource() {
		return $this->getField('datasource');
	}

	public function getJournal() {
		if ($this->hasMediaType(RDSDataProvider::MEDIATYPE_ARTICLE) ||
			$this->hasMediaType(RDSDataProvider::MEDIATYPE_JOURNAL)) {
			$journals = $this->getField('source','title');
			
			if (empty($journals)) {
				$journals = $this->getField('source','display');
			}
			
			// TODO: use appropriate modifier instead of removing the tags
			return str_replace('&amp;', '&', strip_tags($journals[0]));
		} else {
		    return array();
		}
	}

	public function getSeries() {
		$series = $this->getField('series','title');

		$seriesArr = array();
		foreach ($series as $serie) {
		    $seriesArr[] = array('title' => $serie);
		}

		return $seriesArr;
	}

	public function getUebergeordneteWerke() {
	    return array();
	}

	# code duplication form RDSProxyRecord !
	protected function getSubjects($category)
	{
		if(isset($this->fields['subjects']) && isset($this->fields['subjects'][$category])) {
			return $this->fields['subjects'][$category];
		} else {
			return array();
		}
	}

	public function getMARC() {
		return '';
	}

	public function getFulltextLinks() {
		return array();
	}
	
	/**
	 * always return an ARRAY of field values
	 */

	protected function getField() {
		return $this->getFieldRecursive($this->fields, func_get_args());
	}

	protected function getFieldRecursive() {
		$arrayOfFieldValues = null;

		if (func_num_args() < 1) {
		    return array();
		}

		$args = func_get_args();

		$array = $args[0];
		$fieldNames = $args[1];
		$fieldName = array_shift($fieldNames);

	    $fieldValue = $array[$fieldName];
		if (count($fieldNames) <= 0) {
		    return $this->getArrayOf($fieldValue);
		} else {
		    if (is_array($fieldValue)) {
		        return $this->getFieldRecursive($fieldValue, $fieldNames);
		    } else {
		        return array();
		    }
		}
	}

    protected function getArrayOf($values) {
    	$array = array();

    	if (isset($values)) {
    		if (is_array($values)) {
    			$array = $values;
    		} else {
    			$array = array($values);
    		}
    	}

    	return $array;
    }

    protected function hasMediatype($mediatype) {
    	$currentMediatypes = $this->getMediatype();
    	return (($currentMediatypes & $mediatype) > 0) ;
    }

	############################################################################
	#  Debug
	############################################################################

	public function getFields() {
		$debug_out = "";

		$field_list = Array('id', 'dbid', 'openurlgenre', 'links', 'authors', 'series', 'source');
		$field_list = array_merge($field_list, array('volume'));


		#$field_list = Array('', '');


		foreach ($field_list as $field) {
			if (is_array($this->fields[$field])) {
				$debug_out .= "$field = \n    " . implode("\n    ", $this->fields[$field]) . "\n";
			} else {
				$debug_out .= "$field = \n    " . $this->fields[$field] . "\n";
			}

		}

		global $interface;
		$interface->assign('index', $debug_out);

		return $debug_out;
	}

}

?>