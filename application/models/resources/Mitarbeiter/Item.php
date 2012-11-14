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
class Azebo_Resource_Mitarbeiter_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Mitarbeiter_Item_Interface {

    private $_vorname = '';
    private $_nachname = '';
    private $_rolle = null;
    private $_hochschule = null;

    public function getName() {
        return $this->_vorname . ' ' . $this->_nachname;
    }

    /**
     * Gibt einen Arbeitstag für den angegbenen Tag zurück.
     * Falls ein Arbeitstag in der DB für den angegebenen Tag existiert,
     * wird dieser zurückgegeben, ansonsten ein frisch initialisierter
     * Arbeitstag.
     * 
     * @param Zend_Date $tag
     * @return Azebo_Resource_Arbeitstag_Item_Interface 
     */
    public function getArbeitstagNachTag(Zend_Date $tag) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        return $arbeitstagTabelle->getArbeitstagNachTagUndMitarbeiterId(
                        $tag, $this->id);
    }

    /**
     * Gibt ein Array von Arbeitstagen für den angegebenen Monat zurück.
     * Falls ein Arbeitstag in der DB existiert wird dieser eingefügt, falls
     * nicht wird ein frisch intialisierter Arbeitstag eingefügt.
     * 
     * @param Zend_Date $monat
     * @return array 
     */
    public function getArbeitstageNachMonat(Zend_Date $monat) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        return $arbeitstagTabelle->getArbeitstageNachMonatUndMitarbeiterId(
                        $monat, $this->id);
    }

    public function setRolle(array $gruppen) {
        foreach ($gruppen as $gruppe) {
            if ($gruppe == 'HFS-Zeit' || $gruppe == 'HFM-Zeit' ||
                    $gruppe == 'KHB-Zeit') {
                $this->_rolle = 'bueroleitung';
            }
            if ($gruppe == 'SC-IT-Admin') {
                $this->_rolle = 'scit';
            }
        }
        if ($this->_rolle === null) {
            $this->_rolle = 'mitarbeiter';
        }
    }

    public function getRolle() {
        return $this->_rolle;
    }

    public function setHochschule(array $gruppen) {
        foreach ($gruppen as $gruppe) {
            if ($gruppe == 'HFS-Mitglied') {
                $this->_hochschule = 'hfs';
            } else if ($gruppe == 'HFM-Mitglied') {
                $this->_hochschule = 'hfm';
            } else if ($gruppe == 'KHB-Mitglied') {
                $this->_hochschule = 'khb';
            }
        }
    }

    public function getHochschule() {
        return $this->_hochschule;
    }

    public function saveArbeitstag(Zend_Date $tag, array $daten) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        $arbeitstagTabelle->saveArbeitstag($tag, $this->id, $daten);
    }

    public function setNachname($nachname) {
        $this->_nachname = $nachname;
    }

    public function setVorname($vorname) {
        $this->_vorname = $vorname;
    }

    public function getBeamter() {
        return $this->getRow()->beamter == 'ja' ? true : false;
    }

    //TODO Kommentieren!
    /**
     *
     * @return \Azebo_Model_Saldo 
     */
    public function getSaldouebertrag() {
        $stunden = $this->getRow()->saldouebertragstunden;
        $minuten = $this->getRow()->saldouebertragminuten;
        $positiv = $this->getRow()->saldouebertragpositiv == 'ja' ?
                true : false;
        $uebertrag = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        return $uebertrag;
    }

    public function getArbeitsmonate() {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatsTabelle->getArbeitsmonateNachMitarbeiterId($this->id);
    }

    public function getSaldoBisher() {
        $saldo = $this->getSaldouebertrag();
        $monate = $this->getArbeitsmonate();
        foreach ($monate as $monat) {
            $monatsSaldo = $monat->getSaldo();
            $saldo->add($monatsSaldo);
       }
       //TODO Mehr als 10 Defizitstunden
       return $saldo->getString();
    }

    public function getSaldo(Zend_Date $monat) {
        $arbeitstage = $this->getArbeitstageNachMonat($monat);
        $saldo = new Azebo_Model_Saldo(0, 0, true);

        foreach ($arbeitstage as $arbeitstag) {
            $tagesSaldo = $arbeitstag->getSaldo();
            $saldo->add($tagesSaldo);

        }

        return $saldo->getString();
    }

}