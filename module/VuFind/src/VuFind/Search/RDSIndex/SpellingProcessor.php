<?php
/**
 * Solr spelling processor.
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
 * @package  Search_Solr
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace VuFind\Search\RDSIndex;
use VuFindSearch\Backend\RDSIndex\Response\Json\Spellcheck;
use VuFindSearch\Query\AbstractQuery;
use Zend\Config\Config;

/**
 * Solr spelling processor.
 *
 * @category VuFind2
 * @package  Search_Solr
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class SpellingProcessor extends \VuFind\Search\Solr\SpellingProcessor
{
    /**
     * Get raw spelling suggestions for a query.
     *
     * @param Spellcheck    $spellcheck Complete spellcheck information
     * @param AbstractQuery $query      Query for which info should be retrieved
     *
     * @return array
     * @throws \Exception
     */
    public function getSuggestions(Spellcheck $spellcheck, AbstractQuery $query)
    {
        $allSuggestions = [];
        foreach ($spellcheck as $term => $info) {
            if (!$this->shouldSkipTerm($query, $term, false)
                && ($suggestions = $this->formatAndFilterSuggestions($query, $info))
            ) {
                $allSuggestions[$term] = [
                    'freq' => $info['origFreq'],
                    'suggestions' => $suggestions
                ];
            }
        }
        // Fail over to secondary suggestions if primary failed:
        if (empty($allSuggestions) && ($secondary = $spellcheck->getSecondary())) {
            return $this->getSuggestions($secondary, $query);
        }
        return $allSuggestions;
    }
}
