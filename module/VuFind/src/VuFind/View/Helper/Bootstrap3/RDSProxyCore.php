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
       'DataSource',
       'CitationLinks',
    ];  
    
    public function getTitle() {
      $value = $this->driver->getTitle();
      return $value;
    }
    public function getTitleAlt() {
      $value = $this->driver->getTitleAlt();
      return $value;
    
    }
    public function getAuthors() {
      $value = $this->driver->getAuthors();
      return $value;
    }
    public function getSource() {
      $value = $this->driver->getSource();
      return $value;
    }
    public function getSeriesTitle() {
      $value = $this->driver->getSeriesTitle();
      return $value;
    }
    public function getDoi() {
      $value = '';
      $doi = $this->driver->getDoi();
      if ($doi) {
          $value = '<a href="http://dx.doi.org/' . $doi . '" target="_blank">' . $doi . '</a>';
      }
      return $value;
    }
    public function getPmid() {
      $value = '';
      $pmid = $this->driver->getPmid();
      if ($pmid) 
      {
        $value = '<a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $pmid . '" target="_blank">' . $pmid . '</a>';
      } 
      return $value;
    }
    public function getIsbns() {
      // {foreach from=$summISBNs key=type item=isbn name=loop}{$isbn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
      $value = $this->driver->getIsbns();
      $value = implode('<br />', 
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
    public function getIssns() {
      //{foreach from=$summISSNs key=type item=issn name=loop}{$issn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
      $value = $this->driver->getIssns();
      $value = implode('<br />',
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
    
    public function getPubYear() {
      $value = $this->driver->getPubYear();
      return $value;
    }

    public function getCitationLinks() {
      //<a target="_blank" href="{$summCitationLinks[0].url}" onclick="userAction('click', 'RdsCitationLink', '{$ppn}');">&rarr;{translate text="Link zum Zitat"}</a>
      
      if ($this->driver->showCitationLinks() == false) {
        return '';
      };  
        
      $html = '';
      foreach ($this->driver->getCitationLinks() as $citationLink) {
          $html .= '<a target="_blank" href="' . $citationLink[url] . '" onclick="userAction(\'click\', \'RdsCitationLink\', \'{$ppn}\');">&rarr; ' . $this->translate("Link zum Zitat") .'</a>';
      }  
      
      return $html;
    }
}
