<?php

/**
 * Factory for the default SOLR backend.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2013.
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
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFind\Search\Factory;

use VuFindSearch\Backend\Interlending\Response\Json\RecordCollectionFactory;
use VuFindSearch\Backend\Interlending\Connector;
use VuFindSearch\Backend\Interlending\Backend;

/**
 * Factory for the default SOLR backend.
 *
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class InterlendingBackendFactory extends AbstractInterlendingBackendFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->searchConfig = 'Interlending_searches';
        $this->searchYaml = 'Interlending_searchspecs.yaml';
        $this->facetConfig = 'Interlending_facets';
    }

    /**
     * Get the Interlending core.
     *
     * @return string
     */
    protected function getSolrCore()
    {
        $config = $this->config->get('config');
        return isset($config->Interlending->default_core)
            ? $config->Interlending->default_core : 'biblio';
    }

    /**
     * Create the Interlending SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {
        $backend = parent::createBackend($connector);
        $manager = $this->serviceLocator->get('VuFind\RecordDriverPluginManager');
        $factory = new RecordCollectionFactory([$manager, 'getSolrRecord']);
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }
}
