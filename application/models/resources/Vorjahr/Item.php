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
 *     Copyright 2015 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Description of Item
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Vorjahr_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Vorjahr_Item_Interface {

//    protected $_dzService;
//
//    public function __construct($config) {
//        parent::__construct($config);
//        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
//    }

        /**
     *
     * @return Azebo_Model_Saldo 
     */
    public function getSaldouebertrag() {
        $stunden = $this->getRow()->saldouebertragstunden;
        $minuten = $this->getRow()->saldouebertragminuten;
        $positiv = $this->getRow()->saldouebertragpositiv == 'ja' ?
                true : false;
        if ($this->getRow()->saldo2007stunden === null) {
            $uebertrag = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        } else {
            $restStunden = $this->getRow()->saldo2007stunden;
            $restMinuten = $this->getRow()->saldo2007minuten;
            $uebertrag = new Azebo_Model_Saldo(
                    $stunden, $minuten, $positiv, true, $restStunden, $restMinuten);
        }
        return $uebertrag;
    }
    
    public function setSaldouebertrag(Azebo_Model_Saldo $saldo) {
        $this->_row->saldouebertragstunden = $saldo->getStunden();
        $this->_row->saldouebertragminuten = $saldo->getMinuten();
        $this->_row->saldouebertragpositiv = $saldo->getPositiv() ? 'ja' : 'nein';
    }

}
