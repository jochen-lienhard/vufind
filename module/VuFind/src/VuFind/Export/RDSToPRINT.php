<?php

namespace VuFind\Export;

class RDSToPRINT extends RDSToHTML{
    
    protected function getLinkresolverLink() {
        $html = '  <h2>' . $this->translate("RDS_PROXY_HOLDINGS_MORE_SOURCES") .' </h2>';
        $html .= ' <a target="linkresolver" href="' . $this->driver->getOpenUrlExternal() . '" title="' . $this->translate("RDS_PROXY_HOLDINGS_REDI_LINKS") . '">' . $this->translate("RDS_PROXY_HOLDINGS_REDI_LINKS") . '</a>';
        return $html;
     }
}
?>