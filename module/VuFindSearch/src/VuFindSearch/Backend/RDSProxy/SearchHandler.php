<?php

/**
 * VuFind SearchHandler.
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
namespace VuFindSearch\Backend\RDSProxy;

/**
 * VuFind SearchHandler.
 *
 * The SearchHandler implements the rule-based translation of a user search
 * query to a SOLR query string.
 *
 * @category VuFind2
 * @package  Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   David Maus <maus@hab.de>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
class SearchHandler extends \VuFindSearch\Backend\Solr\SearchHandler
{
    /**
     * Return a Dismax subquery for specified search string.
     *
     * @param string $search Search string
     *
     * @return string
     */
    protected function dismaxSubquery($search)
    {
        $dismaxParams = [];
        foreach ($this->specs['DismaxParams'] as $param) {
            $dismaxParams[] = sprintf(
                "%s='%s'", $param[0], addcslashes($param[1], "'")
            );
        }
        foreach ($this->specs['DismaxFields'] as $field) {
           $dismaxQuery .= sprintf(
            '(%s:(%s)',
            $field,
            $search
        );
           if ($field !== end($this->specs['DismaxFields'])) {
              $dismaxQuery = sprintf('%s) OR ', $dismaxQuery);
           } else {
              $dismaxQuery .= ")";
           }
        }
        return sprintf("%s", $dismaxQuery);
    }
}
