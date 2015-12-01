<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToBibTeX extends RDSToFormat {

	protected $referenceTypeByMediatype = array();

	protected $bibTeXTags = array(
			'abstract',
			'address',
			'annote',
			'author',
			'doi',
			'edition',
			'isbn',
			'issn',
			'issue',
			'journal',
			'keywords',
			'language',
			'note',
			'pages',
			'publisher',
			'school',
			'series',
			'title',
			'url',
			'urldate',
			'volume',
			'year'
	);

	public function __construct($driver) {
	    parent::__construct($driver);

    	$this->referenceTypeByMediatype = array(
			'book'       => RDSDataProvider::MEDIATYPE_BOOK |
    						RDSDataProvider::MEDIATYPE_EBOOK |
    						RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED,
			'article'    => RDSDataProvider::MEDIATYPE_ARTICLE,
			'phdthesis'  => RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED,
			'conference' => RDSDataProvider::MEDIATYPE_PROCEEDING,
		);
	}

	public function getRecord() {
		$bibTeX = "";

		$bibTeX .= $this->getReferenceType() . "{" . $this->getCitationKey() . ", \n";

		foreach ($this->bibTeXTags as $bibTeXEntry) {
			$function_getBibTeXEntry = 'getTag_' . $bibTeXEntry;
			$entry = $this->$function_getBibTeXEntry();
			if (!empty($entry)) {
				$bibTeX .= '    ' . $bibTeXEntry . " = {" . $entry . "},\n";
			}
		}

		$bibTeX .= "}";

		return $bibTeX;
	}

	protected function getReferenceType() {
		$currentMediatypes  = $this->dataProvider->getMediatype();

		foreach ($this->referenceTypeByMediatype as $referenceType => $mediatypes) {
			if (($currentMediatypes & $mediatypes) > 0) {
				return '@' . $referenceType;
			}
		}

		return '@misc';
	}

	protected function getCitationKey() {
		$bibTeXId = "";

		$bibTeXId = $this->dataProvider->getID();

		return $bibTeXId;
	}

	protected function getTag_urldate() {
	 	return '';
	}

	protected function getTag_title() {
		$bibTeXTitle = "";

		$bibTeXTitle = $this->dataProvider->getTitle(RDSDataProvider::TITLE_HT);

		return implode(', ', $bibTeXTitle);
	}

	protected function getTag_author() {
		$bibTeXAuthors = "";

		$authorsShort = $this->dataProvider->getAuthor();
		if (is_array($authorsShort)) {
			$bibTeXAuthors = implode(' and ',$authorsShort);
		} else {
			$bibTeXAuthors = $authorsShort;
		}

		return implode(' and ',$authorsShort);
	}

	protected function getTag_year() {
		return $this->getTag('year', $this->dataProvider->getPublishingYear());
	}

	protected function getTag_isbn() {
		$formattedISBNs = array();

		$isbns = $this->dataProvider->getISBNs();
		foreach ($isbns as $isbn) {
			$tmp = $isbn->getValue();
			$type =	$isbn->getType();
			if (!empty($type)) {
		    	$tmp .= " (" . $type . ")";
			}
			$formattedISBNs[] = $tmp;
		}

		return implode(", ", $formattedISBNs);
	}

	protected function getTag_issn() {
		$formattedISSNs = array();

		$issns = $this->dataProvider->getISSNs();
		foreach ($issns as $issn) {
			$tmp = $issn->getValue();
			$type =	$issn->getType();
			if (!empty($type)) {
		    	$tmp .= " (" . $type . ")";
			}
			$formattedISSNs[] = $tmp;
		}

		return implode(", ", $formattedISSNs);
	}

	protected function getTag_language() {
		return implode(", ", $this->dataProvider->getLanguages());
	}

	protected function getTag_address() {
	$publishPlace = $this->dataProvider->getPublishingPlace();

		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
		    $publishPlace = (empty($publishPlace)) ? array() : array($publishPlace[0]);
		}

		return implode(", ", $publishPlace);
	}

	protected function getTag_publisher() {
		$publisher = $this->dataProvider->getPublisher();
		return isset($publisher) ? implode("; ",$publisher) : '';
	}

	public function getTag_pages() {
		$bibTeX_pages = array();

		$pages = $this->dataProvider->getPages();
		foreach ($pages as $page) {
		
			$startPage = $page->getStartPage();
			$endPage = $page->getEndPage();
			if (!empty($startPage) && !empty($endPage)) {
				$bibTeX_pages[] = $startPage . '--' . $endPage;
				continue; 
			} 

			$totalPages = $page->getTotalPages();
			if (!empty($totalPages)) {
				$bibTeX_pages[] = $totalPages;
				continue;
			}							
			
		    $bibTeX_pages[] = $page->getUnstructuredText();
		}

		return implode(', ',$bibTeX_pages);
	}

	public function getTag_note() {
		$allFootnotes = '';

		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
		    $allFootnotes .= implode(', ', $this->dataProvider->getSchool());
		}

		foreach ($this->dataProvider->getFootnotes(15) as $footnotes) {
			$allFootnotes .= implode(', ', $footnotes);
		}

		return $allFootnotes;
	}

	public function getTag_volume() {
		return implode(', ', $this->dataProvider->getVolume());
	}

	protected function getTag_edition() {
		return implode(', ',$this->dataProvider->getEdition());
	}

	protected function getTag_abstract() {
		return implode(', ', $this->dataProvider->getAbstract());
	}

	protected function getTag_annote() {
		$annotations = array();

		$dataSources = $this->dataProvider->getDataSource();
		if (!empty($dataSources)) {
		    $annotations = array_merge($annotations, $dataSources);
		}

		return implode(", ", $annotations);
	}

	protected function getTag_school() {
		$schools = array();

		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED)) {
			$schools = $this->dataProvider->getSchool();
		}

		return implode(', ', $schools);
	}

	protected function getTag_keywords() {
		$keywords = $this->dataProvider->getKeywords();
		$keywords[] = '';
		$commaFreeKeywords = str_replace(',', '', $keywords);

		return implode(", ", $commaFreeKeywords);
	}

	protected function getTag_url() {
		return '';
	}

	protected function getTag_doi() {
		return implode(', ', $this->dataProvider->getDOI());
	}

	public function getTag_series() {
		$result = array();

		$series = $this->dataProvider->getUebergeordneteWerke();

		if (empty($series)) {
			$series = $this->dataProvider->getSeries();
		}

		$seriesTitles = array();
		foreach ($series as $serie) {
		    if (!empty($serie['title'])) {
		        $seriesTitles[] = $serie['title'];
		    }
		}

		return implode(', ', $seriesTitles);
	}

	protected function getTag_journal() {
		return $this->getTag('journal', $this->dataProvider->getJournal());
	}

	protected function getTag_issue() {
		return implode(', ', $this->dataProvider->getIssue());
	}

	protected function hasMediatype($mediatype) {
		$currentMediatypes = $this->dataProvider->getMediatype();
		return (($currentMediatypes & $mediatype) > 0) ;
	}


	protected function getTag($tagName, $tagValues) {
	    if (!isset($tagValues)) {
	        return '';
	    }

	    if (is_array($tagValues)) {
	        return implode(', ', $tagValues);
	    }

	    return $tagValues;
	}

}

?>