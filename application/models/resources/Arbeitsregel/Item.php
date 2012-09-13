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
 * Description of Item
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsregel_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Arbeitsregel_Item_Interface {
    
    protected $_dzService;

    public function __construct($config) {
        parent::__construct($config);
        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
    }

    public function getBis() {
        return $this->_dzService->datumSqlZuPhp($this->_row->bis);
    }

    public function setBis($bis) {
        $this->_row->bis = $this->_dzService->datumPhpZuSql($bis);
        
    }

    public function getVon() {
        return $this->_dzService->datumSqlZuPhp($this->_row->von);
    }

    public function setVon($von) {
        $this->_row->von = $this->_dzService->datumPhpZuSql($von);
    }

    public function getSoll() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->soll);
    }

}
