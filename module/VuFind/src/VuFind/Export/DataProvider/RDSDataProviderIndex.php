<?php
/** 
 * RDSDataProviderIndex exporter for rds data
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
 * RDSDataProviderIndex exporter for rds data
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSDataProviderIndex implements RDSDataProvider
{

    protected $fields;
    protected $recordDriver;

    /**
     * Get.
     *
     * @param string $indexFields  fields of the index 
     * @param string $recordDriver current driver
     */
    public function __construct($indexFields, $recordDriver) 
    {
        $this->fields = $indexFields;
        $this->recordDriver = $recordDriver;

        $this->mediatypeByMedieninfo = array(
        RDSDataProvider::MEDIATYPE_BOOK         => array('book',
                                                                 'Book'),
        RDSDataProvider::MEDIATYPE_ARTICLE         => array('article',
                                                                 'Article'),
        RDSDataProvider::MEDIATYPE_JOURNAL         => array('journal',
                                                                 'zeitschrift',
                                                                 'Zeitung'),
        RDSDataProvider::MEDIATYPE_BINARY         => array('binary'),
        RDSDataProvider::MEDIATYPE_PROCEEDING     => array('gkko'),
        RDSDataProvider::MEDIATYPE_AUDIO         => array('audio'),
        RDSDataProvider::MEDIATYPE_VIDEO         => array('video'),
        RDSDataProvider::MEDIATYPE_MAP             => array('map'),
        RDSDataProvider::MEDIATYPE_MUDRUCK         => array('mudruck'),
        );
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getMediatype() 
    {
        $mediatypes = array('');

        $currentMedieninfos = $this->getField('medieninfo');

        $currentOpenurlgenres = $this->getField('openurlgenre');
        foreach ($this->mediatypeByMedieninfo as $mediatype => $medieninfos) {
            if (count(array_intersect($currentMedieninfos, $medieninfos)) > 0) {
                $mediatypes[] = $mediatype;
            }
        }

        if (count(array_intersect(array('hs'), $currentMedieninfos)) > 0) {
            $isbns = $this->getISBNs();
            if (empty($isbns)) {
                $mediatypes[] = RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_UNPUBLISHED;
            } else {
                $mediatypes[] = RDSDataProvider::MEDIATYPE_HOCHSCHULSCHRIFT_PUBLISHED;
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
     * Get.
     *
     * @return string
     */
    public function getID() 
    {
        return isset($this->fields['ppn']) ? $this->fields['ppn'] : '&nbsp;';
    }

    /**
     * Get.
     *
     * @param string $type default title short
     *
     * @return string
     */
    public function getTitle($type = RDSDataProvider::TITLE_SHORT) 
    {

        switch($type) {
        case RDSDataProvider::TITLE_SHORT:
            return $this->getField('ti_short');
        case RDSDataProvider::TITLE_LONG:
            return $this->getField('ti_long');
        case RDSDataProvider::TITLE_HT:
            return $this->getField('ht');
        default:
            return '';
        }
    }

    /**
     * Get.
     *
     * @param string $type default author short
     *
     * @return string
     */
    public function getAuthor($type = RDSDataProvider::AUTHORS_SHORT) 
    {

        switch($type) {
        case RDSDataProvider::AUTHORS_SHORT:
            return isset($this->fields['au_display_short']) ? $this->fields['au_display_short'] : '';
        case RDSDataProvider::AUTHORS_LONG:
            return isset($this->fields['au_display']) ? $this->fields['au_display'] : '';
        default:
            return array();
        }
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getPublishingYear() 
    {
        return $this->getField('py_display');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getISBNs()
    {
        $isbns = array();

        foreach ($this->getField('isbn_display') as $isbn) {
            $isbns[] = new ISBN_('', $isbn);
        }

        return $isbns;
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getISSNs() 
    {
        $issns = array();

        foreach ($this->getField('issn_display') as $issn) {
            $issns[] = new ISSN('', $issn);
        }

        return $issns;
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getLanguages() 
    {
        return $this->getField('la');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getPublishingPlace() 
    {
        return $this->getField('pp_display');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getPublisher() 
    {
        return $this->getField('pu');
    }
   
    /**
     * Get.
     *
     * @return string
     */ 
    public function getPages() 
    {
        $resultPages = array();
        
        $umfaenge = $this->getField('umfang');
        
        foreach ($umfaenge as $umfang) {
            $result = preg_match('/[0-9]+\\sS/', $umfang, $pages);
        
            if (count($pages) > 0) {
                $totalPages = substr($pages[0], 0, -2);
                $resultPages[] = new Page($umfang, $totalPages, null, null);
            } else {
                $resultPages[] = new Page($umfang);
            }
        }
        
        return $resultPages;
    }

    /**
     * Get.
     *
     * @param string $type default title short
     *
     * @return string
     */
    public function getFootnotes($type = RDSDataProvider::FOOTNOTES_ALL) 
    {
        $footnotes = array();

        if ($type && RDSDataProvider::FOOTNOTES == RDSDataProvider::FOOTNOTES) {
            $footnotes['fn'] = $this->getField('fn_display');
        }

        if ($type && RDSDataProvider::FOOTNOTES_ENTHWERKE == RDSDataProvider::FOOTNOTES_ENTHWERKE) {
            $footnotes['enthWerke'] = $this->getField('fn_enthWerke');
        }

        if ($type && RDSDataProvider::FOOTNOTES_EBOOKS == RDSDataProvider::FOOTNOTES_EBOOKS) {
            $footnotes['ebooks'] = $this->getField('fn_ebooks');
        }

        if ($type && RDSDataProvider::FOOTNOTES_INTERPRET == RDSDataProvider::FOOTNOTES_INTERPRET) {
            $footnotes['interpret'] = $this->getField('fn_interpret');
        }

        return $footnotes;
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getEdition() 
    {
        return $this->getField('ausgabe');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getVolume() 
    {
        return $this->getField('bnd_display');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getAbstract() 
    {
        return $this->getField('abstract');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getSchool() 
    {
        return $this->getField('hss');
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getKeywords() 
    {
        $keywords = array();

        if (isset($this->fields['ct_display'])) {
            $arr_ct = $this->fields['ct_display'];
            foreach ($arr_ct as $ct_string) {
                $keywords = array_merge($keywords, explode(" , ", $ct_string));
            }
        }

        return $keywords;
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getPersistentLink() 
    {
        return $this->recordDriver->getPersistentLink();
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getDOI() 
    {
        return array();
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getDataSource() 
    {
        return array();
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getJournal() 
    {
        return array();
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getUebergeordneteWerke() 
    {
        $uebergeordneteWerke = $this->getField('band_werk');

        return $this->getArrayOfArrays($uebergeordneteWerke);
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getSeries() 
    {
        $series = $this->getField('serie_tit');
        if (empty($series)) {
            $series = $this->getField('ungez_reihe');
        }

        return $this->getArrayOfArrays($series);
    }

    /**
     * Get.
     *
     * @return string
     */ 
    public function getFulltextLinks() 
    {
        return $this->getField('url_short');
    }
    
    /**
     * Get.
     *
     * @param array $items items 
     *
     * @return string
     */
    protected function getArrayOfArrays($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $item) {
                if (strstr($item, "|")) {
                    $id_title_volume = explode(" | ", $item);
                    
                    $id = isset($id_title_volume[0]) ? $id_title_volume[0] : ''; 
                    $title = isset($id_title_volume[1]) ? $id_title_volume[1] : '';
                    $volume = isset($id_title_volume[2]) ? $id_title_volume[2] : '';
                    
                    $result[] = array(
                    'id' => $id,
                    'title' => $title,
                    'volume' => $volume
                    );
                } else {
                    $result[] = array(
                    'id' => '',
                    'title' => $item,
                    'volume' => ''
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getMARC() 
    {
        return $this->fields['fullrecord'];
    }

    /**
     * Get.
     *
     * @return string
     */
    public function getIssue() 
    {
        return array();
    }

    /**
     * Get array of fields.
     *
     * @param string $fieldName name of the field
     *
     * @return string
     */
    protected function getField($fieldName) 
    {
        $arrayOfFieldValues = null;

        if (isset($this->fields[$fieldName])) {
            $fieldValue =  $this->fields[$fieldName];
        } else {
            return array();
        }

        if (is_array($fieldValue)) {
            $arrayOfFieldValues = $fieldValue;
        } else {
            $arrayOfFieldValues = array($fieldValue);
        }

        return $arrayOfFieldValues;
    }


    /**
     * For debugging only.
     *
     * @return string
     */
    public function getFields() 
    {
        $debug_out = "";

        $field_list = Array('umfang','ppn', 'isbn_display','issn_display');
        $field_list = array_merge($field_list, Array('medieninfo', 'mt_facet', 'medienicon', 'tz', 'hss'));
        $field_list = array_merge($field_list, Array('ti','ti_display','ti_short','ti_long','ti_long_f','ti_long_f_second','ti_part'));
        $field_list = array_merge($field_list, Array('isbn_display','issn_display'));
        $field_list = array_merge($field_list, Array('unterreihe','ht','nt','ast','est','ta','hss','sekundaer'));
        $field_list = array_merge($field_list, Array('au', 'au_display', 'au_display_short', 'au_facet', 'au_register', 'ai'));
        $field_list = array_merge($field_list, Array('pu','pu_pp_display','pp','pp_display','pp_auto'));
        $field_list = array_merge($field_list, Array('py','py_num','py_facet','py_display'));
        $field_list = array_merge($field_list, Array('ct','ct_display','ct_register','ct_facet_lower', 'ct_facet'));
        $field_list = array_merge($field_list, Array('serie_tit'));
        $field_list = array_merge($field_list, Array('umfang'));
        $field_list = array_merge($field_list, Array('werk_info', 'rn', 'bnd', 'bnd_display', 'aufsatz','mi', 'band_werk', 'serie_tit', 'ungez_reihe'));
        $field_list = array_merge($field_list, Array('zdb_nr', 'zs_hinweis', 'ff', 'zs_verlauf', 'ausgabe'));
        $field_list = array_merge($field_list, Array('orig_verlag_display', 'orig_ausgabe_display', 'orig_serie_tit'));
        $field_list = array_merge($field_list, Array('abstract'));
        $field_list = array_merge($field_list, Array('fn', 'fn_display', 'fn_enthWerke', 'fn_ebooks', 'fn_interpret'));

        //$debug_out .= "recordFormats = \n    " . $this->getFormat() . "\n";

        $empty_fields = '';

        foreach ($field_list as $field) {
            if (empty($this->fields[$field])) {
                $empty_fields .= "<tr>";
                $empty_fields .= "<td>" . $field . "</td><td /><td />";
                $empty_fields .= "</tr>";
            } else {
                $debug_out .= "<tr>";
                if (is_array($this->fields[$field])) {
                    $debug_out .= "<td>$field</td><td class='space' /><td>[" . implode("<br />", $this->fields[$field]) . "]</td>";
                } else {
                    $debug_out .= "<td>$field</td><td class='space' /><td>" . $this->fields[$field] . "</td>";
                }
                $debug_out .= "</tr>";
            }
        }

        $debug_out = $debug_out . $empty_fields;

        global $interface;
        $interface->assign('index', $debug_out);

        return $debug_out;
    }
   
    /**
     * Get raw data.
     *
     * @return string
     */ 
    public function getRaw() 
    {
        return $this->recordDriver->getRawData();
    }
}

?>
