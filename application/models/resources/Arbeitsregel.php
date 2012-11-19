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
 * Schnittstelle zur MySQL-Tabelle 'arbeitstag'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsregel extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Arbeitsregel_Interface {

    protected $_name = 'arbeitsregel';
    protected $_id = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitsregel_Item';
    protected $_referenceMap = array(
        'Arbeitsregel' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );

    public function getArbeitsregelnNachMonatUndMitarbeiterId(
            Zend_Date $monat, $mitarbeiterId) {
        
        // Ersten und Letzten des Monats finden
        $erster = new Zend_Date($monat);
        $letzter = new Zend_Date($monat);
        $erster->setDay(1);
        $letzter->setDay($monat->get(Zend_Date::MONTH_DAYS));

        // alle Regeln für den Mitarbeiter finden
        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        $dbRegeln = $this->fetchAll($select);

        // die Regeln, die diesen Monat gelten finden
        $regeln = array();
        foreach ($dbRegeln as $dbRegel) {
            if ($dbRegel->von->compare($erster) != 1) {
                if ($dbRegel->bis === null) {
                    array_push($regeln, $dbRegel);
                } else {
                    if ($dbRegel->bis->compare($letzter) != -1) {
                        array_push($regeln, $dbRegel);
                    }
                }
            }
        }

        return $regeln;
    }

}

