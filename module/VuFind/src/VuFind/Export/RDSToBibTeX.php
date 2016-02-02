<?php
/** 
 * RDSToBibTeX exporter for rds data
 *
 * PHP version 5
 *  
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */

namespace VuFind\Export;
use VuFind\Export\RDSToFormat;
use VuFind\Export\DataProvider\RDSDataProvider;

/**
 * RDSToBibTeX exporter for rds data
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSToBibTeX extends RDSToFormat
{

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

    /**
     * Constructor 
     *
     * @param string $driver current driver 
     */
    public function __construct($driver) 
    {
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

    /**
     * Get the record 
     *
     * @return string
     */
    public function getRecord() 
    {
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

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getReferenceType() 
    {
        $currentMediatypes  = $this->dataProvider->getMediatype();

        foreach ($this->referenceTypeByMediatype as $referenceType => $mediatypes) {
            if (($currentMediatypes & $mediatypes) > 0) {
                return '@' . $referenceType;
            }
        }

        return '@misc';
    }

     /**
     * Get the record parts
     *
     * @return string
     */
    protected function getCitationKey() 
    {
        $bibTeXId = "";

        $bibTeXId = $this->dataProvider->getID();

        return $bibTeXId;
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_urldate() 
    {
        return '';
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_title() 
    {
        $bibTeXTitle = "";

        $bibTeXTitle = $this->dataProvider->getTitle(RDSDataProvider::TITLE_HT);

        return implode(', ', $bibTeXTitle);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_author() 
    {
        $bibTeXAuthors = "";

        $authorsShort = $this->dataProvider->getAuthor();
        if (is_array($authorsShort)) {
            $bibTeXAuthors = implode(' and ', $authorsShort);
        } else {
            $bibTeXAuthors = $authorsShort;
        }

        return implode(' and ', $authorsShort);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_year() 
    {
        return $this->getTag('year', $this->dataProvider->getPublishingYear());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_isbn() 
    {
        $formattedISBNs = array();

        $isbns = $this->dataProvider->getISBNs();
        foreach ($isbns as $isbn) {
            $tmp = $isbn->getValue();
            $type =    $isbn->getType();
            if (!empty($type)) {
                $tmp .= " (" . $type . ")";
            }
            $formattedISBNs[] = $tmp;
        }

        return implode(", ", $formattedISBNs);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_issn() 
    {
        $formattedISSNs = array();

        $issns = $this->dataProvider->getISSNs();
        foreach ($issns as $issn) {
            $tmp = $issn->getValue();
            $type =    $issn->getType();
            if (!empty($type)) {
                $tmp .= " (" . $type . ")";
            }
            $formattedISSNs[] = $tmp;
        }

        return implode(", ", $formattedISSNs);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_language() 
    {
        return implode(", ", $this->dataProvider->getLanguages());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_address() 
    {
        $publishPlace = $this->dataProvider->getPublishingPlace();

        if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
            $publishPlace = (empty($publishPlace)) ? array() : array($publishPlace[0]);
        }

        return implode(", ", $publishPlace);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_publisher() 
    {
        $publisher = $this->dataProvider->getPublisher();
        return isset($publisher) ? implode("; ", $publisher) : '';
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    public function getTag_pages() 
    {
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

        return implode(', ', $bibTeX_pages);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    public function getTag_note() 
    {
        $allFootnotes = '';

        if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED)) {
            $allFootnotes .= implode(', ', $this->dataProvider->getSchool());
        }

        foreach ($this->dataProvider->getFootnotes(15) as $footnotes) {
            $allFootnotes .= implode(', ', $footnotes);
        }

        return $allFootnotes;
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    public function getTag_volume() 
    {
        return implode(', ', $this->dataProvider->getVolume());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_edition() 
    {
        return implode(', ', $this->dataProvider->getEdition());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_abstract() 
    {
        return implode(', ', $this->dataProvider->getAbstract());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_annote() 
    {
        $annotations = array();

        $dataSources = $this->dataProvider->getDataSource();
        if (!empty($dataSources)) {
            $annotations = array_merge($annotations, $dataSources);
        }

        return implode(", ", $annotations);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_school() 
    {
        $schools = array();

        if ($this->hasMediatype(RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED)) {
            $schools = $this->dataProvider->getSchool();
        }

        return implode(', ', $schools);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_keywords() 
    {
        $keywords = $this->dataProvider->getKeywords();
        $keywords[] = '';
        $commaFreeKeywords = str_replace(',', '', $keywords);

        return implode(", ", $commaFreeKeywords);
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_url() 
    {
        return '';
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_doi() 
    {
        return implode(', ', $this->dataProvider->getDOI());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    public function getTag_series() 
    {
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

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_journal() 
    {
        return $this->getTag('journal', $this->dataProvider->getJournal());
    }

    /**
     * Get the record parts
     *
     * @return string
     */
    protected function getTag_issue() 
    {
        return implode(', ', $this->dataProvider->getIssue());
    }

    /**
     * Get the media type 
     *
     * @param string $mediatype typo of hte media
     *
     * @return string
     */
    protected function hasMediatype($mediatype) 
    {
        $currentMediatypes = $this->dataProvider->getMediatype();
        return (($currentMediatypes & $mediatype) > 0) ;
    }

    /**
     * Get the tags
     *
     * @param string $tagName   name of the tag
     * @param string $tagValues value of the tag
     *
     * @return string
     */
    protected function getTag($tagName, $tagValues) 
    {
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
