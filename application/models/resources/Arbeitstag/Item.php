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
class Azebo_Resource_Arbeitstag_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Arbeitstag_Item_Interface {

    protected $_feiertagsService;
    protected $_feiertag;

    public function getBeginn() {
        if ($this->_row->beginn !== null) {
            return new Zend_Date($this->_row->beginn, Zend_Date::TIME_MEDIUM);
        } else {
            return null;
        }
    }

    public function getEnde() {
        if ($this->_row->ende !== null) {
            return new Zend_Date($this->_row->ende, Zend_Date::TIME_MEDIUM);
        } else {
            return null;
        }
    }

    public function setBeginn($beginn) {
        $this->_row->beginn = $beginn === null ?
                null : $beginn->toString('HH:mm:ss');
    }

    public function setEnde($ende) {
        $this->_row->ende = $ende === null ?
                null : $ende->toString('HH:mm:ss');
    }

    public function getTag() {
        if ($this->_row->tag !== null) {
            return new Zend_Date($this->_row->tag, 'yyyy-MM-dd');
        } else {
            return null;
        }
    }

    public function setTag($tag) {
        $this->_row->tag = $tag === null ?
                null : $tag->toString('yyyy-MM-dd');
    }

    public function getFeiertag() {
        if ($this->_feiertag === null) {
            if ($this->_feiertagsService === null) {
                $ns = new Zend_Session_Namespace();
                $this->_feiertagsService = $ns->feiertagsservice;
            }
            if ($this->_feiertagsService !== null) {
                $this->_feiertag =
                        $this->_feiertagsService->feiertag($this->getTag());
            }
        }
        return $this->_feiertag;
    }

}

