<?php
/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace VuFind\RecordDriver;
use VuFind\Code\ISBN;

/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
 *
 * This should be used as the base class for all Solr-based record models.
 *
 * @category                                     VuFind2
 * @package                                      RecordDrivers
 * @author                                       Demian Katz <demian.katz@villanova.edu>
 * @license                                      http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link                                         http://vufind.org/wiki/vufind2:record_drivers Wiki
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class RDSIndex extends SolrMarc
{
    /**
     * These Solr fields should be used for snippets if available (listed in order
     * of preference).
     *
     * @var array
     */
    protected $preferredSnippetFields = array(
    'contents', 'topic'
    );

    /**
     * These Solr fields should NEVER be used for snippets.  (We exclude author
     * and title because they are already covered by displayed fields; we exclude
     * spelling because it contains lots of fields jammed together and may cause
     * glitchy output; we exclude ID because random numbers are not helpful).
     *
     * @var array
     */
    protected $forbiddenSnippetFields = array(
    'author', 'author-letter', 'title', 'title_short', 'title_full',
    'title_full_unstemmed', 'title_auth', 'title_sub', 'spelling', 'id',
    'ctrlnum'
    );

    /**
     * These are captions corresponding with Solr fields for use when displaying
     * snippets.
     *
     * @var array
     */
    protected $snippetCaptions = array();

    /**
     * Should we highlight fields in search results?
     *
     * @var bool
     */
    protected $highlight = false;

    /**
     * Should we include snippets in search results?
     *
     * @var bool
     */
    protected $snippet = false;

    /**
     * Hierarchy driver plugin manager
     *
     * @var \VuFind\Hierarchy\Driver\PluginManager
     */
    protected $hierarchyDriverManager = null;

    /**
     * Hierarchy driver for current object
     *
     * @var \VuFind\Hierarchy\Driver\AbstractBase
     */
    protected $hierarchyDriver = null;

    /**
     * Highlighting details
     *
     * @var array
     */
    protected $highlightDetails = array();

    /**
     * Constructor
     *
     * @param \Zend\Config\Config $mainConfig     VuFind main configuration (omit for
     * built-in defaults)
     * @param \Zend\Config\Config $recordConfig   Record-specific configuration file
     * (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config $searchSettings Search-specific configuration file
     */
    public function __construct($mainConfig = null, $recordConfig = null,
        $searchSettings = null
    ) {
        // Turn on highlighting/snippets as needed:
        $this->highlight = !isset($searchSettings->General->highlighting)
        ? false : $searchSettings->General->highlighting;
        $this->snippet = !isset($searchSettings->General->snippets)
        ? false : $searchSettings->General->snippets;

        // Load snippet caption settings:
        if (isset($searchSettings->Snippet_Captions)
            && count($searchSettings->Snippet_Captions) > 0
        ) {
            foreach ($searchSettings->Snippet_Captions as $key => $value) {
                $this->snippetCaptions[$key] = $value;
            }
        }
        parent::__construct($mainConfig, $recordConfig);
    }

    /**
     * Add highlighting details to the object.
     *
     * @param array $details Details to add
     *
     * @return void
     */
    public function setHighlightDetails($details)
    {
        $this->highlightDetails = $details;
    }

    /**
     * Get access restriction notes for the record.
     *
     * @return array
     */
    public function getAccessRestrictions()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     */
    public function getAllSubjectHeadings()
    {
        $topic = isset($this->fields['ct']) ? $this->fields['ct'] : array();

        // The Solr index doesn't currently store subject headings in a broken-down
        // format, so we'll just send each value as a single chunk.  Other record
        // drivers (i.e. MARC) can offer this data in a more granular format.
        $retval = array();
        foreach ($topic as $t) {
            $retval[] = array($t);
        }
        return $retval;
    }

    /**
     * Get all record links related to the current record. Each link is returned as
     * array.
     * NB: to use this method you must override it.
     * Format:
     * <code>
     * array(
     *        array(
     *               'title' => label_for_title
     *               'value' => link_name
     *               'link'  => link_URI
     *        ),
     *        ...
     * )
     * </code>
     *
     * @return null|array
     */
    public function getAllRecordLinks()
    {
        return null;
    }

    /**
     * Get award notes for the record.
     *
     * @return array
     */
    public function getAwards()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get notes on bibliography content.
     *
     * @return array
     */
    public function getBibliographyNotes()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get text that can be displayed to represent this record in
     * breadcrumbs.
     *
     * @return string Breadcrumb text to represent this record.
     */
    public function getBreadcrumb()
    {
        return $this->getShortTitle();
    }

    /**
     * Get the call number associated with the record (empty string if none).
     *
     * @return string
     */
    public function getCallNumber()
    {
        // Use the callnumber-a field from the Solr index; the plain callnumber
        // field is normalized to have no spaces, so it is unsuitable for display.
        return isset($this->fields['si']) ?
        $this->fields['si'] : '';
    }

    /**
     * Get just the base portion of the first listed ISSN (or false if no ISSNs).
     *
     * @return mixed
     */
    public function getCleanISSN()
    {
        $issns = $this->getISSNs();
        if (empty($issns)) {
            return false;
        }
        $issn = $issns[0];
        if ($pos = strpos($issn, ' ')) {
            $issn = substr($issn, 0, $pos);
        }
        return $issn;
    }

    /**
     * Get just the first listed OCLC Number (or false if none available).
     *
     * @return mixed
     */
    public function getCleanOCLCNum()
    {
        $nums = $this->getOCLC();
        return empty($nums) ? false : $nums[0];
    }

    /**
     * Get the main corporate author (if any) for the record.
     *
     * @return string
     */
    public function getCorporateAuthor()
    {
        // Not currently stored in the Solr index
        return null;
    }

    /**
     * Get the date coverage for a record which spans a period of time (i.e. a
     * journal).  Use getPublicationDates for publication dates of particular
     * monographic items.
     *
     * @return array
     */
    public function getDateSpan()
    {
        return isset($this->fields['dateSpan']) ?
        $this->fields['dateSpan'] : array();
    }

    /**
     * Deduplicate author information into associative array with main/corporate/
     * secondary keys.
     *
     * @return array
     */
    /*    public function getDeduplicatedAuthors()
    {
        $authors = array(
        'main' => $this->getPrimaryAuthor(),
        'corporate' => $this->getCorporateAuthor(),
        'secondary' => $this->getSecondaryAuthors()
        );

        // The secondary author array may contain a corporate or primary author;
        // let's be sure we filter out duplicate values.
        $duplicates = array();
        if (!empty($authors['main'])) {
            $duplicates[] = $authors['main'];
        }
        if (!empty($authors['corporate'])) {
            $duplicates[] = $authors['corporate'];
        }
        if (!empty($duplicates)) {
            $authors['secondary'] = array_diff($authors['secondary'], $duplicates);
        }

        return $authors;
    }
    */

    /**
     * Get the edition of the current record.
     *
     * @return string
     */
    public function getEdition()
    {
        return isset($this->fields['ausgabe'][0]) ?
        $this->fields['ausgabe'][0] : '';
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     */
    public function getFindingAids()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get an array of all the formats associated with the record.
     *
     * @return array
     */
    public function getFormats()
    {
        return isset($this->fields['medieninfo']) ? $this->fields['medieninfo'] : array();
    }

    /**
     * Get general notes on the record.
     *
     * @return array
     */
    public function getGeneralNotes()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get a highlighted author string, if available.
     *
     * @return string
     */
    public function getHighlightedAuthor()
    {
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        return (isset($this->highlightDetails['au'][0]))
        ? $this->highlightDetails['au'][0] : '';
    }

    /**
     * Get a string representing the last date that the record was indexed.
     *
     * @return string
     */
    public function getLastIndexed()
    {
        return isset($this->fields['udate'])
        ? $this->fields['udate'] : '';
    }

    /**
     * Given a Solr field name, return an appropriate caption.
     *
     * @param string $field Solr field name
     *
     * @return mixed        Caption if found, false if none available.
     */
    public function getSnippetCaption($field)
    {
        return isset($this->snippetCaptions[$field])
        ? $this->snippetCaptions[$field] : false;
    }

    /**
     * Pick one line from the highlighted text (if any) to use as a snippet.
     *
     * @return mixed False if no snippet found, otherwise associative array
     * with 'snippet' and 'caption' keys.
     */
    public function getHighlightedSnippet()
    {
        // Only process snippets if the setting is enabled:
        if ($this->snippet) {
            // First check for preferred fields:
            foreach ($this->preferredSnippetFields as $current) {
                if (isset($this->highlightDetails[$current][0])) {
                    return array(
                    'snippet' => $this->highlightDetails[$current][0],
                    'caption' => $this->getSnippetCaption($current)
                            );
                }
            }

            // No preferred field found, so try for a non-forbidden field:
            if (isset($this->highlightDetails)
                && is_array($this->highlightDetails)
            ) {
                foreach ($this->highlightDetails as $key => $value) {
                    if (!in_array($key, $this->forbiddenSnippetFields)) {
                        return array(
                        'snippet' => $value[0],
                        'caption' => $this->getSnippetCaption($key)
                                );
                    }
                }
            }
        }

        // If we got this far, no snippet was found:
        return false;
    }

    /**
     * Get a highlighted title string, if available.
     *
     * @return string
     */
    public function getHighlightedTitle()
    {
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        return (isset($this->highlightDetails['ti'][0]))
        ? $this->highlightDetails['ti'][0] : '';
    }

    /**
     * Get the institutions holding the record.
     *
     * @return array
     */
    public function getInstitutions()
    {
        return isset($this->fields['zj'])
        ? $this->fields['zj'] : array();
    }

    /**
     * Get an array of all ISBNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISBNs()
    {
        // If ISBN is in the index, it should automatically be an array... but if
        // it's not set at all, we should normalize the value to an empty array.
        return isset($this->fields['sb']) && is_array($this->fields['sb']) ?
        $this->fields['sb'] : array();
    }

    /**
     * Get an array of all ISSNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISSNs()
    {
        // If ISSN is in the index, it should automatically be an array... but if
        // it's not set at all, we should normalize the value to an empty array.
        return isset($this->fields['ss']) && is_array($this->fields['ss']) ?
        $this->fields['ss'] : array();
    }

    /**
     * Get an array of all the languages associated with the record.
     *
     * @return array
     */
    public function getLanguages()
    {
        return isset($this->fields['la']) ?
        $this->fields['la'] : array();
    }

    /**
     * Get a LCCN, normalised according to info:lccn
     *
     * @return string
     */
    public function getLCCN()
    {
        // Get LCCN from Index
        $raw = isset($this->fields['lccn']) ? $this->fields['lccn'] : '';

        // Remove all blanks.
        $raw = preg_replace('{[ \t]+}', '', $raw);

        // If there is a forward slash (/) in the string, remove it, and remove all
        // characters to the right of the forward slash.
        if (strpos($raw, '/') > 0) {
            $tmpArray = explode("/", $raw);
            $raw = $tmpArray[0];
        }
        /* If there is a hyphen in the string:
        a. Remove it.
        b. Inspect the substring following (to the right of) the (removed)
        hyphen. Then (and assuming that steps 1 and 2 have been carried out):
        i.  All these characters should be digits, and there should be
        six or less.
        ii. If the length of the substring is less than 6, left-fill the
        substring with zeros until  the length is six.
        */
        if (strpos($raw, '-') > 0) {
            // haven't checked for i. above. If they aren't all digits, there is
            // nothing that can be done, so might as well leave it.
            $tmpArray = explode("-", $raw);
            $raw = $tmpArray[0] . str_pad($tmpArray[1], 6, "0", STR_PAD_LEFT);
        }
        return $raw;
    }

    /**
     * Get an array of newer titles for the record.
     *
     * @return array
     */
    public function getNewerTitles()
    {
        return isset($this->fields['title_new']) ?
        $this->fields['title_new'] : array();
    }

    /**
     * Get the OCLC number of the record.
     *
     * @return array
     */
    public function getOCLC()
    {
        return isset($this->fields['oclc_num']) ?
        $this->fields['oclc_num'] : array();
    }

    /**
     * Support method for getOpenURL() -- pick the OpenURL format.
     *
     * @return string
     */
    protected function getOpenURLFormat()
    {
        // If we have multiple formats, Book, Journal and Article are most
        // important...
        $formats = $this->getFormats();
        if (in_array('book', $formats)) {
            return 'Book';
        } else if (in_array('article', $formats)) {
            return 'Article';
        } else if (in_array('journal', $formats)) {
            return 'Journal';
        } else if (isset($formats[0])) {
            return $formats[0];
        } else if (strlen($this->getCleanISSN()) > 0) {
            return 'Journal';
        }
        return 'Book';
    }

    /**
     * Get the COinS identifier.
     *
     * @return string
     */
    protected function getCoinsID()
    {
        // Get the COinS ID -- it should be in the OpenURL section of config.ini,
        // but we'll also check the COinS section for compatibility with legacy
        // configurations (this moved between the RC2 and 1.0 releases).
        if (isset($this->mainConfig->OpenURL->rfr_id)
            && !empty($this->mainConfig->OpenURL->rfr_id)
        ) {
            return $this->mainConfig->OpenURL->rfr_id;
        }
        if (isset($this->mainConfig->COinS->identifier)
            && !empty($this->mainConfig->COinS->identifier)
        ) {
            return $this->mainConfig->COinS->identifier;
        }
        return 'vufind.svn.sourceforge.net';
    }

    /**
     * Get default OpenURL parameters.
     *
     * @return array
     */
    protected function getDefaultOpenURLParams()
    {
        // Get a representative publication date:
        $pubDate = $this->getPublicationDates();
        $pubDate = empty($pubDate) ? '' : $pubDate[0];

        // Start an array of OpenURL parameters:
        return array(
        'ctx_ver' => 'Z39.88-2004',
        'ctx_enc' => 'info:ofi/enc:UTF-8',
        'rfr_id' => 'info:sid/' . $this->getCoinsID() . ':generator',
        'rft.title' => $this->getTitle(),
        'rft.date' => $pubDate
                );
    }

    /**
     * Get OpenURL parameters for a book.
     *
     * @return array
     */
    protected function getBookOpenURLParams()
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:book';
        $params['rft.genre'] = 'book';
        $params['rft.btitle'] = $params['rft.title'];
        $series = $this->getSeries();
        if (count($series) > 0) {
            // Handle both possible return formats of getSeries:
            $params['rft.series'] = is_array($series[0]) ?
            $series[0]['name'] : $series[0];
        }
        $params['rft.au'] = $this->getPrimaryAuthor();
        $publishers = $this->getPublishers();
        if (count($publishers) > 0) {
            $params['rft.pub'] = $publishers[0];
        }
        $params['rft.edition'] = $this->getEdition();
        $params['rft.isbn'] = (string)$this->getCleanISBN();
        return $params;
    }

    /**
     * Get OpenURL parameters for an article.
     *
     * @return array
     */
    protected function getArticleOpenURLParams()
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        $params['rft.genre'] = 'article';
        $params['rft.issn'] = (string)$this->getCleanISSN();
        // an article may have also an ISBN:
        $params['rft.isbn'] = (string)$this->getCleanISBN();
        $params['rft.volume'] = $this->getContainerVolume();
        $params['rft.issue'] = $this->getContainerIssue();
        $params['rft.spage'] = $this->getContainerStartPage();
        // unset default title -- we only want jtitle/atitle here:
        unset($params['rft.title']);
        $params['rft.jtitle'] = $this->getContainerTitle();
        $params['rft.atitle'] = $this->getTitle();
        $params['rft.au'] = $this->getPrimaryAuthor();

        $params['rft.format'] = 'Article';
        $langs = $this->getLanguages();
        if (count($langs) > 0) {
            $params['rft.language'] = $langs[0];
        }
        return $params;
    }

    /**
     * Get OpenURL parameters for an unknown format.
     *
     * @param string $format Name of format
     *
     * @return array
     */
    protected function getUnknownFormatOpenURLParams($format)
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:dc';
        $params['rft.creator'] = $this->getPrimaryAuthor();
        $publishers = $this->getPublishers();
        if (count($publishers) > 0) {
            $params['rft.pub'] = $publishers[0];
        }
        $params['rft.format'] = $format;
        $langs = $this->getLanguages();
        if (count($langs) > 0) {
            $params['rft.language'] = $langs[0];
        }
        return $params;
    }

    /**
     * Get OpenURL parameters for a journal.
     *
     * @return array
     */
    protected function getJournalOpenURLParams()
    {
        $params = $this->getUnknownFormatOpenURLParams('Journal');
        /* This is probably the most technically correct way to represent
        * a journal run as an OpenURL; however, it doesn't work well with
        * Zotero, so it is currently commented out -- instead, we just add
        * some extra fields and to the "unknown format" case.
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        $params['rft.genre'] = 'journal';
        $params['rft.jtitle'] = $params['rft.title'];
        $params['rft.issn'] = $this->getCleanISSN();
        $params['rft.au'] = $this->getPrimaryAuthor();
        */
        $params['rft.issn'] = (string)$this->getCleanISSN();

        // Including a date in a title-level Journal OpenURL may be too
        // limiting -- in some link resolvers, it may cause the exclusion
        // of databases if they do not cover the exact date provided!
        unset($params['rft.date']);

        // If we're working with the SFX resolver, we should add a
        // special parameter to ensure that electronic holdings links
        // are shown even though no specific date or issue is specified:
        if (isset($this->mainConfig->OpenURL->resolver)
            && strtolower($this->mainConfig->OpenURL->resolver) == 'sfx'
        ) {
            $params['sfx.ignore_date_threshold'] = 1;
        }
        return $params;
    }

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     */
    public function getOpenURL()
    {
        // Set up parameters based on the format of the record:
        switch ($format = $this->getOpenURLFormat()) {
        case 'Book':
            $params = $this->getBookOpenURLParams();
            break;
        case 'Article':
            $params = $this->getArticleOpenURLParams();
            break;
        case 'Journal':
            $params = $this->getJournalOpenURLParams();
            break;
        default:
            $params = $this->getUnknownFormatOpenURLParams($format);
            break;
        }

        // Assemble the URL:
        return http_build_query($params);
    }

    /**
     * Get an array of physical descriptions of the item.
     *
     * @return array
     */
    public function getPhysicalDescriptions()
    {
        // ToDo
        return isset($this->fields['umfang']) ?
        $this->fields['umfang'] : array();
    }

    /**
     * Get the item's place of publication.
     *
     * @return array
     */
    public function getPlacesOfPublication()
    {
        // Not currently stored in the Solr index
        return isset($this->fields['pu_pp_display']) ?
        $this->fields['pu_pp_display'] : array();
    }

    /**
     * Get an array of playing times for the record (if applicable).
     *
     * @return array
     */
    public function getPlayingTimes()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get an array of previous titles for the record.
     *
     * @return array
     */
    /*    public function getPreviousTitles()
    {
        return isset($this->fields['title_old']) ?
        $this->fields['title_old'] : array();
    }
    */
    

    /**
     * Get the main author of the record.
     *
     * @return string
     */
    public function getPrimaryAuthor()
    {
        return isset($this->fields['au_display_short'][0]) ?  $this->fields['au_display_short'][0] : "" ;
    }

    /**
     * Get credits of people involved in production of the item.
     *
     * @return array
     */
    public function getProductionCredits()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get the publication dates of the record.  See also getDateSpan().
     *
     * @return array
     */
    public function getPublicationDates()
    {
        return (isset($this->fields['py']) && $this->fields['py']!="0") ?
        array($this->fields['py']) : array();
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     */
    public function getPublicationDetails()
    {
        $places = $this->getPlacesOfPublication();
        $names = $this->getPublishers();
        $dates = $this->getPublicationDates();

        $i = 0;
        $retval = array();
        while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
            // Build objects to represent each set of data; these will
            // transform seamlessly into strings in the view layer.
            $retval[] = new Response\PublicationDetails(
                isset($places[$i]) ? $places[$i] : '',
                isset($names[$i]) ? $names[$i] : '',
                isset($dates[$i]) ? $dates[$i] : ''
            );
            $i++;
        }

        return $retval;
    }

    /**
     * Get an array of publication frequency information.
     *
     * @return array
     */
    public function getPublicationFrequency()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get the publishers of the record.
     *
     * @return array
     */
    public function getPublishers()
    {
        return isset($this->fields['pu']) ?
        $this->fields['pu'] : array();
    }

    /**
     * Get an array of information about record history, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHistory()
    {
        // Not supported by the Solr index -- implement in child classes.
        return array();
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHoldings()
    {
        // Not supported by the Solr index -- implement in child classes.
        return array();
    }

    /**
     * Get an array of strings describing relationships to other items.
     *
     * @return array
     */
    public function getRelationshipNotes()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthor()).
     *
     * @return array
     */
    public function getSecondaryAuthors()
    {
        return isset($this->fields['author2']) ?
        $this->fields['author2'] : array();
    }

    /**
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     */
    public function getSeries()
    {
        // Only use the contents of the series2 field if the series field is empty
        if (isset($this->fields['orig_reihe_display']) && !empty($this->fields['orig_reihe_display'])) {
            return $this->fields['orig_reihe_display'];
        }
        return isset($this->fields['orig_ureihe_display']) ?
        $this->fields['orig_ureihe_display'] : array();
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return isset($this->fields['ti_short']) ?
        $this->fields['ti_short'] : '';
    }

    /**
     * Get the subtitle of the record.
     *
     * @return string
     */
    public function getSubtitle()
    {
        // ToDo
        return isset($this->fields['title_sub']) ?
        $this->fields['title_sub'] : '';
    }

    /**
     * Get an array of technical details on the item represented by the record.
     *
     * @return array
     */
    public function getSystemDetails()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Get an array of summary strings for the record.
     *
     * @return array
     */
    public function getSummary()
    {
        // We need to return an array, so if we have a description, turn it into an
        // array as needed (it should be a flat string according to the default
        // schema, but we might as well support the array case just to be on the safe
        // side:
        if (isset($this->fields['abstract'])
            && !empty($this->fields['abstract'])
        ) {
            return is_array($this->fields['abstract'])
            ? $this->fields['abstract'] : array($this->fields['abstract']);
        }

        // If we got this far, no description was found:
        return array();
    }

    /**
     * Get an array of note about the record's target audience.
     *
     * @return array
     */
    public function getTargetAudienceNotes()
    {
        // Not currently stored in the Solr index
        return array();
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        if ($isbn = $this->getCleanISBN()) {
            return array('isn' => $isbn, 'size' => $size);
        }
        return false;
    }

    
    /**
     * Get the statement of responsibility that goes with the title (i.e. "by John
     * Smith").
     *
     * @return string
     */
    public function getTitleStatement()
    {
        // Not currently stored in the Solr index
        return null;
    }

    /**
     * Get an array of lines from the table of contents.
     *
     * @return array
     */
    public function getTOC()
    {
        return isset($this->fields['contents'])
        ? $this->fields['contents'] : array();
    }

    /**
     * Return an array of associative URL arrays with one or more of the following
     * keys:
     *
     * <li>
     *   <ul>desc: URL description text to display (optional)</ul>
     *   <ul>url: fully-formed URL (required if 'route' is absent)</ul>
     *   <ul>route: VuFind route to build URL with (required if 'url' is absent)</ul>
     *   <ul>routeParams: Parameters for route (optional)</ul>
     *   <ul>queryString: Query params to append after building route (optional)</ul>
     * </li>
     *
     * @return array
     */
    public function getURLs()
    {
        $myurl = array();
        if (isset($this->fields['url_short'])) {
            $myurl[] = (array('url'=>$this->fields['url_short']));
            return($myurl);
        }
        return array();
    }

    /**
     * Get a hierarchy driver appropriate to the current object.  (May be false if
     * disabled/unavailable).
     *
     * @return \VuFind\Hierarchy\Driver\AbstractBase|bool
     */
    public function getHierarchyDriver()
    {
        if (null === $this->hierarchyDriver
            && null !== $this->hierarchyDriverManager
        ) {
            $type = $this->getHierarchyType();
            $this->hierarchyDriver = $type
            ? $this->hierarchyDriverManager->get($type) : false;
        }
        return $this->hierarchyDriver;
    }

    /**
     * Inject a hierarchy driver plugin manager.
     *
     * @param \VuFind\Hierarchy\Driver\PluginManager $pm Hierarchy driver manager
     *
     * @return SolrDefault
     */
    public function setHierarchyDriverManager(
        \VuFind\Hierarchy\Driver\PluginManager $pm
    ) {
        $this->hierarchyDriverManager = $pm;
        return $this;
    }

    /**
     * Get the hierarchy_top_id(s) associated with this item (empty if none).
     *
     * @return array
     */
    public function getHierarchyTopID()
    {
        return isset($this->fields['hierarchy_top_id'])
        ? $this->fields['hierarchy_top_id'] : array();
    }

    /**
     * Get the absolute parent title(s) associated with this item
     * (empty if none).
     *
     * @return array
     */
    public function getHierarchyTopTitle()
    {
        return isset($this->fields['hierarchy_top_title'])
        ? $this->fields['hierarchy_top_title'] : array();
    }

    /**
     * Get an associative array (id => title) of collections containing this record.
     *
     * @return array
     */
    public function getContainingCollections()
    {
        // If collections are disabled or this record is not part of a hierarchy, go
        // no further....
        if (!isset($this->mainConfig->Collections->collections)
            || !$this->mainConfig->Collections->collections
            || !($hierarchyDriver = $this->getHierarchyDriver())
        ) {
            return false;
        }

        // Initialize some variables needed within the switch below:
        $isCollection = $this->isCollection();
        $titles = $ids = array();

        // Check config setting for what constitutes a collection, act accordingly:
        switch ($hierarchyDriver->getCollectionLinkType()) {
        case 'All':
            if (isset($this->fields['hierarchy_parent_title'])
                && isset($this->fields['hierarchy_parent_id'])
            ) {
                $titles = $this->fields['hierarchy_parent_title'];
                $ids = $this->fields['hierarchy_parent_id'];
            }
            break;
        case 'Top':
            if (isset($this->fields['hierarchy_top_title'])
                && isset($this->fields['hierarchy_top_id'])
            ) {
                foreach ($this->fields['hierarchy_top_id'] as $i => $topId) {
                    // Don't mark an item as its own parent -- filter out parent
                    // collections whose IDs match that of the current collection.
                    if (!$isCollection
                        || $topId !== $this->fields['is_hierarchy_id']
                    ) {
                        $ids[] = $topId;
                        $titles[] = $this->fields['hierarchy_top_title'][$i];
                    }
                }
            }
            break;
        }

        // Map the titles and IDs to a useful format:
        $c = count($ids);
        $retVal = array();
        for ($i = 0; $i < $c; $i++) {
            $retVal[$ids[$i]] = $titles[$i];
        }
        return $retVal;
    }

    /**
     * Get the value of whether or not this is a collection level record
     *
     * @return bool
     */
    public function isCollection()
    {
        if (!($hierarchyDriver = $this->getHierarchyDriver())) {
            // Not a hierarchy type record
            return false;
        }

        // Check config setting for what constitutes a collection
        switch ($hierarchyDriver->getCollectionLinkType()) {
        case 'All':
            return (isset($this->fields['is_hierarchy_id']));
        case 'Top':
            return isset($this->fields['is_hierarchy_title'])
           && isset($this->fields['is_hierarchy_id'])
           && in_array(
               $this->fields['is_hierarchy_id'],
               $this->fields['hierarchy_top_id']
           );
        default:
            // Default to not be a collection level record
            return false;
        }
    }

    /**
     * Get the positions of this item within parent collections.  Returns an array
     * of parent ID => sequence number.
     *
     * @return array
     */
    public function getHierarchyPositionsInParents()
    {
        $retVal = array();
        if (isset($this->fields['hierarchy_parent_id'])
            && isset($this->fields['hierarchy_sequence'])
        ) {
            foreach ($this->fields['hierarchy_parent_id'] as $key => $val) {
                $retVal[$val] = $this->fields['hierarchy_sequence'][$key];
            }
        }
        return $retVal;
    }

    /**
     * Get a list of hierarchy trees containing this record.
     *
     * @param string $hierarchyID The hierarchy to get the tree for
     *
     * @return mixed An associative array of hierachy trees on success (id => title),
     * false if no hierarchies found
     */
    public function getHierarchyTrees($hierarchyID = false)
    {
        $hierarchyDriver = $this->getHierarchyDriver();
        if ($hierarchyDriver && $hierarchyDriver->showTree()) {
            return $hierarchyDriver->getTreeRenderer($this)
                ->getTreeList($hierarchyID);
        }
        return false;
    }

    /**
     * Get the Hierarchy Type (false if none)
     *
     * @return string|bool
     */
    public function getHierarchyType()
    {
        if (isset($this->fields['hierarchy_top_id'])) {
            $hierarchyType = isset($this->fields['hierarchytype'])
            ? $this->fields['hierarchytype'] : false;
            if (!$hierarchyType) {
                $hierarchyType = isset($this->mainConfig->Hierarchy->driver)
                ? $this->mainConfig->Hierarchy->driver : false;
            }
            return $hierarchyType;
        }
        return false;
    }

    /**
     * Return the unique identifier of this record within the Solr index;
     * useful for retrieving additional information (like tags and user
     * comments) from the external MySQL database.
     *
     * @return string Unique identifier.
     */
    public function getUniqueID()
    {
        if (!isset($this->fields['id'])) {
            throw new \Exception('ID not set!');
        }
        return $this->fields['id'];
    }

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string $format Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     *
     * @return mixed         XML, or false if format unsupported.
     */
    public function getXML($format)
    {
        // For OAI-PMH Dublin Core, produce the necessary XML:
        if ($format == 'oai_dc') {
            $dc = 'http://purl.org/dc/elements/1.1/';
            $xml = new \SimpleXMLElement(
                '<oai_dc:dc '
                . 'xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '
                . 'xmlns:dc="' . $dc . '" '
                . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
                . 'xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ '
                . 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd" />'
            );
            $xml->addChild('title', htmlspecialchars($this->getTitle()), $dc);
            $primary = $this->getPrimaryAuthor();
            if (!empty($primary)) {
                $xml->addChild('creator', htmlspecialchars($primary), $dc);
            }
            $corporate = $this->getCorporateAuthor();
            if (!empty($corporate)) {
                $xml->addChild('creator', htmlspecialchars($corporate), $dc);
            }
            foreach ($this->getSecondaryAuthors() as $current) {
                $xml->addChild('creator', htmlspecialchars($current), $dc);
            }
            foreach ($this->getLanguages() as $lang) {
                $xml->addChild('language', htmlspecialchars($lang), $dc);
            }
            foreach ($this->getPublishers() as $pub) {
                $xml->addChild('publisher', htmlspecialchars($pub), $dc);
            }
            foreach ($this->getPublicationDates() as $date) {
                $xml->addChild('date', htmlspecialchars($date), $dc);
            }
            foreach ($this->getAllSubjectHeadings() as $subj) {
                $xml->addChild(
                    'subject', htmlspecialchars(implode(' -- ', $subj)), $dc
                );
            }

            return $xml->asXml();
        }

        // Unsupported format:
        return false;
    }

    /**
     * Does the OpenURL configuration indicate that we should display OpenURLs in
     * the specified context?
     *
     * @param string $area 'results', 'record' or 'holdings'
     *
     * @return bool
     */
    public function openURLActive($area)
    {
        // Only display OpenURL link if the option is turned on and we have
        // an ISSN.  We may eventually want to make this rule more flexible.
        if (!$this->getCleanISSN()) {
            return false;
        }
        return parent::openURLActive($area);
    }

    /**
     * Get an array of strings representing citation formats supported
     * by this record's data (empty if none).  For possible legal values,
     * see /application/themes/root/helpers/Citation.php, getCitation()
     * method.
     *
     * @return array Strings representing citation formats.
     */
    public function getCitationFormats()
    {
        return array('APA', 'MLA');
    }

    /**
     * Get the title of the item that contains this record (i.e. MARC 773s of a
     * journal).
     *
     * @return string
     */
    public function getContainerTitle()
    {
        return isset($this->fields['container_title'])
        ? $this->fields['container_title'] : '';
    }

    /**
     * Get the volume of the item that contains this record (i.e. MARC 773v of a
     * journal).
     *
     * @return string
     */
    public function getContainerVolume()
    {
        return isset($this->fields['container_volume'])
        ? $this->fields['container_volume'] : '';
    }

    /**
     * Get the issue of the item that contains this record (i.e. MARC 773l of a
     * journal).
     *
     * @return string
     */
    public function getContainerIssue()
    {
        return isset($this->fields['container_issue'])
        ? $this->fields['container_issue'] : '';
    }

    /**
     * Get the start page of the item that contains this record (i.e. MARC 773q of a
     * journal).
     *
     * @return string
     */
    public function getContainerStartPage()
    {
        return isset($this->fields['container_start_page'])
        ? $this->fields['container_start_page'] : '';
    }

    /**
     * Get the end page of the item that contains this record.
     *
     * @return string
     */
    public function getContainerEndPage()
    {
        // not currently supported by Solr index:
        return '';
    }

    /**
     * Get a full, free-form reference to the context of the item that contains this
     * record (i.e. volume, year, issue, pages).
     *
     * @return string
     */
    public function getContainerReference()
    {
        return isset($this->fields['container_reference'])
        ? $this->fields['container_reference'] : '';
    }

    /**
     * Get a sortable title for the record (i.e. no leading articles).
     *
     * @return string
     */
    public function getSortTitle()
    {
        return isset($this->fields['title_sort'])
        ? $this->fields['title_sort'] : parent::getSortTitle();
    }

    /**
     * Get longitude/latitude text (or false if not available).
     *
     * @return string|bool
     */
    public function getLongLat()
    {
        return isset($this->fields['long_lat'])
        ? $this->fields['long_lat'] : false;
    }

    /**
     * Get schema.org type mapping, an array of sub-types of
     * http://schema.org/CreativeWork, defaulting to CreativeWork
     * itself if nothing else matches.
     *
     * @return array
     */
    public function getSchemaOrgFormatsArray()
    {
        $types = array();
        foreach ($this->getFormats() as $format) {
            switch ($format) {
            case 'Book':
            case 'eBook':
                $types['Book'] = 1;
                break;
            case 'Video':
            case 'VHS':
                $types['Movie'] = 1;
                break;
            case 'Photo':
                $types['Photograph'] = 1;
                break;
            case 'Map':
                $types['Map'] = 1;
                break;
            case 'Audio':
                $types['MusicAlbum'] = 1;
                break;
            default:
                $types['CreativeWork'] = 1;
            }
        }
        return array_keys($types);
    }
    /**
     * Get schema.org type mapping, expected to be a space-delimited string of
     * sub-types of http://schema.org/CreativeWork, defaulting to CreativeWork
     * itself if nothing else matches.
     *
     * @return string
     */
    public function getSchemaOrgFormats()
    {
        return implode(' ', $this->getSchemaOrgFormatsArray());
    }

    /**
     * Get the Medienicon description from index 
     * RDS
     * @return string
     */
    public function getMedienicon() 
    {
        return isset($this->fields['medienicon']) ? $this->fields['medienicon'] : '&nbsp;';
    }

    /**
     * Get an array of  authors for the record.
     * RDS
     * @return array
     */
    public function getShortAuthors()
    {
        return isset($this->fields['au_display_short']) ?
        $this->fields['au_display_short'] : array();
    }

    /**
     * Get an array of authors for the record.
     * RDS
     * @return array
     */
    public function getAuthorsLong() 
    {

        $gnd_ppn = "";
        $authors_long = array();
        if (isset($this->fields['au_display'])) {
            $arr_links = $this->fields['au_display'];
            $last_item = end($arr_links);
            foreach ($arr_links as $key => $link) {
                $gnd_ppn = "";
                $chk_link = $link;
                if (strstr($link, " ; ")) {
                    $tmp = $link;
                    $pos = strrpos($link, " ; ");
                    $gnd_ppn = substr($tmp, $pos+3);
                    $authors_long[$key]["gnd"] = $gnd_ppn;
                    $link = substr($link, '0', $pos);
                }
                if (strstr($link, "|")) {
                    $arr_link = explode(" | ", $link);
                    $authors_long[$key]["link"]=$arr_link[0];
                    $authors_long[$key]["link_text"]=$arr_link[1];
                } else {
                    $authors_long[$key]["link"]=$link;
                }
            }
        }
        return $authors_long;
    }

    /**
     * Get an array of corporations for the record.
     * RDS
     * @return array
    */
    public function getCorporation() 
    {
        $co_display = array();            
        if (isset($this->fields['co_display'])) {
            $arr_links = $this->fields['co_display'];
            $last_item = end($arr_links);
            foreach ($arr_links as $key => $link) {
                $gnd_ppn = "";
                $chk_link = $link;
                if (strstr($link, " ; ")) {
                    $tmp = $link;
                    $pos = strrpos($link, " ; ");
                    $gnd_ppn = substr($tmp, $pos+3);
                    $co_display[$key]["gnd"] = $gnd_ppn;
                    $link = substr($link, '0', $pos);
                    $link = str_replace('"', '\"', $link);
                    $co_display[$key]["link"]=$link;
                }
            }
        }
        return $co_display;
    }

    /**
     * Get the full title of the record.
     * RDS
     * @return string
     */
    public function getTitle()
    {
        return isset($this->fields['ti_long']) ?
        $this->fields['ti_long'] : '';
    }

    /**
     * Get the text of the part/section portion of the title.
     * RDS
     * @return string
     */
    public function getTitlePart()
    {
        $arr_link = "";
        if (isset($this->fields['ti_part'])) {
            $arr_link = explode(" ; ", $this->fields['ti_part']);
        }
        return $arr_link;
    }
    /**
     * Get part of 'f-Satz' from series
     * RDS
     * @return string
     */
    public function getTitleLongf()
    {
        return isset($this->fields['ti_long_f']) ? 
        $this->fields['ti_long_f'] : '';
    }

    /**
     * Get second part of 'f-Satz' from series
     * RDS
     * @return string
     */
    public function getTitleLongfsec()
    {
        return isset($this->fields['ti_long_f_second']) ? $this->fields['ti_long_f_second'] : '';
    }

    /**
     * Get short form of title 
     * RDS
     * @return string
     */
    public function getTitleCut()
    {
        return isset($this->fields['ti_cut']) ? $this->fields['ti_cut'] : '';
    }
    /**
     * Get main Title  
     * RDS
     * @return string
     */      
    public function getTitleMain()
    {
        return isset($this->fields['ht']) ? implode($this->fields['ht']) : '';
    }

    /**
     * Get main heading of title 
     * RDS
     * @return string
     */
    public function getAst()
    {
        return isset($this->fields['ast']) ? implode($this->fields['ast']) : '';
    }

}
