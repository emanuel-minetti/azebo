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
    protected $_dzService;
    protected $_feiertag;
    protected $_regel;

    public function __construct($config) {
        parent::__construct($config);
        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $ns = new Zend_Session_Namespace();
        $this->_feiertagsService = $ns->feiertagsservice;
    }

    public function getBeginn() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->beginn);
    }

    public function getEnde() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->ende);
    }

    public function setBeginn($beginn) {
        $this->_row->beginn = $this->_dzService->zeitPhpZuSql($beginn);
    }

    public function setEnde($ende) {
        $this->_row->ende = $this->_dzService->zeitPhpZuSql($ende);
    }

    public function getTag() {
        return $this->_dzService->datumSqlZuPhp($this->_row->tag);
    }

    public function setTag($tag) {
        $this->_row->tag = $this->_dzService->datumPhpZuSql($tag);
    }

    public function getFeiertag() {
        if ($this->_feiertag === null && $this->_feiertagsService !== null) {
            $this->_feiertag =
                    $this->_feiertagsService->feiertag($this->getTag());
        }
        return $this->_feiertag;
    }

    public function getRegel() {
        if ($this->_regel === null) {
            $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
            $arbeitsregeln = $arbeitsregelTabelle->
                    getArbeitsregelNachMonatUndMitarbeiterId(
                    $this->getTag(), $this->mitarbeiter_id);
            foreach ($arbeitsregeln as $arbeitsregel) {
                if ($arbeitsregel->wochentag == 'alle') {
                    if ($arbeitsregel->kalenderwoche == 'alle') {
                        $this->_regel = $arbeitsregel;
                        break;
                    } else {
                        $kwUngerade = $this->getTag()->get(Zend_Date::WEEK) % 2;
                        if (($kwUngerade && $arbeitsregel->kalenderwoche == 'ungerade') ||
                                (!$kwUngerade && $arbeitsregel->kalenderwoche == 'gerade')) {
                            $this->_regel = $arbeitsregel;
                            break;
                        }
                    }
                } else {
                    if ($arbeitsregel->wochentag ==
                            strtolower($this->getTag()->get(Zend_Date::WEEKDAY))) {
                        if ($arbeitsregel->kalenderwoche == 'alle') {
                            $this->_regel = $arbeitsregel;
                            break;
                        } else {
                            $kwUngerade = $this->getTag()->get(Zend_Date::WEEK) % 2;
                            if (($kwUngerade && $arbeitsregel->kalenderwoche == 'ungerade') ||
                                    (!$kwUngerade && $arbeitsregel->kalenderwoche == 'gerade')) {
                                $this->_regel = $arbeitsregel;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $this->_regel;
    }

}

