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
use     VuFind\I18n\Translator\TranslatorAwareInterface;

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
class RDSProxyHoldings extends RDSHelper
{
    protected $items = ['Holdings'];
    
    public function __construct($linkresolver) {
        $this->linkresolver = $linkresolver;
    }
    
    public function getHoldings() {
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
    
    protected function getFulltextLinks() {
        $fulltextLinks = $this->driver->getFulltextLinks();
        $html = '';
        
        foreach ($fulltextLinks as $fulltextLink){
            
            if ($fulltextLink['indicator'] == 1) {
              if ($this->authManager->isLoggedIn() === false) {
                 $html .= '<span class="t_ezb_yellow"></span>';
                 $html .= '<a style="text-decoration: none;" href=" ' . $this->getLoginLink() .  ' "; return false;">';
                 $html .=     $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') ' . $this->translate("RDS_PROXY_HOLDINGS_AUTHORIZED_USERS_ONLY_LOGIN");
                 $html .= '</a>';
              } elseif ($this->driver->getGuestView() == 'brief') {
                  $html .= '<span class="t_ezb_yellow"></span>';
                  $html .=    $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') - ' . $this->translate("RDS_PROXY_AUTHORIZED_USERS_ONLY");
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
                        $html .= '<span class="t_ezb_{$fulltextLink.access}"></span>';
                        $html .= $this->translate("RDS_PROXY_HOLDINGS_TO_THE_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                      }
                    $html .= '<span class="t_link"><a target="_blank" href="' . $fulltextLink['url'] . '">&#187;</a></span>';
                  $html .= '</p>';
                $html .= '</div>';
            }
        }
        return $html;
    }
    
    protected function getInfoLinks() {
        $html = '';
        $infoLinks = $this->driver->getInfoLinks();
        foreach ($infoLinks as $infoLink) {
          $html .= '<div class="t_ezb_result">';
          $html .= '  <p>';
          $html .= '    <span class="t_ezb_' . $infoLink['access']. '"></span>';
          $html .=         $this->translate("RDS_LINKS_INFO_" . strtoupper($infoLink[info]));
          $html .= '    <span class="t_link"><a target="_blank" href="' . $infoLink['url']. '">&#187;</a></span>';
          $html .= '  </p>';
          $html .= '</div>';
        }
    }
    
    protected function getLinkresolverLink() {
        $html = '<div class="tg">';
        $html .= '  <h2>' . $this->translate("RDS_PROXY_HOLDINGS_MORE_SOURCES") .' </h2>';
        $html .= '  <p>';
        $html .= '    <a class="redi_links_icon" target="linkresolver" href="' . $this->driver->getOpenUrlExternal() . '" title="' . $this->translate("RDS_PROXY_HOLDINGS_REDI_LINKS") . '"></a>';
        $html .= '  </p>';
        $html .= '</div>';
        return $html;
     }
     
     protected function getLoginLink() {
        $followupUrl = $this->view->plugin('serverUrl')->__invoke() . $_SESSION['Search']['last'];
        $target = $this->view->plugin('url')->__invoke('myresearch-home') . '?followupUrl=' . urlencode($followupUrl);
  
        $sessionInitiator = $this->authManager->getManager()->getSessionInitiator($target);
        if ($sessionInitiator) {
            $loginLink = $this->view->plugin('escapeHtmlAttr')->__invoke($sessionInitiator);
        } else {
            $loginLink = $this->view->plugin('url')->__invoke('myresearch-userlogin');
        }
        return $loginLink;
     
     }
     
     protected function getLinkresolverEmbedded() {        
        $locale = $this->getLocale();
        if ($locale) { 
            $openUrl = $this->driver->getOpenUrlExternal() . "&rl_language=" . $locale; 
        } else {
            $openUrl = $this->driver->getOpenUrlExternal();
        }
        
        $xmlResponse = $this->linkresolver->fetchLinks($openUrl);
        $output = substr ($xmlResponse, strpos($xmlResponse,'<div id="services">'));
        $pos = strpos($output, '<div id="services_end">');
        $output = substr ($output, 0, $pos);
        return $output;
    }
}
