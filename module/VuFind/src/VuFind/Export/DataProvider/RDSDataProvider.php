<?php
/** 
 * RDSDataProvider exporter for rds data
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


namespace VuFind\Export\DataProvider;

/**
 * RDSDataProvider exporter for rds data
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
interface RDSDataProvider
{

    const MEDIATYPE_BOOK                            =     1;
    const MEDIATYPE_ARTICLE                        =     2;
    const MEDIATYPE_JOURNAL                         =     4;
    const MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED  =     8; // university only
    const MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED       =    16; // university and publisher
    const MEDIATYPE_UEBERGEORDNETES_WERK          =    32;
    const MEDIATYPE_SERIE                          =    64;
    const MEDIATYPE_BINARY                          =   128;
    const MEDIATYPE_EBOOK                          =   256;
    const MEDIATYPE_PROCEEDING                      =   512; // gkko
    const MEDIATYPE_AUDIO                          =  1024; //
    const MEDIATYPE_VIDEO                           =  2048; //
    const MEDIATYPE_MAP                              =  4096; //
    const MEDIATYPE_MUDRUCK                          =  8192; //
    const MEDIATYPE_UNKNOWN                          = 16384;
                                                  
    const MEDIATYPE_ALL                              = 32767;

    const TITLE_SHORT    = 1;
    const TITLE_LONG     = 2;
    const TITLE_FULL       = 4;
    const TITEL_SUBTITLE = 8;
    const TITLE_SERIES   = 16;
    const TITLE_HT         = 32;

    const AUTHORS_SHORT = 1;
    const AUTHORS_LONG  = 2;

    const FOOTNOTES_ALL = 15;
    const FOOTNOTES = 1;
    const FOOTNOTES_ENTHWERKE = 2;
    const FOOTNOTES_EBOOKS = 4;
    const FOOTNOTES_INTERPRET = 8;

    /**
     * Get.
     *
     * @return void
     */
    public function getMediatype();

    /**
     * Get.
     *
     * @return void
     */
    public function getID();

    /**
     * Get.
     *
     * @return void
     */
    public function getFields();

    // Methods to retrieve bibliographic data

    /**
     * Get.
     *
     * @param string $type type
     *
     * @return void
     */
    public function getTitle($type);

    /**
     * Get.
     *
     * @param string $type type
     *
     * @return void
     */
    public function getAuthor($type);

    /**
     * Get.
     *
     * @return void
     */
    public function getPublisher();

    /**
     * Get.
     *
     * @return void
     */
    public function getPublishingPlace();

    /**
     * Get.
     *
     * @return void
     */
    public function getPublishingYear();

    /**
     * Get.
     *
     * @return void
     */
    public function getLanguages();

    /**
     * Get.
     *
     * @return void
     */
    public function getISBNs();

    /**
     * Get.
     *
     * @return void
     */
    public function getISSNs();

    /**
     * Get.
     *
     * @return void
     */
    public function getPages();
    
    /**
     * Get.
     *
     * @return void
     */
    public function getEdition();

    /**
     * Get.
     *
     * @return void
     */
    public function getAbstract();

    /**
     * Get array of strings.
     *
     * @return void
     */
    public function getKeywords();

    /**
     * Get.
     *
     * @return void
     */
    public function getSchool();

    /**
     * Get.
     *
     * @param string $type type
     *
     * @return void
     */
    public function getFootnotes($type);

    /**
     * Get.
     *
     * @return void
     */
    public function getPersistentLink();

    /**
     * Get.
     *
     * @return void
     */
    public function getFulltextLinks();

    /**
     * Get array that should look like this: 
     * array(array('id'=> seriesId, 'title'=> seriesTitle, 
     *             'volume'=> seriesVolume), array(...), array(...), ...);
     *
     * @return void
     */
    public function getSeries();

    /**
     * Get.
     *
     * @return void
     */
    public function getUebergeordneteWerke();

    /**
     * Get.
     *
     * @return void
     */
    public function getJournal();

    /**
     * Get.
     *
     * @return void
     */
    public function getIssue();

    /**
     * Get.
     *
     * @return void
     */
    public function getVolume();

    /**
     * Get.
     *
     * @return void
     */
    public function getDOI();

    /**
     * Get.
     *
     * @return void
     */
    public function getDataSource();

    /**
     * Get.
     *
     * @return void
     */
    public function getMARC();

}


class Journal
{
    protected $title;
    protected $issn;
    protected $issue;
    protected $publishingYear;
    protected $publishingMonth;
    protected $publishingDay;

    protected $startPage;
    protected $numPages;

    public function getTitle() 
    {
        return $this->title;
    }
    public function setTitle($title) 
    {
        $this->title = $title;
    }

    public function getISSN() 
    {
        return $this->issn;
    }
    public function setISSN($issn) 
    {
        return $this->issn;
    }

    public function getIssue() 
    {
        return $this->issue;
    }
    public function setIssue($issue) 
    {
        return $this->issue;
    }

    public function getStartPage() 
    {
        return $this->startPage;
    }
    public function setStartPage($startPage) 
    {
        return $this->startPage;
    }

    public function getNumPages() 
    {
        return $this->numPages;
    }
    public function setNumPages($numPages) 
    {
        return $this->numPages;
    }

    public function getPublishingYear() 
    {
        return $this->publishingYear;
    }
    public function setPublishingYear($publishingYear) 
    {
        return $this->publishingYear;
    }
}

class ISBN_
{
    protected $type;
    protected $value;

    public function __construct($type, $value) 
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType() 
    {
        return $this->type;
    }

    public function getValue() 
    {
        return $this->value;
    }
}

class ISSN
{
    protected $type;
    protected $value;

    public function __construct($type, $value) 
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType() 
    {
        return $this->type;
    }

    public function getValue() 
    {
        return $this->value;
    }
}

class Page
{
    protected $unstructuredText;
    protected $startPage;
    protected $endPage;
    protected $totalPages;
    
    public function __construct($unstructuredText = '', $totalPages = null, $startPage = null, $endPage = null) 
    {
        $this->unstructuredText = $unstructuredText;
        $this->startPage = $startPage;
        $this->endPage = $endPage;
        $this->totalPages = $totalPages;
    }

    public function getStartPage() 
    {
        return $this->startPage;
    }
    
    public function getEndPage() 
    {
        return $this->endPage;
    }
    
    public function getTotalPages() 
    {
        return $this->totalPages;
    }
    
    public function getUnstructuredText() 
    {
        return $this->unstructuredText;
    }
}
?>
