<?php

/**
 * RDSIndex QueryBuilder.
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
 * @package  Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   David Maus <maus@hab.de>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
namespace VuFindSearch\Backend\RDSIndex;

use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;
use VuFindSearch\Query\Query;

use VuFindSearch\ParamBag;

/**
 * SOLR QueryBuilder.
 *
 * @category VuFind2
 * @package  Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   David Maus <maus@hab.de>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
class QueryBuilder extends \VuFindSearch\Backend\Solr\QueryBuilder implements QueryBuilderInterface
{
    /**
     * Return SOLR search parameters based on a user query and params.
     *
     * @param AbstractQuery $query User query
     *
     * @return ParamBag
     */
    public function build(AbstractQuery $query)
    {
        $params = new ParamBag();

        // Add spelling query if applicable -- note that we mus set this up before
        // we process the main query in order to avoid unwanted extra syntax:
        if ($this->createSpellingQuery) {
            $params->set('spellcheck.q', $query->getAllTerms());
        }

        if ($query instanceof QueryGroup) {
            $query = $this->reduceQueryGroup($query);
        } else {
            $query->setString(
                $this->getLuceneHelper()->normalizeSearchString($query->getString())
            );
        }
        $string  = $query->getString() ?: '*:*';

        if ($handler = $this->getSearchHandler($query->getHandler(), $string)) {
            if (!$handler->hasExtendedDismax()
                && $this->getLuceneHelper()->containsAdvancedLuceneSyntax($string)
            ) {
                $string = $this->createAdvancedInnerSearchString($string, $handler);
                if ($handler->hasDismax()) {
                    $oldString = $string;
                    $string = $handler->createBoostQueryString($string);

                    // If a boost was added, we don't want to highlight based on
                    // the boost query, so we should use the non-boosted version:
                    if ($this->createHighlightingQuery && $oldString != $string) {
                        $params->set('hl.q', $oldString);
                    }
                }
            } else {
                if ($handler->hasDismax()) {
                    $params->set('qf', implode(' ', $handler->getDismaxFields()));
                    $params->set('qt', $handler->getDismaxHandler());
                    foreach ($handler->getDismaxParams() as $param) {
                        $params->add(reset($param), next($param));
                    }
                    if ($handler->hasFilterQuery()) {
                        $params->add('fq', $handler->getFilterQuery());
                    }
                } else {
                    $string = $handler->createSimpleQueryString($string);
                }
            }
        }

        // remove filter query, if $string contains rn:
        if (preg_match('/\(rn\:/', $string) || preg_match('/\ rn\:/', $string)  
            || preg_match('/^rn\:/', $string) || preg_match('/\brn\:/', $string)
        ) {
            $params->set('fq', null);
        } else {
            $params->set('fq', 'mi:0'); 
        }

        $params->set('q', $string);

        return $params;
    }

    /**
     * Reduce components of query group to a search string of a simple query.
     *
     * This function implements the recursive reduction of a query group.
     *
     * @param AbstractQuery $component Component
     *
     * @return string
     *
     * @see self::reduceQueryGroup()
     */
    protected function reduceQueryGroupComponents(AbstractQuery $component)
    {
        if ($component instanceof QueryGroup) {
            $reduced = array_map(
                [$this, 'reduceQueryGroupComponents'], $component->getQueries()
            );
            $searchString = $component->isNegated() ? 'NOT ' : '';
            $searchString .= sprintf(
                '(%s)', implode(" {$component->getOperator()} ", $reduced)
            );
        } else {
            // RDS if search field is py, manipulate the searchString
            if ($component->getHandler() == 'py') {
                $searchString = $this->filterPy($component->getString());
            } else {
                $searchString  = $this->getLuceneHelper()
                    ->normalizeSearchString($component->getString());
            }
            // RDS if search field is au, manipulate the searchString
            if ($component->getHandler() == 'au') {
                $searchString = $this->filterAu($searchString);
            }
            $searchHandler = $this->getSearchHandler(
                $component->getHandler(),
                $searchString
            );
            if ($searchHandler) {
                $searchString
                    = $this->createSearchString($searchString, $searchHandler);
            }
        }
        return $searchString;
    }

    /**
     * Manipulates the search string for au search field.
     *
     * @param string $lookfor search string
     *
     * @return string
     */
    protected function filterAu($lookfor)
    {
        $filteredTerms = [];
        $totalQuotationMarks = 0;
        foreach (preg_split('/;/u', $lookfor) as $term) {
            // trim separators (whitespace)
            $term = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $term);

            $quotationMarks = mb_substr_count($term, '"');
            $totalQuotationMarks += $quotationMarks;

            // only convert term to phrase if
            // - no quotations marks included
            // - even number of quotation marks before term = semicolon not within phrase
            // - exactly one comma included
            // - no wildcard included
            if ($quotationMarks == 0 
                && $totalQuotationMarks % 2 === 0 
                && mb_substr_count($term, ',') == 1 
                && mb_strpos($term, '*') === false
            ) {
                $filteredTerms[] = '"' . $term . '"';
            } else {
                $filteredTerms[] = $term;
            }
        }
        $filtered = implode(' ', $filteredTerms);
        return $filtered;
    }

    /**
     * Manipulates the search string for py search field.
     *
     * @param string $lookfor search string
     *
     * @return string
     */
    protected function filterPy($lookfor)
    {
        $result_term = "";
        // remove whitespace
        $lookfor = preg_replace('/\p{Z}+/u', '', $lookfor);
        // check if lookfor looks like 2000-2010
        if (preg_match('/^[0-9]{1,4}(-[0-9]{1,4})?$/', $lookfor)) {
            $result_term=preg_replace('/^([0-9]{1,4})-([0-9]{1,4})/', '[$1 TO $2]', $lookfor);
        } else {
            // check if lookfor looks like 2000-
            if (preg_match('/^[0-9]{1,4}-?$/', $lookfor)) {
                $result_term=preg_replace('/^([0-9]{1,4})-/', '[$1 TO *]', $lookfor);
            } else {
                // else delete ! at the end
                $result_term = preg_replace('/\!$/u', '', $lookfor); 
            }
        }
        return ($result_term);
    }
}
