<?php
/** 
 * RDSDataProviderProxy for rds data
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
use VuFind\Export\DataProvider\RDSDataProvider;

/**
 * RDSDataProviderProxy for rds data
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSDataProviderProxy implements RDSDataProvider
{

    protected $fields;
    protected $recordDriver;

    protected $mediatypeByOpenurlGenre = array();

    /**
     * Constructor 
     *
     * @param array  $indexFields  fields 
     * @param string $recordDriver current recordDriver
     */
    public function __construct($indexFields, $recordDriver) 
    {
        $this->fields = $indexFields;
        $this->recordDriver = $recordDriver;

        $this->mediatypeByOpenurlGenre = array(
        RDSDataProvider::MEDIATYPE_ARTICLE     => array('article'),
        RDSDataProvider::MEDIATYPE_BOOK     => array('book','bookitem'),
        RDSDataProvider::MEDIATYPE_JOURNAL     => array('journal')
        );

    }

    /**
     * Get rds data 
     *
     * @return string
     */
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

    /**
     * Get rds data 
     *
     * @return string
     */
    public function getID() 
    {
        return isset($this->fields['id']) ? $this->fields['id'] : '';
    }

    /**
     * Get rds data 
     *
     * @param string $type default full
     *
     * @return string
     */
    public function getTitle($type = RDSDataProvider::TITLE_FULL) 
    {
        return $this->getField('title');
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @param string $type default short author 
     *
     * @return string
     */
    public function getAuthor($type = RDSDataProvider::AUTHORS_SHORT) 
    {
        $result = array();
        if (isset($this->fields['authors'])) {
            $result = $this->fields['authors'];
            for ($i = 0; $i < count($result); $i++) {
                $result[$i] = preg_replace('|<sup>[^<]*</sup>|u', '', $result[$i]);
            }
        }

        return $result;
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @return string
     */
    public function getPublishingYear() 
    {
        return $this->getField('source', 'dates', 'published', 'year');

        if (isset($this->fields['dates'])) {
            $dates = $this->fields['dates'];
        } elseif (isset($this->fields['source']) && isset($this->fields['source']['dates'])) {
            $dates = $this->fields['source']['dates'];
        } else {
            return '';
        }

        if (isset($dates['published'])) {
            return $dates['published']['year'];
        } else {
            return '';
        }
    }

    /**
     * Get rds data 
     *
     * @return string
     */
    public function getLanguages() 
    {
        return $this->getField('languages');
    }

    // code duplication form RDSProxyRecord !
    // !modification: getISBN() -> getISBNs()

    /**
     * Get rds data 
     *
     * @return string
     */
    public function getISBNs()
    {
        $isbns = array();
        if (isset($this->fields['pisbn'])) {
            $isbns[] = new ISBN_('print', $this->fields['pisbn']);
        } elseif (isset($this->fields['source']) && isset($this->fields['source']['pisbn'])) {
            $isbns[] = new ISBN_('print', $this->fields['source']['pisbn']);
        }
        if (isset($this->fields['eisbn'])) {
            $isbns[] = new ISBN_('electronic', $this->fields['eisbn']);
        } elseif (isset($this->fields['source']) && isset($this->fields['source']['eisbn'])) {
            $isbns[] = new ISBN_('electronic', $this->fields['source']['eisbn']);
        }

        return $isbns;
    }

    // code duplication form RDSProxyRecord !
    // ! modification: getISSN() -> getISSNs()

    /**
     * Get rds data 
     *
     * @return string
     */
    public function getISSNs() 
    {
        $issns = array();
        if (isset($this->fields['pissn'])) {
            $issns[] = new ISSN('print', $this->fields['pissn']);
        } elseif (isset($this->fields['source']) && isset($this->fields['source']['pissn'])) {
            $issns[] = new ISSN('print', $this->fields['source']['pissn']);
        }
        if (isset($this->fields['eissn'])) {
            $issns[] = new ISSN('electronic', $this->fields['eissn']);
        } elseif (isset($this->fields['source']) && isset($this->fields['source']['eissn'])) {
            $issns[] = new ISSN('electronic', $this->fields['source']['eisbn']);
        }

        return $issns;
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getPublishingPlace() 
    {
        return array('');
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getPublisher() 
    {
        return array();
    }

    /**
     * Get rds data 
     *
     * @return string 
     */
    public function getPages() 
    {
        $startPages = $this->getField('startpage');

        $totalPages = $this->getField('numpages');
        if (empty($totalPages)) {
            $totalPages = $this->getField('source', 'numpages');
        } 
        
        $endPage = null;
        if (!empty($startPages) && !empty($totalPages)) {
            $endPage = $startPages[0] + $totalPages[0] - 1;
        }

        return array(new Page('', $totalPages[0], $startPages[0], $endPage));
    }


    /**
     * Get rds data 
     *
     * @param string $type types
     *
     * @return array
     */
    public function getFootnotes($type) 
    {
        return array();
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getEdition() 
    {
        return array();
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getVolume() 
    {
        $volumes = $this->getField('volume');

        if (empty($volume)) {
            $volumes = $this->getField('source', 'volume');
        }

        return $volumes;
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getIssue() 
    {
        $issues = $this->getField('issue');

        if (empty($issues)) {
            $issues = $this->getField('source', 'issue');
        }

        return $issues;
    }

    // code duplication form RDSProxyRecord !
    // ! modified

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getAbstract() 
    {
        return $this->getField('abstracts', 'main');
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getSchool() 
    {
        return '';
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getKeywords() 
    {
        return $this->getSubjects('general');
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getPersistentLink() 
    {
        return null;
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getDOI()
    {
        return $this->getField('doi');
    }

    // code duplication form RDSProxyRecord !
    // ! modified !

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getDataSource() 
    {
        return $this->getField('datasource');
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getJournal() 
    {
        if ($this->hasMediaType(RDSDataProvider::MEDIATYPE_ARTICLE) 
            || $this->hasMediaType(RDSDataProvider::MEDIATYPE_JOURNAL)
        ) {
            $journals = $this->getField('source', 'title');
            
            if (empty($journals)) {
                $journals = $this->getField('source', 'display');
            }
            
            // TODO: use appropriate modifier instead of removing the tags
            return str_replace('&amp;', '&', strip_tags($journals[0]));
        } else {
            return array();
        }
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getSeries() 
    {
        $series = $this->getField('series', 'title');

        $seriesArr = array();
        foreach ($series as $serie) {
            $seriesArr[] = array('title' => $serie);
        }

        return $seriesArr;
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getUebergeordneteWerke() 
    {
        return array();
    }

    // code duplication form RDSProxyRecord !

    /**
     * Get rds data 
     *
     * @param string $category category
     *
     * @return array
     */
    protected function getSubjects($category)
    {
        if (isset($this->fields['subjects']) && isset($this->fields['subjects'][$category])) {
            return $this->fields['subjects'][$category];
        } else {
            return array();
        }
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    public function getMARC() 
    {
        return '';
    }


    /**
     * Get rds data 
     *
     * @return array
     */
    public function getFulltextLinks() 
    {
        return array();
    }
    
    /**
     * Always return an ARRAY of field values
     *
     * @return array
     */
    protected function getField() 
    {
        return $this->getFieldRecursive($this->fields, func_get_args());
    }

    /**
     * Get rds data 
     *
     * @return array
     */
    protected function getFieldRecursive() 
    {
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


    /**
     * Get rds data 
     *
     * @param string $values string or array
     *
     * @return array
     */
    protected function getArrayOf($values) 
    {
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


    /**
     * Get rds data 
     *
     * @param string $mediatype type of media
     *
     * @return array
     */
    protected function hasMediatype($mediatype) 
    {
        $currentMediatypes = $this->getMediatype();
        return (($currentMediatypes & $mediatype) > 0) ;
    }


    /**
     * For debug 
     *
     * @return array
     */
    public function getFields() 
    {
        $debug_out = "";

        $field_list = Array('id', 'dbid', 'openurlgenre', 'links', 'authors', 'series', 'source');
        $field_list = array_merge($field_list, array('volume'));


        // $field_list = Array('', '');


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
