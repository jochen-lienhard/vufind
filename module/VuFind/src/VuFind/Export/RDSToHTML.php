<?php

namespace VuFind\Export;

class RDSToHTML {
    
    protected $driver = null; 
    protected $translator = null;
    protected $sourceIdentifier = ''; 
    
    protected $items = [
       'RDSProxyCore' =>  [
            'RDSProxy_Title',
            'RDSProxy_TitleAlt',
            'RDSProxy_Authors',
            'RDSProxy_Source',
            'RDSProxy_SeriesTitle',
            'RDSProxy_Doi',
            'RDSProxy_Pmid',
            'RDSProxy_Isbns',
            'RDSProxy_Issns',
            'RDSProxy_PubYear',
            'RDSProxy_DataSource',
            'RDSProxy_CitationLinks',
        ],
       'RDSProxyDescription' => ['RDSProxy_SubjectsGeneral','RDSProxy_Abstracts','RDSProxy_Review','RDSProxy_Reviewers'],
       'RDSProxyHoldings' => ['RDSProxy_Holdings'],
       'RDSIndex' => ['']
    ];
    
    public function __construct($driver, $view, $linkresolver) {
        $this->view = $view; 
        $this->translator = $view->plugin('translate')->getTranslator();
        $this->authManager = $view->plugin('auth');
        $this->driver = $driver;
        $this->linkresolver = $linkresolver;
    }
    
    public function getRecord() {
        return $this->driver->getTitle();
    }
    
    public function getCore() {
        return $this->getItems('Core');
    }
    
    public function getDescription() {
        return $this->getItems('Description');
    }
    
    public function getHoldings($driver) {
        return $this->getItems('Holdings');
    }
    
    public function getStaffView() {
    }

    
    public function getItems($type) {
        $items = $this->items[$this->driver->getSourceIdentifier() . $type];
        
        $results = [];
        foreach ($items as $item) {
            $function = [$this, 'get' . $item];
            $itemValue = call_user_func($function);
            if ($itemValue) {
                $results[$item] = $itemValue;
            }
        }
        return $results;
    }
    
    // RDSProxy Bibliographic Details
    
    public function getRDSProxy_Title() {
      $value = $this->driver->getTitle();
      return $value;
    }
    public function getRDSProxy_TitleAlt() {
      $value = $this->driver->getTitleAlt();
      return $value;
    
    }
    public function getRDSProxy_Authors() {
      $value = $this->driver->getAuthors();
      return $value;
    }
    public function getRDSProxy_Source() {
      $value = $this->driver->getSource();
      return $value;
    }
    public function getRDSProxy_SeriesTitle() {
      $value = $this->driver->getSeriesTitle();
      return $value;
    }
    public function getRDSProxy_Doi() {
      $value = '';
      $doi = $this->driver->getDoi();
      if ($doi) {
          $value = '<a href="http://dx.doi.org/' . $doi . '" target="_blank">' . $doi . '</a>';
      }
      return $value;
    }
    public function getRDSProxy_Pmid() {
      $value = '';
      $pmid = $this->driver->getPmid();
      if ($pmid) 
      {
        $value = '<a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $pmid . '" target="_blank">' . $pmid . '</a>';
      } 
      return $value;
    }
    public function getRDSProxy_Isbns() {
      // {foreach from=$summISBNs key=type item=isbn name=loop}{$isbn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
      $value = $this->driver->getIsbns();
      $value = implode('<br />', 
                 array_map(
                   function($v, $k) {
                     return htmlspecialchars($v) . ' (' . $this->translate($k) . ')';
                   }, 
                   $value, 
                   array_keys($value)
                 )
               );
      
      
      return $value;
    }
    public function getRDSProxy_Issns() {
      //{foreach from=$summISSNs key=type item=issn name=loop}{$issn|escape} ({translate text="$type"}){if !$smarty.foreach.loop.last}<br/>{/if}{/foreach}
      $value = $this->driver->getIssns();
      $value = implode('<br />',
                 array_map(
                   function($v, $k) {
                     return htmlspecialchars($v) . ' (' . $this->translate($k) . ')';
                   }, 
                   $value, 
                   array_keys($value)
                 )
               );
      return $value;
    }
    
    public function getRDSProxy_PubYear() {
      $value = $this->driver->getPubYear();
      return $value;
    }
    public function getRDSProxy_DataSource() {
      $value = $this->driver->getDataSource();
      return $value;
    }
    public function getRDSProxy_CitationLinks() {
      //<a target="_blank" href="{$summCitationLinks[0].url}" onclick="userAction('click', 'RdsCitationLink', '{$ppn}');">&rarr;{translate text="Link zum Zitat"}</a>
      
      if ($this->driver->showCitationLinks() == false) {
        return '';
      };  
        
      $html = '';
      foreach ($this->driver->getCitationLinks() as $citationLink) {
          $html .= '<a target="_blank" href="' . $citationLink[url] . '" onclick="userAction(\'click\', \'RdsCitationLink\', \'{$ppn}\');">&rarr; ' . $this->translate("Link zum Zitat") .'</a>';
      }  
      
      return $html;
    }
    
    // RDSProxy Description
    
    public function getRDSProxy_SubjectsGeneral() {
        foreach ($this->driver->getSubjectsGeneral() as $subjectGeneral) {
            $html .= $subjectGeneral . '<br />';
        }
        
        return $html;
    }
    
    public function getRDSProxy_Abstracts() {
        foreach ($this->driver->getAbstracts() as $abstract) {
            $html .= $abstract . '<br />';
        }
        
        return $html;
    }
    
    public function getRDSProxy_Review() {
        return $this->driver->getReview();
    }
    
    public function getRDSProxy_Reviewers() {
        return $this->driver->getReviewers();
    }
    
    // RDSProxy Holdings
    
    public function getRDSProxy_Holdings() {
        $output = "";
        
        $fulltextLinks = $this->driver->getFulltextLinks();
        $infoLinks = $this->driver->getInfoLinks();
        
        if ($fulltextLinks && count($fulltextLinks) == 1 && $fulltextLinks[0]['indicator'] == 2) {
            $fulltextLinks = '';
        }
        
        if ($this->driver->getFulltextview()) {
            if ($fulltextLinks || $infoLinks) {
                $output .= '<div class="tg">';
                $output .= '<h2>' . $this->translate("RDS_PROXY_HOLDINGS_ELECTRONIC_FULLTEXT") . '</h2>';
                $output .= $this->getFulltextLinks();
                $output .= $this->getInfoLinks();
                $output .= '</div>';
            }
            
            if ($this->driver->getLinkresolverview() && (!empty($fulltextLinks) || !empty($infoLinks))) {
                $output .= $this->getLinkresolverLink();
            }
        }
        if (!$this->driver->getFulltextview() || (empty($fulltextLinks) && empty($infoLinks))) {
            $output .= $this->getLinkresolverEmbedded();
        }
        return $output;
    }
    
    protected function getFulltextLinks() {
        $fulltextLinks = $this->driver->getFulltextLinks();
        $html = '';
        
        foreach ($fulltextLinks as $fulltextLink){
            
            if ($fulltextLink['indicator'] == 1) {
              if ($this->authManager->isLoggedIn() === false) {
                 $html .= '<span class="t_ezb_yellow"></span>';
                 $html .= '<a style="text-decoration: none;" href=" ' . $this->getLoginLink() .  ' "; return false;">';
                 $html .=     $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') ' . $this->translate("RDS_PROXY_HOLDINGS_AUTHORIZED_USERS_ONLY_LOGIN");
                 $html .= '</a>';
              } elseif ($this->driver->getGuestView() == 'brief') {
                  $html .= '<span class="t_ezb_yellow"></span>';
                  $html .=    $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ') - ' . $this->translate("RDS_PROXY_AUTHORIZED_USERS_ONLY");
              }
            } elseif ($fulltextLink['indicator'] != 2) {  
                $html .= '<div class="t_ezb_result">';
                  $html .= '<p>';
                      if ($fulltextLink['type'] == "pdf") {
                        $html .= '<span class="t_ezb_yellow"></span>';
                        $html .= $this->translate("RDS_PROXY_HOLDINGS_PDF_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                      }
                      if ($fulltextLink['type'] == "html") {
                        $html .= '<span class="t_ezb_yellow"></span>';
                        $html .= $this->translate("RDS_PROXY_HOLDINGS_HTML_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                      }
                      if ($fulltextLink['type'] == "external") {
                        $html .= '<span class="t_ezb_{$fulltextLink.access}"></span>';
                        $html .= $this->translate("RDS_PROXY_HOLDINGS_TO_THE_FULLTEXT") . ' (via ' . $fulltextLink['provider'] . ')';
                      }
                    $html .= '<span class="t_link"><a target="_blank" href="' . $fulltextLink['url'] . '">&#187;</a></span>';
                  $html .= '</p>';
                $html .= '</div>';
            }
        }
        return $html;
    }
    
    protected function getInfoLinks() {
        $html = '';
        $infoLinks = $this->driver->getInfoLinks();
        foreach ($infoLinks as $infoLink) {
          $html .= '<div class="t_ezb_result">';
          $html .= '  <p>';
          $html .= '    <span class="t_ezb_' . $infoLink['access']. '"></span>';
          $html .=         $this->translate("RDS_LINKS_INFO_" . strtoupper($infoLink[info]));
          $html .= '    <span class="t_link"><a target="_blank" href="' . $infoLink['url']. '">&#187;</a></span>';
          $html .= '  </p>';
          $html .= '</div>';
        }
    }
    
    protected function getLinkresolverLink() {
        $html = '<div class="tg">';
        $html .= '  <h2>' . $this->translate("RDS_PROXY_HOLDINGS_MORE_SOURCES") .' </h2>';
        $html .= '  <p>';
        $html .= '    <a class="redi_links_icon" target="linkresolver" href="' . $this->driver->getOpenUrlExternal() . '" title="' . $this->translate("RDS_PROXY_HOLDINGS_REDI_LINKS") . '"></a>';
        $html .= '  </p>';
        $html .= '</div>';
        return $html;
     }
     
     protected function getLoginLink() {
        $followupUrl = $this->view->plugin('serverUrl')->__invoke() . $_SESSION['Search']['last'];
        $target = $this->view->plugin('url')->__invoke('myresearch-home') . '?followupUrl=' . urlencode($followupUrl);
  
        $sessionInitiator = $this->authManager->getManager()->getSessionInitiator($target);
        if ($sessionInitiator) {
            $loginLink = $this->view->plugin('escapeHtmlAttr')->__invoke($sessionInitiator);
        } else {
            $loginLink = $this->view->plugin('url')->__invoke('myresearch-userlogin');
        }
        return $loginLink;
     
     }
     
     protected function getLinkresolverEmbedded() {        
        $locale = $this->getLocale();
        if ($locale) { 
            $openUrl = $this->driver->getOpenUrlExternal() . "&rl_language=" . $locale; 
        } else {
            $openUrl = $this->driver->getOpenUrlExternal();
        }
        
        $xmlResponse = $this->linkresolver->fetchLinks($openUrl);
        $output = substr ($xmlResponse, strpos($xmlResponse,'<div id="services">'));
        $pos = strpos($output, '<div id="services_end">');
        $output = substr ($output, 0, $pos);
        return $output;
    }

    // Helper methods
    
    protected function render($template) {
        return $this->view->render($template);
    }
    
    protected function translate($str) {
        $translation = ($this->translator) ? $this->translator->translate($str) : $str;
        return $translation;
    }
    
    protected function getLocale() {
        $locale = ($this->translator) ? $this->translator->getLocale() : 'de';
        return $locale;
    }
}

?>