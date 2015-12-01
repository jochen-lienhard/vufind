<?php
return array(
    'extends' => 'rds',
    'css' => array(
		//'/css/bootstrap.min.css', // FIXME: We don't need to include it. Inherited from bootstrap theme
    ),
    'js' => array(
    ),
    'favicon' => 'icons/ub-freiburg.ico',
    'helpers' => array(
        'invokables' => array(
            'rdsindexholding' => 'VuFind\View\Helper\Ulm\RDSIndexHolding',
            'rdsindexdescription' => 'VuFind\View\Helper\Ulm\RDSIndexDescription',
        )
    )

);
