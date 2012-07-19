<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public $frontController;
    protected $_logger;


    protected function _initLogging() {
        $this->bootstrap('frontController');
        $logger = new Zend_Log();
        
        $writer = 'production' == $this->getEnvironment() ?
                new Zend_Log_Writer_Stream(APPLICATION_PATH .
                        '/../data/logs/azebo.log') :
                new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);
        
        if('production' == $this->getEnvironment()) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
            $logger->addFilter($filter);
        }
        
        $this->_logger = $logger;
        Zend_Registry::set('log', $logger);
            
    }


    protected function _initLocale() {
        
        $this->_logger->info('Bootstrap ' . __METHOD__);
        
        $locale = new Zend_Locale('de_DE');
        Zend_Registry::set('Zend_Locale', $locale);
    }
    
    protected function _initViewSettings() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $this->bootstrap('view');

        $this->_view = $this->getResource('view');

        // set encoding and doctype
        $this->_view->setEncoding('UTF-8');
        $this->_view->doctype('XHTML1_STRICT');

        // set the content type and language
        $this->_view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $this->_view->headMeta()->appendHttpEquiv('Content-Language', 'de_DE');
    }


}

