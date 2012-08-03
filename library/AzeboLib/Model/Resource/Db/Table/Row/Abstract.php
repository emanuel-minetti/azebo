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
 * Diese Klasse sollte von allen Azebo_Model_Resource_Item-Klassen erweitert
 * werden, die eine DB-Tabelle zur Grundlage haben.
 * 
 * Ein einfacher Wrapper zur Zend_Db_Table_Row-Klasse
 *
 * @author Emanuel Minetti
 */
abstract class AzeboLib_Model_Resource_Db_Table_Row_Abstract {
    
    protected $_row = null;
    
    public function __construct(array $config = array()) {
        $this->setRow($config);
    }
    
    public function __get($columnName) {
        $lazyLoder = 'get' . ucfirst($columnName);
        if(method_exists($this, $lazyLoder)) {
            return $this->$lazyLoder();
        }
        
        return $this->getRow()->__get($columnName);
    }
   
    public function __isset($columnName) {
        return $this->getRow()->__isset($columnName);
    }
    
    public function __set($columnName, $value) {
        return $this->getRow()->__set($columnName, $value);
    }
    
    public function getRow() {
        return $this->_row;
    }
    
    public function setRow(array $config = array()) {
        $rowClass = 'Zend_Db_Table_Row';
        if(isset($config['rowClass'])) {
            $rowClass = $config['rowClass'];
        }
        
        if(is_string($rowClass)) {
            $this->_row = new $rowClass($config);
            return;
        }
        
        if(is_object($rowClass)) {
            $this->_row = $rowClass;
            return;
        }
        
        throw new AzeboLib_Model_Exception(
                "Die 'rowClass' konnte nicht gesetzt werden in " . __CLASS__);
    }
    
    public function __call($method, array $arguments) {
        return call_user_func_array(array($this->getRow(), $method), $arguments);
    }
    
    public function __wakeup() {
        if(!$this->getRow()->isConnected()) {
            $tableClass = $this->getRow()->getTableClass();
            $table = new $tableClass();
            $this->getRow()->setTable($table);
        }
    }
    
}
