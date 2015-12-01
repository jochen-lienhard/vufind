<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToRIS extends RDSToFormat {

	 protected $referenceTypeByMediatype = array();

	 protected $risKeys = array(
	 		'TY',   	// ReferenceType
			'AB',		// Abstract
     		'AU',		// Authors
			'CY',     	// PublisherPlaces
			'C1',		// PublisherPlaces for type: CPAPER
			'DO',		// DOI
			'DP',		// Database Provider
			'EP',       // End Page
			'ET',    	// Editions
			'PB', 		// Publishers
			'PY', 		// PublishingYear
			'KW',		// Keywords
			'LA',		// Languages
			'L2',		// FullText
			'N1',		// Note
			'SN',		// ISBNs/ISSNs
			'SP',		// Start Page / Pages
    		'ST',	    // Short Title
    		'TI',		// Title
			'T2', 		// Series for THES
	 		'T3', 		// Series for MUSIC
	 		'UR',		// URL
			'VL'		// Volume
    );

	public function __construct($driver) {
		parent::__construct($driver);

		$this->referenceTypeByMediatype = array(
				'BOOK'      => RDSDataProvider::MEDIATYPE_BOOK,
				'JOUR'      => RDSDataProvider::MEDIATYPE_ARTICLE,
				'EBOOK'    	=> RDSDataProvider::MEDIATYPE_EBOOK |
							   RDSDataProvider::MEDIATYPE_BINARY,
				'ADVS' 		=> RDSDataProvider::MEDIATYPE_AUDIO,
				'MAP'       => RDSDataProvider::MEDIATYPE_MAP,
				'MUSIC'    	=> RDSDataProvider::MEDIATYPE_MUDRUCK,
				'VIDEO'   	=> RDSDataProvider::MEDIATYPE_VIDEO,
				'JFULL'     => RDSDataProvider::MEDIATYPE_JOURNAL,
				'THES'		=> RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED,
				'CPAPER'  	=> RDSDataProvider::MEDIATYPE_PROCEEDING,
		);
	}

	public function getRecord() {

	    $ris = "";
	    foreach ($this->risKeys as $risKey) {
	        $getEntriesForKey = 'getEntries_' . $risKey;

	        $risEntries = $this->$getEntriesForKey();
	        if (!empty($risEntries)) {
		        $ris .= $risEntries . "\n";
	        }
	    }
	    $ris .= 'ER  -';

		return $ris;
	}

	protected function getReferenceType() {
		$currentMediatypes = $this->dataProvider->getMediatype();

		foreach ($this->referenceTypeByMediatype as $referenceType => $mediatypes) {
			if (($currentMediatypes & $mediatypes) > 0) {
				return $referenceType;
			}
		}

		return 'GEN';
	}

	protected function getEntries_TY() {
	    return $this->getTag('TY', $this->getReferenceType());
	}

	protected function getEntries_TI() {
		$titles = $this->dataProvider->getTitle(RDSDataProvider::TITLE_HT);
	    return $this->getTag('TI', implode(', ', $titles));
	}

	protected function getEntries_ST() {
		$titles = $this->dataProvider->getTitle(RDSDataProvider::TITLE_SHORT);
		return $this->getTag('ST', implode(', ', $titles));
	}

	protected function getEntries_AU() {
		$authors = $this->dataProvider->getAuthor();
		return $this->getTag('AU', $authors, -1);
	}

	protected function getEntries_CY() {
		if($this->getReferenceType() == 'CPAPER' ){
		    return ''; // NOOP
		} else {
			return $this->getTag('CY', $this->dataProvider->getPublishingPlace(), 1);
		}
	}

	protected function getEntries_C1() {
		$publisherPlaces = array();
		if($this->getReferenceType() == 'CPAPER' ){
			$publisherPlaces = $this->dataProvider->getPublishingPlace();
		}

		return $this->getTag('C1', implode(", ", $publisherPlaces));
	}

	protected function getEntries_PB() {
		$entries_PB = array();

   		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED)) {
			return $this->getTag('PB', $this->dataProvider->getSchool());
		}

		return $this->getTag('PB', $this->dataProvider->getPublisher());
	}

	protected function getEntries_PY() {
		$publishingYears = $this->dataProvider->getPublishingYear();
	    return $this->getTag('PY', implode(', ', $publishingYears));
	}

	protected function getEntries_SP() {
		$ris_pages = array();
		
		foreach ($this->dataProvider->getPages() as $page) {
			$startPage = $page->getStartPage();
			$endPage = $page->getEndPage();
			if (!empty($startPage) && !empty($endPage)) {
				$ris_pages[] = $startPage;
				continue;
			}
		
			$totalPages = $page->getTotalPages();
			if (!empty($totalPages)) {
				$ris_pages[] = $totalPages;
				continue;
			}
				
			$ris_pages[] = $page->getUnstructuredText();
		}
		
		return $this->getTag('SP', implode(', ',$ris_pages));
	}

	protected function getEntries_EP() {
		$endPages = array();
		
		foreach ($this->dataProvider->getPages() as $page) {
			$endPage = $page->getEndPage();
			if (isset($endPage)) {
				$endPages[] = $endPage;
			}
		}

		return $this->getTag('EP', $endPage);
	}


	protected function getEntries_SN() {
		$entries_SN = "";

		$isbns = $this->dataProvider->getISBNs();
		$issns = $this->dataProvider->getISSNs();
		$isbn_issns = array_merge($isbns, $issns);

		$formattedISBN_ISSNS = array();
		foreach ($isbn_issns as $isbn_issn) {
			$tmp = $isbn_issn->getValue();
			$type =	$isbn_issn->getType();
			if (!empty($type)) {
				$tmp .= " (" . $type . ")";
			}
			$formattedISBN_ISSNS[] = $tmp;
		}

		return $this->getTag('SN', implode(", ", $formattedISBN_ISSNS));
	}

	protected function getEntries_ET() {
		$editions = $this->dataProvider->getEdition();
		return $this->getTag('ET', implode(', ', $editions));
	}

	protected function getEntries_LA() {
		$entry_LA = '';

		$languages = $this->dataProvider->getLanguages();
		foreach ($languages as $language) {
		    $entry_LA = 'LA  - ' . $language;
		}

		return $this->getTag('LA', implode(', ', $languages));
	}


	protected function getEntries_AB() {
		$abstracts = $this->dataProvider->getAbstract();
		return $this->getTag('AB', implode(', ', $abstracts));
	}

	protected function getEntries_N1() {
		$entry_N1 = '';

		$allFootnotes = '';
		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
			$allFootnotes .= implode(', ', $this->dataProvider->getSchool());
		}

		foreach ($this->dataProvider->getFootnotes(15) as $footnotes) {
			$allFootnotes .= implode(', ', $footnotes);
		}

		return $this->getTag('N1', $allFootnotes);
	}

	protected function getEntries_VL() {
		$volumes = $this->dataProvider->getVolume();
		return $this->getTag('VL', implode(',', $volumes));
	}

	public function getEntries_T2() {
		$series = $this->dataProvider->getUebergeordneteWerke();
		$series = (empty($series)) ? $this->dataProvider->getSeries() : $series;

		if (empty($series)) {
			$journalTitle = $this->dataProvider->getJournal();
			if (! empty($journalTitle)) {
				$series = array(array('title' => $journalTitle));
			}
		}
		
		$seriesTitles = array();
		foreach ($series as $serie) {
			if (!empty($serie['title'])) {
				$seriesTitles[] = $serie['title'];
			}
		}

		return $this->getTag('T2',  implode(', ', $seriesTitles));
	}

	public function getEntries_T3() {
			if (! $this->hasMediatype(RDSDataProvider::MEDIATYPE_MUDRUCK)) {
			return $this->getTag('T3', '');
		}

		$series = $this->dataProvider->getUebergeordneteWerke();
		$series = (empty($series)) ? $this->dataProvider->getSeries() : $series;

		$seriesTitles = array();
		foreach ($series as $serie) {
			if (!empty($serie['title'])) {
				$seriesTitles[] = $serie['title'];
			}
		}

		return $this->getTag('T3',  implode(', ', $seriesTitles));
	}

	protected function getEntries_KW() {
		return $this->getTag('KW', $this->dataProvider->getKeywords(), -1);
	}

	protected function getEntries_UR() {
		return $this->getTag('UR','');
	}

	protected function getEntries_DP() {
		return $this->getTag('DP', $this->dataProvider->getPersistentLink());
	}

	protected function getEntries_DO() {
		return $this->getTag('DO', $this->dataProvider->getDOI());
	}
	
	protected function getEntries_L2() {
		return $this->getTag('L2', $this->dataProvider->getFulltextLinks());
	}
	

	protected function hasMediatype($mediatype) {
		$currentMediatypes = $this->dataProvider->getMediatype();
		return (($currentMediatypes & $mediatype) > 0) ;
	}

	protected function getTag($tagName, $tagValues, $tagCountMax = 1) {
	    $tags = array();

 		if (!isset($tagValues) || empty($tagValues)) {
 			return '';
 			//return $tagName . '  - ';
 		}

		if (!is_array($tagValues)) {
		    $tagValues = array($tagValues);
		}

	    if ($tagCountMax != -1) {
		    $tagValues = array_slice($tagValues, 0, $tagCountMax);
	    }

	    foreach ($tagValues as $tagValue) {
	        $tags[] = $tagName . "  - " . $tagValue;
	    }

		return implode("\n", $tags);
	}

}

?>