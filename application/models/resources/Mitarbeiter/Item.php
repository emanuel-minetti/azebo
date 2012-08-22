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
class Azebo_Resource_Mitarbeiter_Item 
    extends AzeboLib_Model_Resource_Db_Table_Row_Abstract
    implements Azebo_Resource_Mitarbeiter_Item_Interface {

    private $_rolle = null;
    private $_hochschule = null;

    public function getName() {
        return $this->getRow()->vorname . ' ' . $this->getRow()->nachname;
    }

    public function getArbeitstagNachTag(Zend_Date $tag) {
        $select = $this->select()->where('tag = ?', $tag->toString('yyyy-MM-dd'));
        $row = $this->findDependentRowset('Azebo_Resource_Arbeitstag', 'Arbeitstag', $select);
        return $row->current();
    }

    public function getArbeitstageNachMonat(Zend_Date $monat) {
        $erster = new Zend_Date($monat);
        $erster->setDay(1);
        $letzter = new Zend_Date($monat);
        $letzter->setDay($monat->get(Zend_Date::MONTH_DAYS));

        $select = $this->select();
        $select->where('tag >= ?', $erster->toString('yyyy-MM-dd'))
                ->where('tag <= ?', $letzter->toString('yyyy-MM-dd'))
                ->order('tag ASC');
        $rowset = $this->findDependentRowset('Azebo_Resource_Arbeitstag', 'Arbeitstag', $select);
        $arbeitstage = array();
        foreach ($rowset as $arbeitstag) {
            array_push($arbeitstage, $arbeitstag);
        }
        return $arbeitstage;
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
        //$log = Zend_Registry::get('log');
        
        //Hohle den Arbeitstag aus der Tabelle
        $arbeitstag = $this->getArbeitstagNachTag($tag);
        
        //Falls der Arbeitstag noch nicht in der Tabelle existierte,
        //setze die Spalten, die nicht NULL sein dürfen.
        if($arbeitstag === null) {
            $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
            $arbeitstag = $arbeitstagTabelle->createRow();
            $arbeitstag->mitarbeiter_id = $this->id;
            $arbeitstag->tag = $tag->toString('yyyy-MM-dd');
        }
        
        //Setze die Daten
        $arbeitstag->beginn = substr($daten['beginn'], 1);
        $arbeitstag->ende = substr($daten['ende'], 1);
        $arbeitstag->befreiung = $daten['befreiung'];
        $arbeitstag->bemerkung = $daten['bemerkung'];
        $arbeitstag->pause = $daten['pause'];
       
        $arbeitstag->save();
    }

}

