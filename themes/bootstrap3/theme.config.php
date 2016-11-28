<?php
return array(
    'extends' => 'root',
    'css' => array(
        //'vendor/bootstrap.min.css',
        //'vendor/bootstrap-accessibility.css',
        //'vendor/font-awesome.min.css',
        //'bootstrap-custom.css',
        'compiled.css',
        'print.css:print',
    ),
    'js' => array(
        'vendor/base64.js:lt IE 10', // btoa polyfill
        'vendor/jquery.min.js',
        'vendor/bootstrap.min.js',
        'vendor/bootstrap-accessibility.min.js',
        'vendor/validator.min.js',
        'autocomplete.js',
        'common.js',
        'lightbox.js',
    ),
    'less' => array(
        'active' => false,
        'compiled.less'
    ),
    'favicon' => 'vufind-favicon.ico',
    'helpers' => array(
        'factories' => array(
            'flashmessages' => 'VuFind\View\Helper\Bootstrap3\Factory::getFlashmessages',
            'layoutclass' => 'VuFind\View\Helper\Bootstrap3\Factory::getLayoutClass',
            'rdsproxyholdings' => 'VuFind\View\Helper\Bootstrap3\Factory::getRDSProxyHoldings',
            'rdsproxylist' => 'VuFind\View\Helper\Bootstrap3\Factory::getRDSProxyList',
            'rdsproxyholdingsprint' => 'VuFind\View\Helper\Bootstrap3\Factory::getRDSProxyHoldingsPrint',
            'rdsexport' => 'VuFind\View\Helper\Bootstrap3\Factory::getRDSExport',
            'recaptcha' => 'VuFind\View\Helper\Bootstrap3\Factory::getRecaptcha',
        ),
        'invokables' => array(
            'highlight' => 'VuFind\View\Helper\Bootstrap3\Highlight',
            'rdsindexholding' => 'VuFind\View\Helper\Bootstrap3\RDSIndexHolding',
            'rdsproxydescription' => 'VuFind\View\Helper\Bootstrap3\RDSProxyDescription',
            'rdsindexdescription' => 'VuFind\View\Helper\Bootstrap3\RDSIndexDescription',
            'rdsindexcore' => 'VuFind\View\Helper\Bootstrap3\RDSIndexCore',
            'rdsproxycore' => 'VuFind\View\Helper\Bootstrap3\RDSProxyCore',
            'search' => 'VuFind\View\Helper\Bootstrap3\Search'
        )
    )
);
