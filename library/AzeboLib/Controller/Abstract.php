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

/**
 * Stellt einen Logger und einen Seitennamen zur Verfügung.
 * 
 * Alle implementierenden Klassen sollten 'parent::init()' aufrufen.
 *
 * @author Emanuel Minetti
 */
abstract class AzeboLib_Controller_Abstract extends Zend_Controller_Action {
    
    /**
     * Der Logger.
     *
     * @var Zend_Log 
     */
    protected $_log;
    protected $_seitenName;
    
    /**
     *Sollte von jeder implementierenden Klasse aufgerufen werden! 
     */
    public function init() {
        $this->_log = Zend_Registry::get('log');
        $this->_seitenName = $this->getSeitenName();
        $this->view->seitenName = $this->_seitenName;
        if($this->_request->getParam('istBueroleitung', false)) {
            $this->view->istBueroleitung = true;
        } else {
            $this->view->istBueroleitung = false;
        }
    }
    
    /**
     *Sollte einen Seitennamen zurückliefern. 
     */
    abstract function getSeitenName();
}

