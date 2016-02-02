<?php

namespace VuFind\Export\DataProvider;
use VuFind\Export\DataProvider\RDSDataProvider;

class RDSDataProviderIndex implements RDSDataProvider
{

    protected $fields;
    protected $recordDriver;

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

    public function getID() 
    {
        return isset($this->fields['ppn']) ? $this->fields['ppn'] : '&nbsp;';
    }

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

    public function getPublishingYear() 
    {
        return $this->getField('py_display');
    }

    public function getISBNs()
    {
        $isbns = array();

        foreach($this->getField('isbn_display') as $isbn) {
            $isbns[] = new ISBN_('', $isbn);
        }

        return $isbns;
    }

    public function getISSNs() 
    {
        $issns = array();

        foreach($this->getField('issn_display') as $issn) {
            $issns[] = new ISSN('', $issn);
        }

        return $issns;
    }

    public function getLanguages() 
    {
        return $this->getField('la');
    }

    public function getPublishingPlace() 
    {
        return $this->getField('pp_display');
    }

    public function getPublisher() 
    {
        return $this->getField('pu');
    }
    
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

    public function getEdition() 
    {
        return $this->getField('ausgabe');
    }

    public function getVolume() 
    {
        return $this->getField('bnd_display');
    }

    public function getAbstract() 
    {
        return $this->getField('abstract');
    }

    public function getSchool() 
    {
        return $this->getField('hss');
    }

    public function getKeywords() 
    {
        $keywords = array();

        if(isset($this->fields['ct_display'])) {
            $arr_ct = $this->fields['ct_display'];
            foreach ($arr_ct as $ct_string) {
                $keywords = array_merge($keywords, explode(" , ", $ct_string));
            }
        }

        return $keywords;
    }

    public function getPersistentLink() 
    {
        return $this->recordDriver->getPersistentLink();
    }

    public function getDOI() 
    {
        return array();
    }

    public function getDataSource() 
    {
        return array();
    }

    public function getJournal() 
    {
        return array();
    }


    public function getUebergeordneteWerke() 
    {
        $uebergeordneteWerke = $this->getField('band_werk');

        return $this->getArrayOfArrays($uebergeordneteWerke);
    }

    public function getSeries() 
    {
        $series = $this->getField('serie_tit');
        if (empty($series)) {
            $series = $this->getField('ungez_reihe');
        }

        return $this->getArrayOfArrays($series);
    }

    
    public function getFulltextLinks() 
    {
        return $this->getField('url_short');
    }
    
    
    protected function getArrayOfArrays($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $item) {
                if(strstr($item, "|")) {
                    $id_title_volume = explode(" | ", $item);
                    
                    $id = isset($id_title_volume[0]) ? $id_title_volume[0] : ''; 
                    $title = isset($id_title_volume[1]) ? $id_title_volume[1] : '';
                    $volume = isset($id_title_volume[2]) ? $id_title_volume[2] : '';
                    
                    $result[] = array(
                    'id' => $id,
                    'title' => $title,
                    'volume' => $volume
                    );
                }
                else {
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

    public function getMARC() 
    {
        return $this->fields['fullrecord'];
    }

    public function getIssue() 
    {
        return array();
    }

    /**
     * always return an ARRAY of field values
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


    // 
    // Debug
    // 

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
    
    public function getRaw() 
    {
        return $this->recordDriver->getRawData();
    }
}

?>