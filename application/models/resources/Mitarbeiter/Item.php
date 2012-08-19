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

    protected $_rolle = null;
    protected $_hochschule = null;

    public function getName() {
        return $this->getRow()->vorname . ' ' . $this->getRow()->nachname;
    }

    public function getArbeitstagNachTag(Zend_Date $tag) {
        $select = $this->select()->where('tag = ?', $tag->toString('yyyy-MM-dd'));
        $row = $this->findDependentRowset('Azebo_Resource_Arbeitstag', 'Arbeitstag', $select);
        return $row->current();
    }

    /**
     * @param Zend_Date $monat
     * @return array 
     */
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

    /**
     * Setzt die (ACL-)Rolle eines Mitarbeiters.
     * Erwartet ein Array mit den Namen
     * der (LDAP-)Gruppen in denen der Mitarbeiter Mitglied ist.
     * 
     * @param array $gruppen 
     */
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

    public function setHochschule($gruppen) {
        foreach ($gruppen as $gruppe) {
            if ($gruppe == 'HFS-Mitglied'){
                $this->_hochschule = 'hfs';
            }
            else if ($gruppe == 'HFM-Mitglied') {
                $this->_hochschule = 'hfm';
            }
            else if ($gruppe == 'KHB-Mitglied') {
                $this->_hochschule = 'khb';
            }
        }
    }

}

