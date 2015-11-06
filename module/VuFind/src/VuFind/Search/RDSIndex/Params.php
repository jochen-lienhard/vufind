<?php
/**
 * RDSIndex aspect of the Search Multi-class (Params)
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
 * @package  Search_RDSIndex
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace VuFind\Search\RDSIndex;
use VuFindSearch\ParamBag;

/**
 * Solr Search Parameters
 *
 * @category VuFind2
 * @package  Search_Solr
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class Params extends \VuFind\Search\Solr\Params
{
    /**
     * Constructor
     *
     * @param \VuFind\Search\Base\Options  $options      Options to use
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct($options, \VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($options, $configLoader);
        // Use basic facet limit by default, if set:

        $config = $configLoader->get('RDSIndex_facets');
        if (isset($config->Results_Settings->facet_limit)
            && is_numeric($config->Results_Settings->facet_limit)
        ) {
            $this->setFacetLimit($config->Results_Settings->facet_limit);
        }
    }

    /**
     * Initialize facet settings for the specified configuration sections.
     *
     * @param string $facetList     Config section containing fields to activate
     * @param string $facetSettings Config section containing related settings
     * @param string $cfgFile       Optional config file for facets
     *
     * @return bool                 True if facets set, false if no settings found
     */
    protected function initFacetList($facetList, $facetSettings, $cfgFile = 'RDSIndex_facets')
    {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('RDSIndex_facets');
        if (!isset($config->$facetList)) {
            return false;
        }
        if (isset($config->$facetSettings->orFacets)) {
            $orFields
                = array_map('trim', explode(',', $config->$facetSettings->orFacets));
        } else {
            $orFields = array();
        }
        foreach ($config->$facetList as $key => $value) {
            $useOr = (isset($orFields[0]) && $orFields[0] == '*')
                || in_array($key, $orFields);
            $this->addFacet($key, $value, $useOr);
        }
        if (isset($config->$facetSettings->facet_limit)
            && is_numeric($config->$facetSettings->facet_limit)
        ) {
            $this->setFacetLimit($config->$facetSettings->facet_limit);
        }
        return true;
    }
}
