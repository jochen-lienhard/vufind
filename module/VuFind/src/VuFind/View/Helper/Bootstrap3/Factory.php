<?php
/**
 * Factory for Bootstrap view helpers.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace VuFind\View\Helper\Bootstrap3;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for Bootstrap view helpers.
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 *
 * @codeCoverageIgnore
 */
class Factory
{
    /**
     * Construct the Flashmessages helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Flashmessages
     */
    public static function getFlashmessages(ServiceManager $sm)
    {
        $messenger = $sm->getServiceLocator()->get('ControllerPluginManager')
            ->get('FlashMessenger');
        return new Flashmessages($messenger);
    }

    /**
     * Construct the LayoutClass helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return LayoutClass
     */
    public static function getLayoutClass(ServiceManager $sm)
    {
        $config = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        $left = !isset($config->Site->sidebarOnLeft)
            ? false : $config->Site->sidebarOnLeft;
        $mirror = !isset($config->Site->mirrorSidebarInRTL)
            ? true : $config->Site->mirrorSidebarInRTL;
        $offcanvas = !isset($config->Site->offcanvas)
            ? false : $config->Site->offcanvas;
        // The right-to-left setting is injected into the layout by the Bootstrapper;
        // pull it back out here to avoid duplicate effort, then use it to apply
        // the mirror setting appropriately.
        $layout = $sm->getServiceLocator()->get('viewmanager')->getViewModel();
        if ($layout->rtl && !$mirror) {
            $left = !$left;
        }
        return new LayoutClass($left, $offcanvas);
    }

    /**
     * Construct the Recaptcha helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Recaptcha
     */
    public static function getRecaptcha(ServiceManager $sm)
    {
        return new Recaptcha(
            $sm->getServiceLocator()->get('VuFind\Recaptcha'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );
    }

   /**
     * Construct the RDSProxyHoldings helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RDSProxyHoldings
     */
    public static function getRDSProxyHoldings(ServiceManager $sm)
    {
        $serviceLocator = $sm->getServiceLocator();
        $config = $serviceLocator->get('VuFind\Config')->get('config');
        
        $linkresolver = $linkresolver = Factory::getLinkresolver($sm);
        $authzService = $serviceLocator->get('ZfcRbac\Service\AuthorizationService');
        
        return new RDSProxyHoldings($linkresolver, $authzService);
    }

     /**
     * Construct the RDSProxyHoldings helper for print.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RDSProxyHoldings
     */
    public static function getRDSProxyHoldingsPrint(ServiceManager $sm)
    {
        $serviceLocator = $sm->getServiceLocator();
        
        $linkresolver = $linkresolver = Factory::getLinkresolver($sm);
        $authzService = $serviceLocator->get('ZfcRbac\Service\AuthorizationService');
        
        return new RDSProxyHoldings($linkresolver, $authzService);
    }

     /**
     * Construct the RDSExport helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RDSProxyHoldings
     */
    public static function getRDSExport(ServiceManager $sm)
    {
        $serviceLocator = $sm->getServiceLocator();
        $config = $serviceLocator->get('VuFind\Config')->get('config');
        $linkresolver = $linkresolver = Factory::getLinkresolver($sm);
        
        return new RDSExport($linkresolver);
    }

    public static function getRDSProxyList(ServiceManager $sm) {
      $serviceLocator = $sm->getServiceLocator();
      $linkresolver = Factory::getLinkresolver($sm);
      $autzService = $serviceLocator->get('ZfcRbac\Service\AuthorizationService');
      
      
      return new RDSProxyList($linkresolver, $autzService);
    }
    
    private static function getLinkresolver(ServiceManager $sm) {
        $linkresolver = null;
        $serviceLocator = $sm->getServiceLocator();
        $config = $serviceLocator->get('VuFind\Config')->get('config');
        if (isset($config->OpenURL) && isset($config->OpenURL->resolver)) {
            $resolverDriverPluginManger= $serviceLocator
                ->get('VuFind\ResolverDriverPluginManager');
            $linkresolver = $resolverDriverPluginManger
                ->get($config->OpenURL->resolver);
        }
        
        return $linkresolver;
    }
    
}
