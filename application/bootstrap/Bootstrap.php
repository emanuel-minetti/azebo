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

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public $frontController;
    protected $_logger;
    protected $_resourceLoader;

    protected function _initLogging() {
        $this->bootstrap('frontController');
        $logger = new Zend_Log();
        
        $streamWriter = new Zend_Log_Writer_Stream(APPLICATION_PATH .
                        '/../data/logs/azebo.log');
        $logger->addWriter($streamWriter);

        if ('production' == $this->getEnvironment()) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
            $logger->addFilter($filter);
        } else {
            $firebugWriter = new Zend_Log_Writer_Firebug();
            $logger->addWriter($firebugWriter);
        }

        $this->_logger = $logger;
        Zend_Registry::set('log', $logger);
    }

    protected function _initSetFrontController() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $this->bootstrap('frontController');
        $this->frontController = Zend_Controller_Front::getInstance();
    }

    protected function _initResourceAutoloader() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $this->_resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => 'Azebo',
                    'basePath' => APPLICATION_PATH,
                ));

        $this->_resourceLoader->addResourceTypes(array(
            'model' => array(
                'path' => 'models',
                'namespace' => 'Model',
            ),
            'modelResource' => array(
                'path' => 'models/resources',
                'namespace' => 'Resource',
            ),
            'form' => array(
                'path' => 'forms',
                'namespace' => 'Form',
            ),
            'service' => array(
                'path' => 'services',
                'namespace' => 'Service',
            ),
            'plugin' => array(
                'path' => 'plugins',
                'namespace' => 'Plugin',
            ),
        ));
    }

    protected function _initLoadAclIni() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/acl.ini');
        Zend_Registry::set('acl', $config);
    }

    protected function _initAclFrontControllerPlugins() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $this->bootstrap('frontController');
        $this->bootstrap('loadAclIni');


        $plugin = new Azebo_Plugin_Acl(new AzeboLib_Acl_Acl());
        $this->frontController->registerPlugin($plugin);
    }

    protected function _initLocale() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $locale = new Zend_Locale('de_DE');
        Zend_Registry::set('Zend_Locale', $locale);

        $translator = new Zend_Translate(array(
                    'adapter' => 'array',
                    'content' => APPLICATION_PATH . '/../resources/languages',
                    'locale' => 'de',
                    'scan' => Zend_Translate::LOCALE_DIRECTORY,
                ));
        Zend_Validate_Abstract::setDefaultTranslator($translator);
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
            'href' => '/images/logo.ico'), 'APPEND');
    }

    protected function _initDbProfiler() {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        if ('production' !== $this->getEnvironment()) {
            $this->bootstrap('db');
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);
            $this->getPluginResource('db')->getDbAdapter()
                    ->setProfiler($profiler);
        }
    }
    
    protected function _initRoutes() {
        $this->bootstrap('frontController');
        $router = $this->frontController->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
        $router->addDefaultRoutes();
        $router->addConfig($config, 'routes');
        
    }

}

