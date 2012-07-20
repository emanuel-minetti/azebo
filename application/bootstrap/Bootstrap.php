<?php
/*
 * 
 *     This file is part of azebo.
 * 
 *     azebo is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 * 
 *     azebo is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with azebo.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) arcor.de)
 */

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

        // Encoding und Doctype setzen
        $this->_view->setEncoding('UTF-8');
        $this->_view->doctype('XHTML1_STRICT');

        // MIME-Type und Sprache setzen
        $this->_view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $this->_view->headMeta()->appendHttpEquiv('Content-Language', 'de_DE');
        
        //Titel für die ganze Site setzen
        $this->_view->headTitle('Arbeitszeitbogen');
        $this->_view->headTitle()->setSeparator(' - ');
        
        //CSS-Links setzen
        $this->_view->headLink()->appendStylesheet('/css/style.css');
        
        //JS einbinden
        $this->_view->headScript()->appendFile('/js/nav.js');
        
        //Icon setzen
        $this->_view->headLink(array(
            'rel' => 'favicon',
            'href' => '/images/logo.ico'), 
                'APPEND');
    }
}

