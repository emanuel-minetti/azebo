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
class Azebo_Resource_Arbeitsmonat_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Arbeitsmonat_Item_Interface {

    protected $_dzService;

    public function __construct($config) {
        parent::__construct($config);
        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
    }

    /**
     * @return Azebo_Model_Saldo 
     */
    public function getSaldo() {
        $stunden = $this->getRow()->saldostunden;
        $minuten = $this->getRow()->saldominuten;
        $positiv = $this->getRow()->saldopositiv == 'ja' ? true : false;
        if ($this->getRow()->saldo2007stunden === null) {
            $saldo = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        } else {
            $restStunden = $this->getRow()->saldo2007stunden;
            $restMinuten = $this->getRow()->saldo2007minuten;
            $saldo = new Azebo_Model_Saldo($stunden, $minuten, $positiv, true, $restStunden, $restMinuten);
        }
        return $saldo;
    }

    public function setSaldo(Azebo_Model_Saldo $saldo) {
        $this->getRow()->saldostunden = $saldo->getStunden();
        $this->getRow()->saldominuten = $saldo->getMinuten();
        $this->getRow()->saldopositiv = $saldo->getPositiv() ? 'ja' : 'nein';
        if($saldo->getRest()) {
            $restStunden = $saldo->getRestStunden();
            $restMinuten = $saldo->getRestMinuten();
            if($restStunden == 0 && $restMinuten == 0) {
                $restStunden = null;
                $restMinuten = null;
            }
            $this->getRow()->saldo2007stunden = $restStunden;
            $this->getRow()->saldo2007minuten = $restMinuten;
        }
    }

    /**
     * @return Zend_Date 
     */
    public function getMonat() {
        return $this->_dzService->datumSqlZuPhp($this->_row->monat);
    }

    public function setMonat(Zend_Date $monat) {
        $this->_row->monat = $this->_dzService->datumPhpZuSql($monat);
    }

}

