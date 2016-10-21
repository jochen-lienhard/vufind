<?php

/**
 * SOLR connector.
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

use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;
use VuFindSearch\Query\Query;

use VuFindSearch\ParamBag;

use VuFindSearch\Backend\Exception\HttpErrorException;

use VuFindSearch\Backend\RDSProxy\Document\AbstractDocument;

use Zend\Http\Request;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\AdapterInterface;

use Zend\Log\LoggerInterface;

use InvalidArgumentException;
use XMLWriter;

/**
 * SOLR connector.
 *
 * @category VuFind2
 * @package  Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   David Maus <maus@hab.de>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
class Connector extends \VuFindSearch\Backend\Solr\Connector implements \Zend\Log\LoggerAwareInterface
{
    /**
     * Guest flag 
     *
     * @var boolean 
     */
    protected $hasPermission = false;

    /**
     * Set permission flag.
     *
     * @param boolean $hasPermission Set the guest flag
     *
     * @return void
     */
    public function setHasPermission($hasPermission)
    {
        $this->hasPermission= $hasPermission;
    }

    /// Internal API

    /**
     * Send query to SOLR and return response body.
     *
     * @param string   $handler SOLR request handler to use
     * @param ParamBag $params  Request parameters
     *
     * @return string Response body
     */
    public function query($handler, ParamBag $params)
    {
        $urlSuffix = '/' . $handler;
        $paramString = implode('&', $params->request());
        // ToDo Fix
        if ($this->hasPermission) {
            $paramString .= "&guest=n";
        } else {
            $paramString .= "&guest=y";
        }
        $paramString .= "&sid=" . session_id();
        if (strlen($paramString) > self::MAX_GET_URL_LENGTH) {
            $method = Request::METHOD_POST;
        } else {
            $method = Request::METHOD_GET;
        }

        if (strlen($paramString) > self::MAX_GET_URL_LENGTH) {
            $method = Request::METHOD_POST;
            $callback = function ($client) use ($paramString) {
                $client->setRawBody($paramString);
                $client->setEncType(HttpClient::ENC_URLENCODED);
                $client->setHeaders(['Content-Length' => strlen($paramString)]);
            };
        } else {
            $method = Request::METHOD_GET;
            $urlSuffix .= '?' . $paramString;
            $callback = null;
        }

        $this->debug(sprintf('Query %s', $paramString));
        return $this->trySolrUrls($method, $urlSuffix, $callback);
    }
}
