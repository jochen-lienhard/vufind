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
 * @category VuFind2
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFind\Search\Factory;

use VuFind\RecordDriver\PluginManager;

use VuFindSearch\Backend\RDSProxy\Response\Json\RecordCollectionFactory;
use VuFindSearch\Backend\RDSProxy\Connector;
use VuFindSearch\Backend\RDSProxy\Backend;

use VuFind\Search\Listener\NormalizeSolrSort;

/**
 * Factory for the default SOLR backend.
 *
 * @category VuFind2
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class RDSProxyBackendFactory extends AbstractRDSProxyBackendFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->searchConfig = 'RDSProxy_searches';
        $this->searchYaml = 'searchspecs.yaml';
    }

    /**
     * Get the Solr core.
     *
     * @return string
     */
    protected function getSolrCore()
    {
        $config = $this->config->get('config');
        return isset($config->RDSProxy->default_core)
            ? $config->RDSProxy->default_core : 'biblio';
    }

    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {
        if ($this->hasPermission()) $connector->setHasPermission($this->hasPermission());
        $backend = parent::createBackend($connector);
        $manager = $this->serviceLocator->get('VuFind\RecordDriverPluginManager');
        $factory = new RecordCollectionFactory(array($manager, 'getSolrRecord'));
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }

    /**
     * Has the current user the permission to view all 
     *
     * @return bool
     */
    protected function hasPermission()
    {
        return $this->serviceLocator->get('ZfcRbac\Service\AuthorizationService')
            ->isGranted('access.RDSProxyModule');
    }

}