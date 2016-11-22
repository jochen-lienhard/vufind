<?php
/**
 * Record driver view helper
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\View\Helper\Bootstrap3;
use VuFind\View\Helper\Bootstrap3\RDSHelper;

/**
 * Record driver view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSProxyCore extends RDSHelper
{
    protected $items = [
       'Title',
       'TitleAlt',
       'Authors',
       'Source',
       'SeriesTitle',
       'Doi',
       'Pmid',
       'Isbns',
       'Issns',
       'PubYear',
       'Languages', 
       'DataSource',
    ];  

    // removed CitationLinks Lie 19.07.16

    /**
     * Dummy 
     *
     * @return string
     */
    public function getTitle() 
    {
        $record = $this->view->plugin('record')->__invoke($this->driver);
        $value = $record->getTitleHtml(PHP_INT_MAX);
        
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getTitleAlt() 
    {
        $value = $this->driver->getTitleAlt();
        return $value;
    
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getAuthors() 
    {
        $value = $this->driver->getAuthors();
        return $value;
    }

    /**
     * Get DataSource
     *
     * @return string
     */
    public function getDataSource() 
    {
        $value = $this->driver->getDataSource();
        $value .= $this->getCitationLinks();
        return $value;
    }

    /**
     * Get Source 
     *
     * @return string
     */
    public function getSource()
    {
        $value = $this->driver->getSource();
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getSeriesTitle() 
    {
        $value = $this->driver->getSeriesTitle();
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getDoi() 
    {
        $value = '';
        $doi = $this->driver->getDoi();
        if ($doi) {
            $value = '<a class="link-external" href="http://dx.doi.org/' . $doi . '" target="_blank">' . $doi . '</a>';
        }
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getPmid() 
    {
        $value = '';
        $pmid = $this->driver->getPmid();
        if ($pmid) {
            $value = '<a class="link-external" href="http://www.ncbi.nlm.nih.gov/pubmed/' . $pmid . '" target="_blank">' . $pmid . '</a>';
        } 
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getIsbns() 
    {
        // {foreach from=$summISBNs key=type item=isbn name=loop}{$isbn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
        $value = $this->driver->getIsbns();
        $value = implode(
            '<br />', 
            array_map(
                function($v, $k) {
                        return htmlspecialchars($v) . ' (' . $this->translate($k) . ')';
                }, 
                $value, 
                array_keys($value)
            )
        );
      
      
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getIssns() 
    {
        //{foreach from=$summISSNs key=type item=issn name=loop}{$issn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
        $value = $this->driver->getIssns();
        $value = implode(
            '<br />',
            array_map(
                function($v, $k) {
                        return htmlspecialchars($v) . ' (' . $this->translate($k) . ')';
                }, 
                $value, 
                array_keys($value)
            )
        );
        return $value;
    }
   
    /**
     * Dummy 
     *
     * @return string
     */
    public function getPubYear() 
    {
        $value = $this->driver->getPubYear();
        return $value;
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getCitationLinks() 
    {
        //<a target="_blank" href="{$summCitationLinks[0].url}" onclick="userAction('click', 'RdsCitationLink', '{$ppn}');">&rarr;{translate text="Link zum Zitat"}</a>
      
        if ($this->driver->showCitationLinks() == false) {
            return '';
        };  
        
        $html = '';
        foreach ($this->driver->getCitationLinks() as $citationLink) {
          $html .= '<a class="link-external" target="_blank" href="' . $citationLink['url'] . '" onclick="userAction(\'click\', \'RdsCitationLink\', \'' . $this->driver->getUniqueId() . '\');"> ' . $this->translate("RDS_CITATION_LINK") .'</a>';
          
        }  
      
        return $html;
    }
    
    /**
     * Get available languages for entry 
     *
     * @return string
     */
    public function getLanguages() 
    {
        $translatedLanguages = array_map(function($lang){
          return $this->translate($lang);
        }, $this->driver->getLanguages() );
      
        $value = implode(', ', $translatedLanguages);
        return $value;
    }
}
