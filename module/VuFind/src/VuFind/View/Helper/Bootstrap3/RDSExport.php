<?php
namespace VuFind\View\Helper\Bootstrap3;
use Zend\View\Exception\RuntimeException, Zend\View\Helper\AbstractHelper;
use VuFind\Export\DataProvider\RDSDataProviderProxy;
use VuFind\Export\DataProvider\RDSDataProviderIndex;
use VuFind\Export\RDSToBibTeX;
use VuFind\Export\RDSToRIS;
use VuFind\Export\RDSToJSON;
use VuFind\Export\RDSToCOinS;
use VuFind\Export\RDSToHTML;
use VuFind\Export\RDSToPRINT;

class RDSExport extends AbstractHelper
{
    protected $formatter = null;
    protected $linkresolver = null;
    
    public function __construct($linkresolver) 
    {
        $this->linkresolver = $linkresolver;
    }
    
    public function __invoke($driver, $format) 
    {
        switch ($format) {
        case 'HTML':
            $formatter = new RDSToHTML($driver, $this->view, $this->linkresolver);
            break;
        case 'PRINT':
            $formatter = new RDSToPrint($driver, $this->view, $this->linkresolver);
            break;
        case 'BibTeX':
            $formatter = new RDSToBibTeX($driver);
            break;
        case 'RIS':
            $formatter = new RDSToRIS($driver);
            break;                
        }
        
        $this->formatter = $formatter;
        
        return $this;
    }
    
    public function getRecord() 
    {
        return $this->formatter->getRecord();
    }

    public function getCore() 
    {
        return $this->formatter->getCore();
    }
    
    public function getDescription() 
    {
        return $this->formatter->getDescription();
    }
    
    public function getHoldings() 
    {
        return $this->formatter->getHoldings();
    }
    
}

