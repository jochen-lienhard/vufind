<?php
return array(
    'extends' => 'bootstrap3',
    'css' => array(
	    'rds.css',
      'rds-medienicons.css',
    ),
    'js' => array(
    ),
    'favicon' => 'rds-favicon.ico',
    'helpers' => array(
        'invokables' => array(
            'rdsindexrecord' => 'VuFind\View\Helper\RDS\RDSIndexRecord',
        )
    )

);
