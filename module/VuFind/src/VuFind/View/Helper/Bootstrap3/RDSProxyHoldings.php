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
class RDSProxyHoldings extends \Zend\View\Helper\AbstractHelper implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;
    
    protected $linkresolver = null;
    protected $linkresolverview = true;
    
    protected $fulltextview = true;
    
    /**
     * Constructor
     * 
     * @param \VuFind\Resolver\Driver $linkresolver Linkresolver
     */
    public function __construct($linkresolver)
    {
        $this->linkresolver = $linkresolver;
    }
    
    public function getServicesHTML($openUrl) {
        
        $xmlResponse = $this->fetchLinks($openUrl, $this->translator->getLocale());
        
        $output = substr ($xmlResponse, strpos($xmlResponse,'<div id="services">'));
        $pos = strpos($output, '<div id="services_end">');
        $output = substr ($output, 0, $pos) . '</div>';
        
        return $output;
    }   
    
    public function fetchLinks($openUrl, $locale = null) {
        if (isset($locale)) {
            $openUrl .= "&rl_language=" . $locale;
        }
        $xmlResponse = $this->linkresolver->fetchLinks($openUrl);
        
        return $xmlResponse;
    }
    
    public function isFulltextView() {
        
        return $this->fulltextview;;
    }
    
    public function isLinkresolverView() {
        return $this->linkresolverview;
    }
    
}
