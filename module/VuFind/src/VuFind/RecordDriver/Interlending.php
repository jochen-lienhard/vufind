<?php
/**
 * Model for MARC records in Solr.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 * Copyright (C) The National Library of Finland 2015.
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
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author   Jochen Lienhard <jochen.lienhard@ub.uni-freiburg.de>
 * @author   Hannah Born <hannah.born@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace VuFind\RecordDriver;

/**
 * Model for MARC records in Solr.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author   Jochen Lienhard <jochen.lienhard@ub.uni-freiburg.de>
 * @author   Hannah Born <hannah.born@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class Interlending extends SolrMarc
{

    use FullMarcTrait;

    /**** notwendig für SWB - Fernleihe ****/

    /**
     *
     * @var array;
     */
    protected $ppns = [];

    /**
     *
     * @var array;
     */
    protected $libraries = [];

    /**
     *
     * @var bool 
     */
    protected $atCurrentLibrary = false;

    /**
     *
     * @var bool 
     */
    protected $holdings = null;

    /************************ Overwritten *************************/

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     */
    public function getAllSubjectHeadings()
    {
        // These are the fields that may contain subject headings:
        $fields = [
            '600', '610', '611', '630', '648', '650', 
            '651', '655', '656', '689'
        ];

        // This is all the collected data:
        $retval = [];

        // Try each MARC field one at a time:
        foreach ($fields as $field) {
            // Do we have any results for the current field?  If not, try the next.
            $results = $this->getMarcRecord()->getFields($field);
            if (!$results) {
                continue;
            }

            // If we got here, we found results -- let's loop through them.
            foreach ($results as $result) {
                // Start an array for holding the chunks of the current heading:
                $current = [];

                // Get all the chunks and collect them together:
                $subfields = $result->getSubfields();
                if ($subfields) {
                    foreach ($subfields as $subfield) {
                        // Numeric subfields are for control purposes and should not
                        // be displayed:
                        if (!is_numeric($subfield->getCode()) && $subfield->getCode()==strtolower($subfield->getCode())) {
                            $current[] = $subfield->getData();
                        }
                    }
                    // If we found at least one chunk, add a heading to our result:
                    if (!empty($current)) {
                        $retval[] = $current;
                    }
                }
            }
        }

        // Remove duplicates and then send back everything we collected:
        return array_map(
            'unserialize', array_unique(array_map('serialize', $retval))
        );
    }


    /************************ NEW *********************************/

    /**
     * Get Content of 924 as array: isil => array of subfields
     * 
     * @param boolean $isilAsKey          uses ISILs as array keys - be carefull, 
     * information is dropped
     * @param boolean $recurringSubfields allow recurring subfields
     * 
     * @return array
     * 
     */
    public function getField924($isilAsKey = true, $recurringSubfields = false)
    {
        $f924 = $this->getMarcRecord()->getFields('924');
        $result = [];
        foreach ($f924 as $field) {
            $subfields = $field->getSubfields();
            $tmpSubfields = [];
            $isil = null;
            foreach ($subfields as $subfield) {
                if ($subfield->getCode() == 'b') {
                    $isil = $subfield->getData();
                        $tmpSubfields[$subfield->getCode()] = $isil;
                } elseif ($subfield->getCode() == 'd') {
                    $ill_status = '';
                    switch ($subfield->getData()) {
                        case 'a': $ill_status = 'ill_status_a';
                            break;
                        case 'b': $ill_status = 'ill_status_b';
                            break;
                        case 'c': $ill_status = 'ill_status_c';
                            break;
                        case 'd': $ill_status = 'ill_status_d';
                            break;
                        case 'e': $ill_status = 'ill_status_e';
                            break;
                        default: $ill_status = 'ill_status_d';
                    }
                    $tmpSubfields['d'] = $subfield->getData();
                    $tmpSubfields['ill_status'] = $ill_status;
                } elseif (!isset($tmpSubfields[$subfield->getCode()])) {
                    // without $recurringSubfields, only the first occurence is 
                    // included
                    $tmpSubfields[$subfield->getCode()] = $subfield->getData();
                } elseif ($recurringSubfields) {
                    // with §recurringSubfields, all occurences are put together
                    $tmpSubfields[$subfield->getCode()] .= ' | '.$subfield->getData();

                }
            }
            if (isset($isil) && $isilAsKey) {
                $result[$isil] = $tmpSubfields;
            } else {
                $result[] = $tmpSubfields;
            }
        }
        return $result;
    }


    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     */
    public function getHoldingsGlobal()
    {
        $fields=$this->getMarcRecord()->getFields('924');
        $result = [];
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            $tmpSubfields = [];
            $isil = null;
            foreach ($subfields as $subfield) {
                if (!isset($tmpSubfields[$subfield->getCode()])) {
                    $tmpSubfields[$subfield->getCode()] = $subfield->getData();
                } else {
                    $tmpSubfields[$subfield->getCode()] .= ' | '.$subfield->getData();

                }
            }
            $result[] = $tmpSubfields;
        }
        return $result;
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return boolean
     */
    public function checkHoldingsLocal()
    {  
        $local = false; 
        $global = $this->getFieldArray('924', ['b'], true, ' : ');
        $localIDs = $this->recordConfig->Interlending->isil->toArray(); 
        foreach ($global as $value) {
            if (in_array($value, $localIDs)) {
                $local = true;
            }
        }
        return $local;
    }

    /**
     * Get isils from interlending config.
     *
     * @return array
     */
    public function getIsils()
    {
        return $this->recordConfig->Interlending->isil->toArray();
    }


    /**
     * Get consortial links.
     *
     * @return array 
     */
    public function getConsortiumLinks()
    {
        $consortium = $this->recordConfig->Interlending->consortium->toArray();
        return $consortium;
    }

    /**
     * Get link to consortium catalog.
     *
     * @return array 
     */
    public function getConsortiumCatalog()
    {
	$data = [];
        if (!empty($this->getLocalOPACLink())) {
           $data[key($this->getControlNumberID())] = $this->getLocalOPACLink();
        }
        return $data;
    }

    /**
     * Generate link to local OPAC via consortium
     *
     * @return string
     */
    public function getLocalOPACLink() 
    {
        $link = "";
        $uid = $this->getControlNumberID();
        $clinks = $this-> getConsortiumLinks();

        if (array_key_exists(key($uid), $clinks)) {
            $link = str_replace("{PPN}", $uid[key($uid)], $clinks[key($uid)]);
        }

        return $link;
    }

    /**
     * Get local ppn.
     *
     * @return string 
     */
    public function getLocalPPN()
    {
        $controlNumberID=$this->recordConfig->Interlending->controlNumberID;

        $ppn = false;
        $split = explode(')', $this->getUniqueID());
      
        // check controlNumberID 
        if (count($split) == 2) {
            if ($split[0] === "(".$controlNumberID) {
                $ppn = $split[1];
            } 
        }
        
        return $ppn;
    }

    /**
     * Get ID and controlnumber.
     *
     * @return array 
     */
    public function getControlNumberID()
    {
        $cn = [];
        $split = explode(')', $this->getUniqueID());

        // check controlNumberID 
        if (count($split) == 2) {
            $cn[substr($split[0], 1)] = $split[1];
        }
        return $cn;
    }

    /**
     * Get an array with RVK shortcut as key and description as value (array)
     *
     * @return array
     */
    public function getRVKNotations()
    {
        $notationList = [];
        $replace = [
            '"' => "'",
        ];
        foreach ($this->getMarcRecord()->getFields('936') as $field) {
            $suba = $field->getSubField('a');
            if ($suba) {
                $title = [];
                foreach ($field->getSubFields('k') as $item) {
                    $title[] = htmlentities($item->getData());
                }
                $notationList[$suba->getData()] = $title;
            }
        }
        return $notationList;
    }

    /**
     * Maps formats from formats.ini to icon file names
     *
     * @param string $formats that are avialable
     *
     * @return string
     */
    protected function mapIcon($formats) 
    {

        //this function uses simplifies formats as we can only show one icon
        $formats = $this->simplify($formats);
        foreach ($formats as $k => $format) {
            $formats[$k] = strtolower($format);
        }
        $return = '';
        if (is_array($formats)) {
            if (in_array('electronicresource', $formats) && in_array('e-book', $formats)) {
                $return = 'book-e';
            } elseif (in_array('videodisc', $formats) && in_array('video', $formats)) {
                $return = 'movie';
            } elseif (in_array('electronicresource', $formats) && in_array('journal', $formats)) {
                $return = 'journal-e';
            } elseif (in_array('opticaldisc', $formats) && in_array('e-book', $formats)) {
                $return = 'dvd';
            } elseif (in_array('cd', $formats) && in_array('soundrecording', $formats)) {
                $return = 'cdrom';
            } elseif (in_array('book', $formats) && in_array('compilation', $formats)) {
                $return = 'serie';
            } elseif (in_array('musicalscore', $formats)) {
                $return = 'sheet';
            } elseif (in_array('atlas', $formats)) {
                $return = 'map';
            } elseif (in_array('serial', $formats)) {
                $return = 'serie';
            } elseif (in_array('journal', $formats)) {
                $return = 'journal';
            } elseif (in_array('conference proceeding', $formats)) {
                $return = 'journal';
            } elseif (in_array('e-journal', $formats)) {
                $return = 'journal-e';
            } elseif (in_array('text', $formats)) {
                $return = 'article';
            } elseif (in_array('pdf', $formats)) {
                $return = 'article';
            } elseif (in_array('book', $formats)) {
                $return = 'book';
            } elseif (in_array('book chapter', $formats)) {
                $return = 'book';
            } elseif (in_array('e-book', $formats)) {
                $return = 'book-e';
            } elseif (in_array('ebook', $formats)) {
                $return = 'book-e';
            } elseif (in_array('vhs', $formats)) {
                $return = 'videocasette';
            } elseif (in_array('video', $formats)) {
                $return = 'video';
            } elseif (in_array('microfilm', $formats)) {
                $return = 'microfilm';
            } elseif (in_array('platter', $formats)) {
                $return = 'medi';
            } elseif (in_array('dvd/bluray', $formats)) {
                $return = 'dvd';
            } elseif (in_array('music-cd', $formats)) {
                $return = 'cdrom';
            } elseif (in_array('cd-rom', $formats)) {
                $return = 'cdrom';
            } elseif (in_array('article', $formats)) {
                $return = 'article';
            } elseif (in_array('magazine article', $formats)) {
                $return = 'article';
            } elseif (in_array('journal article', $formats)) {
                $return = 'article';
            } elseif (in_array('band', $formats)) {
                $return = 'book';
            } elseif (in_array('cassette', $formats)) {
                $return = 'mc';
            } elseif (in_array('soundrecording', $formats)) {
                $return = 'audio';
            } elseif (in_array('norm', $formats)) {
                $return = 'norm';
            } elseif (in_array('thesis', $formats)) {
                $return = 'book';
            } elseif (in_array('proceedings', $formats)) {
                $return = 'book';
            } elseif (in_array('electronic', $formats)) {
                $return = 'binary';
            } else {
                $return =  'article'; 
            }
        }
        return $return;
    }

    /**
     * General serial items. 
     *
     * @return boolean
     */
    public function isSerial()
    {
        $leader = $this->getMarcRecord()->getLeader();
        $leader_7 = strtoupper($leader{7});
        if ($leader_7 === 'S') {
            return true;
        }
        return false;
    }

    /**
     * Nach der Dokumentation des Fernleihportals
     * 
     * @return boolean
     */
    public function isArticle()
    {
        $leader = $this->getMarcRecord()->getLeader();
        $leader_7 = strtoupper($leader{7});
        // A = Aufsätze aus Monographien
        // B = Aufsätze aus Zeitschriften (wird aber wohl nicht genutzt))
        if ($leader_7 === 'A' || $leader_7 === 'B') {
            return true;
        }
        return false;
    }

    /**
     * Is this a Newspaper?
     * 
     * @return boolean
     */
    public function isNewspaper()
    {
        $f008 = null;
        $f008_21 = '';
        $f008 = $this->getMarcRecord()->getFields("008", false);

        foreach ($f008 as $field) {
            $data = strtoupper($field->getData());
            if (strlen($data) >= 21) {
                $f008_21 = $data{21};
            }
        }
        if ($this->isSerial() && $f008_21 == 'N') {
            return true;
        }
        return false;
    }


    /**
     * is this a Journal, implies it's a serial
     * 
     * @return boolean
     */
    public function isJournal()
    {
        $f008 = null;
        $f008_21 = '';
        $f008 = $this->getMarcRecord()->getFields("008", false);

        foreach ($f008 as $field) {
            $data = strtoupper($field->getData());
            if (strlen($data) >= 21) {
                $f008_21 = $data{21};
            }
        }
        if ($this->isSerial() && $f008_21 == 'P') {
            return true;
        }
        return false;
    }

    /**
     * Ist der Titel ein EBook? 
     * Wertet die Felder 007/00, 007/01 und Leader 7 aus
     * @return boolean
     */
    public function isEBook()
    {
        $f007 = $leader = null;
        $f007_0 = $f007_1 = $leader_7 = '';
        $f007 = $this->getMarcRecord()->getFields("007", false);
        foreach ($f007 as $field) {
            $data = strtoupper($field->getData());
            if (strlen($data) > 0) {
                $f007_0 = $data{0};
            }
            if (strlen($data) > 1) {
                $f007_1 = $data{1};
            }
        }
        $leader = $this->getMarcRecord()->getLeader();
        $leader_7 = strtoupper($leader{7});
        if ($leader_7 == 'M') {
            if ($f007_0 == 'C' && $f007_1 == 'R') {
                return true;
            }
        }
        return false;
    }

   /**
    * Check if free available.
    *
    * @return boolean
    */
    public function isFree()
    {
        $status = false;
        $f856 = $this->getFieldArray([856 => 'z']);
        foreach ($f856 as $field) {
            if (strpos(strtolower($field), 'kostenfrei') !== FALSE) {
                $status = true;
            }
        }
        return $status;
    }

    /**
     * Returns German library network shortcut. 
     * @param bool $outputIsil 
     * @return string
     */
    public function getNetwork($outputIsil = false)
    {
        $raw = trim($this->getUniqueID());

        preg_match('/\((.*?)\)/', $raw, $matches);
        $isil = $matches[1];
        if ($outputIsil) {
            return $isil;
        } else {
            return $this->translate($isil);
        }
    }

    /**
     * Feturn found SWB IDs
     * 
     * @return array
     */
    public function getSwbId()
    {
        return array_unique($this->ppns);
    }

   /**
     * Do we have a SWB PPN
     * 
     * @return boolean
     */
    public function hasSwbId()
    {
        return count($this->ppns) > 0;
    }

    /**
     * Determin if an item is available locally
     * 
     * @param $webservice = false
     * 
     * @return boolean
     */
    public function isAtCurrentLibrary($webservice = false)
    {
        $status = false;

            // if we have local holdings, item can't be ordered
            if ($this->checkHoldingsLocal()) {
                $status = true;
            } elseif ($webservice && $this->getNetwork() == 'SWB'
                 && $this->hasParallelEditions()
            ) {
	        // ToDo replace this check by own method
                // Parallel Ausgaben suchen
                $status = true;
            } elseif ($webservice && $this->getNetwork() !== 'SWB'
                && $this->queryWebservice()
            ) {
                // ToDo replace this check by own method
                // Suche ob im localen Katalog vorhanden
                $status = true;
            }

        // we dont't want to do the query twice, so we save the status
        $this->atCurrentLibrary = $status;
        return $status;

    }

    /**
     * Quer< solr for parallel Editions available at local libraries
     * Save the found PPNs in global array
     * 
     * @return boolean
     */
    protected function hasParallelEditions()
    {
        $ppns = [];
        $related = $this->tryMethod('getRelatedEditions');
        $hasParallel = false;

        foreach ($related as $rel) {
            $ppns[] = $rel['id'];
        }
        return $hasParallel;

        $parallel = [];
        if (count($ppns) > 0) {
            $parallel = $this->holding->getParallelEditions($ppns, $this->client->getIsilAvailability());         

            // check the found records for local available isils            
            $isils = [];
            foreach ($parallel->getResults() as $record) {
                $f924 = $this->getField924(true);
                $recordIsils = array_keys($f924);
                $isils = array_merge($isils, $recordIsils);
            }
            foreach ($isils as $isil) {
                if (in_array($isil, $this->getIsils())) {
                    $hasParallel = true;
                    $this->ppns[] = $this->getUniqueId();
                }
            }
        }
        return $hasParallel;
    }

    public function getRelatedEditions()
    {
        $related = [];
        $f775 = $this->getMarcRecord()->getFields('775');
        foreach ($f775 as $field) {
            $tmp = [];
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                switch ($subfield->getCode()) {
                    case 'i': $label = 'description';
                        break;
                    case 't': $label = 'title';
                        break;
                    case 'w' : $label = 'id';
                        break;
                    case 'a' : $label = 'author';
                        break;
                    default: $label = 'unknown_field';
                }
                if (!array_key_exists($label, $tmp)) {
                    $tmp[$label] = $subfield->getData();
                }
                if (!array_key_exists('description', $tmp)) {
                       $tmp['description'] = 'Parallelausgabe';
                }
            }
            // exclude DNB records
            if (isset($tmp['id']) && strpos($tmp['id'], 'DE-600') === FALSE) {
                $related[] = $tmp;
            }

        }
        return $related;
    }


    /**
     * Get ILLContent.
     *
     * @return string 
     */
    public function getILLContent()
    {
return null;
      if ($this->isFree()) {
          return "keine Fernleihe, frei verfügbar";
      } else {
      return "<span class='checkedout'><a class='external-link' target='_blank' href='https://fernleihe.boss2.bsz-bw.de/InterlendingRecord/". $this->getUniqueID() . "?isil[]=DE-25'>Fernleihbutton</a></span>";
      }
    }

    /**
     * Get ILLContent.
     *
     * @return string 
     */
    public function getILLLink()
    {
         return "https://fernleihe.boss2.bsz-bw.de/InterlendingRecord/". $this->getUniqueID() . "/ILLForm?isil[]=DE-25";
         // return "https://fernleihe.boss2.bsz-bw.de/Shibboleth.sso/Login?entityID=https://mylogin.uni-freiburg.de/shibboleth&target=https://fernleihe.boss2.bsz-bw.de/InterlendingRecord/". $this->getUniqueID() . "/ILLForm?isil[]=DE-25";

    }

    /**
     * Get Query for holdings based on isn, author, title, year .. 
     *
     * @return string 
     */
    public function getHoldingsQuery()
    {

       // Regel um die Query zu bauen 
       /*
        if ($this->driver->isArticle() || $this->driver->isJournal()
                || $this->driver->isNewspaper()
            ) {
            // prefer ZDB ID
            if (!empty($zdb)) {
                $this->holding->setZdbId($zdb);
            } else {
                $this->holding->setIsxns($this->driver->getCleanISSN());
            }
            // use ISSN and year
        } elseif (!empty($isbn)) {
            // use ISBN and year            
            $this->holding->setIsxns($isbn)
                            ->setYear($year);
        } else {
            // use title and author and year
            $this->holding->setTitle($this->driver->getTitle())
                          ->setAuthor($this->driver->getPrimaryAuthor())
                          ->setYear($year);
        }

       */
       $query = null;

       // build query for ISBN or ISSN 
       $isbn = $this->getCleanISBN();
       if (!is_array($isbn)) {
           $isbn = (array)$isbn;
       }
       $issn = $this->getCleanISSN();
       if (!is_array($issn)) {
           $issn = (array)$issn;
       }
       $temp = array_merge($isbn,$issn);
       foreach ($temp as $isxn) {
          // strip non numeric chars
          $isxn = preg_replace('/[^0-9]/', '', $isxn);
          if (strlen($isxn) > 0 && is_numeric($isxn)) {
              $isxns[] = $isxn;
          }
       }
       $isxns = array_unique($isxns);
       $year = $this->getPublicationDates();
       $primauthor = $this->getPrimaryAuthor();
       $shortTitle = $this->getFirstFieldValue('245', array('a'), false);
       // remove sorting char 
       if (strpos($shortTitle, '@') !== false) {
           $occurrence = strpos($shortTitle, '@');
           $shortTitle = substr_replace($shortTitle, '', $occurrence, 1);
       }
       $zdbid = $this->getZdbId();

       if ($this->isArticle() || $this->isJournal()
                || $this->isNewspaper()
            ) {
            // prefer ZDB ID
            if (isset($zdbid)) {
                $query[] = new \VuFindSearch\Query\Query($zdbid,'zdb_id');
            } else {
                if (isset($isxns)) {
                   $params2 = implode(' OR ',$isxns);
                   $query[] = new \VuFindSearch\Query\Query($params2,'isn');
                } 
            }
       } elseif (isset($isxns)) {
            $params2 = implode(' OR ',$isxns);
            $query[] = new \VuFindSearch\Query\Query($params2,'isn');
            if (isset($year)) {
                $params2 = implode(' OR ',$year);
                $query[] = new \VuFindSearch\Query\Query($year,'publish_date');
            }
       } else {
            if (isset($primauthor)) {
                $query[] = new \VuFindSearch\Query\Query($primauthor,'author');
            }
            if (isset($shortTitle)) {
                $query[] = new \VuFindSearch\Query\Query($shortTitle,'title');
            }
            if (isset($year)) {
                $params2 = implode(' OR ',$year);
                $query[] = new \VuFindSearch\Query\Query($year,'publish_date');
            }
       }

       // group queries
       $search = new \VuFindSearch\Query\QueryGroup('AND',$query);

       return ($search);
    }

    /**
     * Get Query for holdings based on isn, author, title, year .. 
     * @param $holdings extrated holdings
     *
     * @return void 
     */
    public function setHoldings($holdings)
    {
       $this->holdings = $holdings;
    }

    /**
     * Query webservice to get SWB hits with the same
     * <ul>
     * <li>ISSN or ISBN (preferred)</li>
     * <li>Title, author and year (optional)</li>
     * </ul>
     * Found PPNs are added to ppns array and can be accessed by other methods. 
     *  
     * @return boolean
     */
    protected function queryWebservice()
    {
        if (isset($this->holdings)) {
                // search for local available PPNs
                foreach ($this->holdings as $ppn => $holding) {
                    foreach ($holding as $entry) {
                        if (isset($entry['isil']) && in_array($entry['isil'], $this->getIsils())) {
                            // save PPN
                            $this->ppns[] = $ppn;
                            $this->libraries[] = $entry['isil'];
                        }

                    }
                }
            }
            // if no locally available ppn found, just take the first one
            if (count($this->ppns) < 1 && isset($this->holdings)) {
                reset($this->holdings);
                $this->ppns[] = key($this->holdings);
            }


        // check if any of the isils from webservic matches local isils
        if (is_array($this->libraries) && count($this->libraries) > 0) {
            return true;
        }
        return false;


return $this->checkHoldingsLocal();
// ToDo fix


        // set up query params
        $isbns = $this->getCleanISBN();
        $years = $this->getPublicationDates();
        $zdb = $this->tryMethod('getZdbId');
        $year = array_shift($years);



        if ($this->isArticle() || $this->isJournal()
                || $this->isNewspaper()
            ) {
            // prefer ZDB ID
            if (!empty($zdb)) {
                $this->holding->setZdbId($zdb);
            } else {
                $this->holding->setIsxns($this->driver->getCleanISSN());
            }
            // use ISSN and year
        } elseif (is_array($isbns) && count($isbns) > 0) {
            // use ISBN and year            
            $this->holding->setIsxns($isbns)
                            ->setYear($year);
        } else {
            // use title and author and year
            $this->holding->setTitle($this->driver->getTitle())
                          ->setAuthor($this->driver->getPrimaryAuthor())
                          ->setYear($year);
        }
        // check query and fire
        if ($this->holding->checkQuery()) {
            $result = $this->holding->query();
            // check if any ppn is available locally

            if (isset($result['holdings'])) {
                // search for local available PPNs
                foreach ($result['holdings'] as $ppn => $holding) {
                    foreach ($holding as $entry) {
                        if (isset($entry['isil']) && in_array($entry['isil'], $this->getIsils())) {
                            // save PPN
                            $this->ppns[] = '(DE-576)'.$ppn;
                            $this->libraries[] = $entry['isil'];
                        }

                    }
                }
            }
            // if no locally available ppn found, just take the first one
            if (count($this->ppns) < 1 && isset($result['holdings'])) {
                reset($result['holdings']);
                $this->ppns[] = '(DE-576)'.key($result['holdings']);
            }

        }

        // check if any of the isils from webservic matches local isils
        if (is_array($this->libraries) && count($this->libraries) > 0) {
            return true;
        }
        return false;
    }


    /**
     * Check if the item should have an ill button
     * @return boolean
     */
    public function isAvailableForInterlending()
    {
        // items marked as free
        $f856 = $this->getFieldArray([856 => 'z']);
        if ($this->isFree()) {
            return false;
        }
        //Missed (stolen) books can be Ordered even if they are available at current library
        if ($this->atCurrentLibrary) {
            $f924 = $this->tryMethod('getField924');
            // missed books can always be ordered, even if available locally. 
            if (count($f924) > 0) {
                // for each of the local isils
                foreach ($this->getIsils() as $isil) {
                    if (array_key_exists($isil, $f924) && isset($f924[$isil]['9']) && ($f924[$isil]['9'] == 'e' || $f924[$isil]['9'] == 'v')) {
                        // library has marked this item as missed
                        return true;
                    }
                }
            }
        }
        // printed journals - show hint
        else if ($this->isArticle() || $this->isJournal()) {
            return true;
        }
        // ebooks - always available
        else if ($this->isEBook() && $this->getNetwork() == 'SWB') {
            // evaluate ill indicator
            $f924 = $this->tryMethod('getField924');
            foreach ($f924 as $field) {
                if (isset($field['d']) && ($field['d'] == 'e'
                       || $field['d'] == 'b'
                       // k is deprecated but might still be used
                       || $field['d'] == 'k') ) {
                    return true;
                }
            }
            // eBooks from other networks do not have 924 and can't be ordered. 
            return false;
        }
        // Books - always available 
        else if (!$this->isAtCurrentLibrary(true) && !$this->isArticle() && !$this->isEBook()) {
            return true;
        }
        return false;
    }

    /**
     * Get ZDB ID if available
     * 
     * @return string
     */
    public function getZdbId()
    {
        $zdb = '';
        $consortial = $this->getConsortialIDs();
        foreach ($consortial as $id) {
            if (($pos = strpos($id, 'ZDB')) !== FALSE) {
                $zdb = substr($id, $pos+3);
            }
        }
        // Pull ZDB ID out of recurring field 016
        foreach ($this->getMarcRecord()->getFields('016') as $field) {
            $isil = $data = '';
            foreach ($field->getSubfields() as $subfield) {
                if ($subfield->getCode() == 'a') {
                    $data = $subfield ->getData();
                } elseif($subfield->getCode() == '2') {
                    $isil = $subfield->getData();
                }
            }
            if ($isil == 'DE-600') {
                $zdb = $data;
            }
        }
        return $zdb;
    }


}
