<?php
/**
 * RDSProxyHolding view helper
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

/**
 * RDSProxyHolding view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSProxyHoldings extends RDSProxyHelper
{
    protected $items = ['Holdings'];
  
    /**
     * Calculate the holdings info
     *
     * @return string 
     */ 
    public function getHoldings() 
    {
        $output = "";
        
        $fulltextLinks = $this->driver->getFulltextLinks();
        $infoLinks = $this->driver->getInfoLinks();
        
        if ($fulltextLinks && count($fulltextLinks) == 1 && $fulltextLinks[0]['indicator'] == 2) {
            $fulltextLinks = '';
        }
        
        if ($this->driver->getFulltextview()) {
            if ($fulltextLinks || $infoLinks) {
                $output .= '<div class="tg">';
                $output .= '<h2>' . $this->translate("RDS_PROXY_HOLDINGS_ELECTRONIC_FULLTEXT") . '</h2>';
                $output .= $this->getFulltextLinks();
                $output .= $this->getInfoLinks();
                $output .= '</div>';
            }
            
            if ($this->driver->getLinkresolverview() && (!empty($fulltextLinks) || !empty($infoLinks))) {
                $output .= $this->getLinkresolverLink();
            }
        }
        if (!$this->driver->getFulltextview() || (empty($fulltextLinks) && empty($infoLinks))) {
            $output .= $this->getLinkresolverEmbedded();
        }
        return $output;
    }
   
    /**
     * Calculate the info links
     *
     * @return string
     */ 
    protected function getInfoLinks() 
    {
        $html = '';
        $infoLinks = $this->driver->getInfoLinks();
        foreach ($infoLinks as $infoLink) {
            $html .= '<div class="t_ezb_result">';
            $html .= '  <p>';
            $html .= '    <span class="t_ezb_' . $infoLink['access']. '"></span>';
            $html .=         $this->translate("RDS_PROXY_HOLDINGS_LINKS_INFO_" . strtoupper($infoLink[info]));
            $html .= '    <span class="t_link"><a target="_blank" href="' . $infoLink['url']. '">&#187;</a></span>';
            $html .= '  </p>';
            $html .= '</div>';
        }
        return $html;
    }
   
    /**
     * Calculate the link resolver link
     *
     * @return string
     */ 
    protected function getLinkresolverLink() 
    {
        $html = '<div class="tg">';
        $html .= '  <h2>' . $this->translate("RDS_PROXY_HOLDINGS_MORE_SOURCES") .' </h2>';
        $html .= '  <p>';
        $html .= '    <a class="redi_links_icon" target="linkresolver" href="' . $this->driver->getOpenUrlExternal() . '" title="' . $this->translate("RDS_PROXY_HOLDINGS_REDI_LINKS") . '"></a>';
        $html .= '  </p>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Build the embedded version of the link resolver info 
     *
     * @return string
     */ 
     protected function getLinkresolverEmbedded() 
     {        
        $locale = $this->getLocale();
        if ($locale) { 
            $openUrl = $this->driver->getOpenUrlExternal() . "&rl_language=" . $locale; 
        } else {
            $openUrl = $this->driver->getOpenUrlExternal();
        }
        
        if ($this->linkresolver) {
            $xmlResponse = $this->linkresolver->fetchLinks($openUrl);
        } else {
            $xmlResponse = 'ERROR - linkresolver not available';
        }
        $output = substr($xmlResponse, strpos($xmlResponse, '<div id="services">'));
        $pos = strpos($output, '<div id="services_end">');
        $output = substr($output, 0, $pos);
        
        // ugly workaround for nested form issue
        $id= $this->driver->getUniqueID() . '_rl_usearch';
        $output = preg_replace('/<form(.*)>/i', '<form${1}> <div id="'. $id . '"${1}>', $output);
        $output = str_replace('type="submit"', 'type="submit" onclick="return performRediLinkSearch(\''.$id.'\');"', $output);
        $output = str_replace('</form>', '</div></form>', $output);
        
        return $output;
     }
     /**
      * Dummy.
      *
      * @return Mixed
      */
     protected function getFulltextLinks()
     {
         $fulltextLinks = $this->driver->getFulltextLinks();
         $html = '';
         
         foreach ($fulltextLinks as $fulltextLink) {
             if ($fulltextLink['indicator'] == 1) {
                 if (!$this->isLoggedIn) {
                     $html .= '<span class="t_ezb_yellow"></span>';
                     $html .= '<a style="text-decoration: none;" href=" ' . $this->getLoginLink() .  '">';
                     $html .=     $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') ' . $this->translate("RDS_PROXY_HOLDINGS_AUTHORIZED_USERS_ONLY_LOGIN");
                     $html .= '</a><br/>';
                 }
                 elseif (! $this->accessRestrictedContent) {
                     $html .= '<span class="t_ezb_yellow"></span>';
                     $html .=    $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') - ' . $this->translate("RDS_PROXY_HOLDINGS_AUTHORIZED_USERS_ONLY");
                 }
             } elseif ($fulltextLink['indicator'] != 2) {
                 $html .= '<div class="t_ezb_result">';
                 $html .= '<p>';
                 if ($fulltextLink['type'] == "pdf") {
                     $html .= '<span class="t_ezb_yellow"></span>';
                     $html .= $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                 }
                 if ($fulltextLink['type'] == "html") {
                     $html .= '<span class="t_ezb_yellow"></span>';
                     $html .= $this->translate("RDS_PROXY_HOLDINGS_HTML_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                 }
                 if ($fulltextLink['type'] == "external") {
                     $html .= '<span class="t_ezb_' . $fulltextLink['access'] . '"></span>';
                     $html .= $this->translate("RDS_PROXY_HOLDINGS_TO_THE_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                 }
                 $html .= '<span class="t_link"><a target="_blank" href="' . $fulltextLink['url'] . '">&#187;</a></span>';
                 $html .= '</p>';
                 $html .= '</div>';
             }
         }
         return $html;
     }
}
