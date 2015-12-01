<?php

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSToCOINS extends RDSToFormat {

	protected $rtfValFmt = '';
	protected $rft_genre = '';
	
	protected $keysets = array(
			'info:ofi/fmt:kev:mtx:book' => array(
					'ctx_ver',
					'ctx_enc',
					'rft_val_fmt',
					'rfr_id',
					'rft_au',
					'rft_btitle',
					'rft_date',
					'rft_edition',
					'rft_epage',
					//'rft_genre',
			 		'rft_isbn',
					'rft_issn',	
					'rft_pages',	
					'rft_place',	
					'rft_pub',
					'rft_series',
					'rft_spage',
					'rft_tpages',
					'rft_title',
			),
			'info:ofi/fmt:kev:mtx:journal' => array(
					'ctx_ver',
					'ctx_enc',
					'rft_val_fmt',
					'rfr_id',
					'rft_atitle',
					'rft_au',
					'rft_aucorp',
					'rft_date',
					//'rft_genre',
					'rft_issn',
					'rft_title',
					'rft_jtitle',
					'rft_stitle',
			),
			'info:ofi/fmt:kev:mtx:dc' => array(
					'ctx_ver',
					'ctx_enc',
					'rft_val_fmt',
					'rfr_id',
					'rft_au',
					'rft_title',
					'rft_date',
					'rft_language',
					//'rft_type',
			),
			'info:ofi/fmt:kev:mtx:dissertation' => array(
					'ctx_ver',
					'ctx_enc',
					'rft_val_fmt',
					'rfr_id',
					'rft_au',
					'rft_title',
					'rft_date',
					//'rft_genre',
					'rft_place',
			)
	);
	
	public function __construct($driver) {
	    parent::__construct($driver);
	    
	    $rtfValFmtByMediatype = array(
	    		'info:ofi/fmt:kev:mtx:journal' 		=> 	RDSDataProvider::MEDIATYPE_ARTICLE | 
	    												RDSDataProvider::MEDIATYPE_JOURNAL,
	    		'info:ofi/fmt:kev:mtx:dissertation' => 	RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED,
	    		'info:ofi/fmt:kev:mtx:book'    		=> 	RDSDataProvider::MEDIATYPE_BOOK | 
	    										  		RDSDataProvider::MEDIATYPE_BINARY |
	    										  		RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED,
	    		'info:ofi/fmt:kev:mtx:dc'      		=> 	RDSDataProvider::MEDIATYPE_ALL
	    );
	    
// 	    $rft_genreByMediatype = array(
// 	    		'book'  => RDSDataProvider::MEDIATYPE_BOOK,
// 	    		'bookitem' => 0,
// 	    		'proceeding' => 0,
// 	    		'report' => 0,
// 	    		'document' => 0,
// 	    		'unknown' => RDSDataProvider::MEDIATYPE_UNKNOWN,
	    		
// 	    		'issue' => RDSDataProvider::MEDIATYPE_JOURNAL,
// 	    		'article' => RDSDataProvider::MEDIATYPE_ARTICLE,
// 	    		'preprint' => 0,
// 	    );
	    
	    $currentMediatypes = $this->dataProvider->getMediatype();
	    foreach ($rtfValFmtByMediatype as $rtfValFmt => $mediatypes) {
	    	if (($currentMediatypes & $mediatypes) > 0) {
	    		$this->rtfValFmt = $rtfValFmt;
	    		break;
	    	}
	    }
	    
// 	    foreach ($rft_genreByMediatype as $rft_genre => $mediatypes) {
// 	    	if (($currentMediatypes & $mediatypes) > 0) {
// 	    		$this->rft_genre = $rft_genre;
// 	    		break;
// 	    	}
// 	    }
	    
	}

	public function getRecord() {
		$COinS = '';

		$keyset = array();
		
		$keyset = $this->keysets[$this->rtfValFmt];
		
		foreach ($keyset as $key) {
			$getKEVForKey = 'get_' . $key;
			$kev = $this->$getKEVForKey();
			$COinS .= (empty($kev)) ? '' : $kev . '&';
		}
		
		return  substr($COinS, 0, -1);
	}
	
	public function getFormattedRecordForDebug() {
		$debugOut = '';
		
		$parts = explode('&', $this->getFormattedRecord());
		foreach ($parts as $part) {
			$keyValuePair = explode('=', $part);
			
			$debugOut .= $keyValuePair[0] . '=' . urldecode($keyValuePair[1]) . "\n";
		}
		
		return $debugOut;
	}
	
	
	public function get_ctx_ver() {
		return $this->getKEV('ctx_ver', 'Z39.88-2004');
	}
	
	public function get_ctx_enc() {
		return $this->getKEV('ctx_enc', 'info:ofi/enc:UTF-8');
	}
	
	public function get_rft_au() {
		return $this->getKEV('rft.au', $this->dataProvider->getAuthor(), -1);
	}
	
	public function get_rft_aucorp() {
		return $this->getKEV('rft.aucorp', ''); //TODO
		//return $this->getKEV('rft.aucorp', implode(', ', $this->dataProvider->getCorporation()));
	}
	
	public function get_rft_atitle() {
		return $this->getKEV('rft.atitle', implode(',', $this->dataProvider->getTitle()));
	}
	
	public function get_rft_btitle() {
		return $this->getKEV('rft.btitle', implode(',', $this->dataProvider->getTitle()));
	}

	public function get_rft_date() {
		return $this->getKEV('rft.date',implode(', ', $this->dataProvider->getPublishingYear()));
	}
	
	public function get_rft_edition() {
		return $this->getKEV('rft.edition', implode(', ', $this->dataProvider->getEdition()));
	}
	
	public function get_rft_epage() {
		$result = array();
	
		foreach ($this->dataProvider->getPages() as $page) {
			$endPage = $page->getEndPage();
			if (isset($endPage)) {
				$result[] = $page->getEndPage();
			}
		}
	
		return $this->getKEV('rft.epage', implode(', ', $result));
	}

	public function get_rft_isbn() {
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
	
		return $this->getKEV('rft.isbn', implode(", ", $formattedISBNs));
	}
	
	public function get_rft_issn() {
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
	
		return $this->getKEV('rft.issn', implode(", ", $formattedISSNs));
	
	}
	
	public function get_rfr_id() {
		global $configArray;
		$coinsID = isset($configArray['OpenURL']['rfr_id']) ?
		$configArray['OpenURL']['rfr_id'] :
		$configArray['COinS']['identifier'];
		if (empty($coinsID)) {
			$coinsID = 'vufind.svn.sourceforge.net';
		}
	
		$rfr_id = 'info:sid/' . $coinsID . ':generator';
		return $this->getKEV('rfr.id', $rfr_id);
	}

	public function get_rft_jtitle() {
		$titles = $this->dataProvider->getTitle(RDSDataProvider::TITLE_HT);
		
		if (empty($titles)) {
			$titles = $this->dataProvider->getTitle();
		}
		
		return $this->getKEV('rft.jitle',implode(',', $titles));
	}
	
	public function get_rft_language() {
		return $this->getKEV('rft.language',  implode(', ', $this->dataProvider->getLanguages()));
	}
	
	public function get_rft_pages() {
		$coins_pages = array();
	
		$pages = $this->dataProvider->getPages();
		foreach ($pages as $page) {
	
			$startPage = $page->getStartPage();
			$endPage = $page->getEndPage();
			if (!empty($startPage) && !empty($endPage)) {
				$coins_pages[] = $startPage . '-' . $endPage;
				continue;
			}
	
			$totalPages = $page->getTotalPages();
			if (!empty($totalPages)) {
				$coins_pages[] = $totalPages;
				continue;
			}
	
			$coins_pages[] = $page->getUnstructuredText();
		}
	
		return $this->getKEV('rft.pages', implode(', ',$coins_pages));
	
	}
	
	public function get_rft_place() {
		$publishPlace = $this->dataProvider->getPublishingPlace();
		
		if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
			$publishPlace = (empty($publishPlace)) ? array() : array($publishPlace[0]);
		}
		
		return $this->getKEV('rft.place', implode(', ', $publishPlace));
	}
	
	
	public function get_rft_pub() {
		return $this->getKEV('rft.pub', implode(', ', $this->dataProvider->getPublisher()));
	}
	
	public function get_rft_series() {
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
	
		return $this->getKEV('rft.series', implode(', ', $seriesTitles));
	}
	
	public function get_rft_spage() {
		$result = array();
	
		foreach ($this->dataProvider->getPages() as $page) {
			$startPage = $page->getStartPage();
			if (isset($startPage)) {
				$result[] = $page->getStartPage();
			}
		}
	
		return $this->getKEV('rft.spage', implode(', ', $result));
	}
	
	public function get_rft_stitle() {
		$titles = $this->dataProvider->getTitle(RDSDataProvider::TITLE_SHORT);
	
		if (empty($titles)) {
			$titles = $this->dataProvider->getTitle();
		}
	
		return $this->getKEV('rft.stitle',implode(',', $titles));
	}
	
	public function get_rft_title() {
		$titles = $this->dataProvider->getTitle(RDSDataProvider::TITLE_HT);
		
		if (empty($titles)) {
			$titles = $this->dataProvider->getTitle();
		}
		
		return $this->getKEV('rft.title',implode(',', $titles));
	}
	
	public function get_rft_tpages() {
		$result = array();
	
		foreach ($this->dataProvider->getPages() as $page) {
			$totalPages = $page->getTotalPages();
			if (isset($totalPages)) {
				$result[] = $page->getTotalPages();
			}
		}
	
		return $this->getKEV('rft.tpages', implode(', ', $result));
	}
	
	public function get_rft_val_fmt() {
		return $this->getKEV('rft_val_fmt', $this->rtfValFmt);
	}
	
// 	public function get_rft_genre() {
// 		return $this->getKEV('rft.genre', 'article');
// 		return $this->getKEV('rft.genre', $this->rft_genre);
// 	}
	
// 	public function get_rft_type() {
// 		return $this->getKEV('rft.type', 'Book');
// 	}
	
	

	protected function hasMediatype($mediatype) {
		$currentMediatypes = $this->dataProvider->getMediatype();
		return (($currentMediatypes & $mediatype) > 0) ;
	}
	
	protected function getKEV($key, $values, $tagCountMax = 1) {
		$kevs = array();
	
		if (empty($values)) {
			return '';
		}
	
		if (!is_array($values)) {
			$values = array($values);
		}
	
		if ($tagCountMax != -1) {
			$values = array_slice($values, 0, $tagCountMax);
		}
	
		foreach ($values as $value) {
			$kevs[] = $key . "=" . urlencode($value);
		}
	
		return implode("&", $kevs);
	}
	
	
	
	

}

?>