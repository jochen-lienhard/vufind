<?php

namespace VuFind\Export\DataProvider;

interface RDSDataProvider {

	const MEDIATYPE_BOOK 			 			  =     1;
	const MEDIATYPE_ARTICLE 		 			  =     2;
	const MEDIATYPE_JOURNAL      	 			  =     4;
	const MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED  =     8; // university only
	const MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED 	  =    16; // university and publisher
	const MEDIATYPE_UEBERGEORDNETES_WERK		  =    32;
	const MEDIATYPE_SERIE						  =    64;
	const MEDIATYPE_BINARY						  =   128;
	const MEDIATYPE_EBOOK						  =   256;
	const MEDIATYPE_PROCEEDING					  =   512; // gkko
	const MEDIATYPE_AUDIO						  =  1024; //
	const MEDIATYPE_VIDEO 						  =  2048; //
	const MEDIATYPE_MAP							  =  4096; //
	const MEDIATYPE_MUDRUCK						  =  8192; //
	const MEDIATYPE_UNKNOWN						  = 16384;
                                                  
	const MEDIATYPE_ALL							  = 32767;

	const TITLE_SHORT    = 1;
	const TITLE_LONG     = 2;
	const TITLE_FULL  	 = 4;
	const TITEL_SUBTITLE = 8;
	const TITLE_SERIES   = 16;
	const TITLE_HT		 = 32;

	const AUTHORS_SHORT = 1;
	const AUTHORS_LONG  = 2;

	const FOOTNOTES_ALL = 15;
    const FOOTNOTES = 1;
    const FOOTNOTES_ENTHWERKE = 2;
    const FOOTNOTES_EBOOKS = 4;
    const FOOTNOTES_INTERPRET = 8;

	public function getMediatype();

	public function getID();

	public function getFields();

	# Methods to retrieve bibliographic data

	public function getTitle($type);

	public function getAuthor($type);

	public function getPublisher();

	public function getPublishingPlace();

	public function getPublishingYear();

	public function getLanguages();

	public function getISBNs();

	public function getISSNs();

	public function getPages();
	
	public function getEdition();

	public function getAbstract();


	/**
	 * @return array of strings
	 */
	public function getKeywords();

	public function getSchool();

	public function getFootnotes($type);

	public function getPersistentLink();

	public function getFulltextLinks();

	/**
	 * @return array(array('id'=> seriesId, 'title'=> seriesTitle, 'volume'=> seriesVolume), array(...), array(...), ...);
	 */
	public function getSeries();

	public function getUebergeordneteWerke();

	public function getJournal();

	public function getIssue();
	public function getVolume();



	public function getDOI();

	public function getDataSource();

	public function getMARC();

}


class Journal {
    protected $title;
    protected $issn;
    protected $issue;
    protected $publishingYear;
    protected $publishingMonth;
    protected $publishingDay;

    protected $startPage;
    protected $numPages;

	public function getTitle() {return $this->title;}
	public function setTitle($title) {$this->title = $title;}

	public function getISSN() {return $this->issn;}
	public function setISSN($issn) {return $this->issn;}

	public function getIssue() {return $this->issue;}
	public function setIssue($issue) {return $this->issue;}

	public function getStartPage() {return $this->startPage;}
	public function setStartPage($startPage) {return $this->startPage;}

	public function getNumPages() {return $this->numPages;}
	public function setNumPages($numPages) {return $this->numPages;}

	public function getPublishingYear() {return $this->publishingYear;}
	public function setPublishingYear($publishingYear) {return $this->publishingYear;}
}

class ISBN_ {
    protected $type;
    protected $value;

	public function __construct($type, $value) {
	    $this->type = $type;
	    $this->value = $value;
	}

	public function getType() {
		return $this->type;
	}

	public function getValue() {
		return $this->value;
	}
}

class ISSN {
    protected $type;
    protected $value;

	public function __construct($type, $value) {
	    $this->type = $type;
	    $this->value = $value;
	}

	public function getType() {
		return $this->type;
	}

	public function getValue() {
		return $this->value;
	}
}

class Page {
	protected $unstructuredText;
	protected $startPage;
	protected $endPage;
	protected $totalPages;
	
	public function __construct($unstructuredText = '', $totalPages = null, $startPage = null, $endPage = null) {
		$this->unstructuredText = $unstructuredText;
		$this->startPage = $startPage;
		$this->endPage = $endPage;
		$this->totalPages = $totalPages;
	}

	public function getStartPage() {
		return $this->startPage;
	}
	
	public function getEndPage() {
		return $this->endPage;
	}
	
	public function getTotalPages() {
		return $this->totalPages;
	}
	
	public function getUnstructuredText() {
		return $this->unstructuredText;
	}
}
?>