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
    protected $_zeitrechnerService;
    
    protected $_feiertag;
    protected $_regel;
    protected $_ist;
    protected $_anwesend;
    protected $_saldo;

    public function __construct($config) {
        parent::__construct($config);
        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $ns = new Zend_Session_Namespace();
        $this->_feiertagsService = $ns->feiertagsservice;
        $this->_zeitrechnerService = new Azebo_Service_Zeitrechner();
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

    /**
     * Liefert ein Array mit den Eigenschaften 'name' und 'feiertag'
     * zurück. 'name' ist ein string mit dem Namen des Feiertags.
     * 'feiertag' ist ein boolean, der true ist falls das Datum ein
     * Feiertag ist.
     * 
     * @return array
     */
    public function getFeiertag() {
        if ($this->_feiertag === null && $this->_feiertagsService !== null) {
            $this->_feiertag =
                    $this->_feiertagsService->feiertag($this->getTag());
        }
        return $this->_feiertag;
    }

    /**
     * Holt die Arbeitsregel für diesen Tag aus der DB.
     * 
     * Gibt es für diesen Tag keine Regel oder ist dieser Tag ein Feier-, Sonn-
     * oder Samstag wird NULL zurückgegeben, ansonsten ein Objekt vom Typ
     * Azebo_Resource_Arbeitsregel_Item_Interface
     * 
     * @return null|Azebo_Resource_Arbeitsregel_Item_Interface die Regel
     */
    public function getRegel() {
        //Prüfe, ob die Regel für diesen Tag schon gesetzt ist. Falls ja,
        //gib sie einfach zurück.
        if ($this->_regel === null) {
            //Hole die Regeln für den ganzen Monat
            $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
            $arbeitsregeln = $arbeitsregelTabelle->
                    getArbeitsregelnNachMonatUndMitarbeiterId(
                    $this->getTag(), $this->mitarbeiter_id);
            //Iteriere über die Regeln
            foreach ($arbeitsregeln as $arbeitsregel) {
                if ($arbeitsregel->wochentag == 'alle') {
                    //Regel gilt für 'alle' Wochentage
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
                    //Regel gilt für einen Wochentag
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
            //Falls dieser Tag ein 'Feiertag' ist, gib NULL zurück
            $feiertag = $this->getFeiertag();
            if ($feiertag['feiertag']) {
                $this->_regel = null;
            }
        }
        return $this->_regel;
    }

    public function getAnwesend() {
        if ($this->_anwesend === null) {
            if ($this->getBeginn() !== null && $this->getEnde() !== null) {
                $this->_anwesend = $this->_zeitrechnerService->anwesend(
                        $this->getBeginn(), $this->getEnde());
            }
        }

        return $this->_anwesend;
    }

    public function getIst() {
        if ($this->_ist === null) {
            if ($this->getAnwesend() !== null) {
                $ohnePause = $this->pause == '-' ? false : true;
                $this->_ist = $this->_zeitrechnerService->ist(
                        $this->_anwesend, $ohnePause);
            }
        }

        return $this->_ist;
    }

    public function getSaldo() {
        if ($this->_saldo === null) {
            if ($this->getIst() !== null) {
                $this->_saldo = $this->_zeitrechnerService->saldo(
                        $this->_ist, $this->getRegel());
            } else {
                //TODO Gleitzeittage anrechnen!!!!
                $this->_saldo = new Azebo_Model_Saldo(0, 0, true);
            }
        }

        return $this->_saldo;
    }

}
