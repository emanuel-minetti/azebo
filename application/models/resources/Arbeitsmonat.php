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
 * Schnittstelle zur MySQL-Tabelle 'arbeitsmonat'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsmonat extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Arbeitsmonat_Interface {

    protected $_name = 'arbeitsmonat';
    protected $_primary = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitsmonat_Item';
    protected $_referenceMap = array(
        'Arbeitsmonat' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );

    public function getArbeitsmonateNachMitarbeiterId($mitarbeiterId) {

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        $dbMonate = $this->fetchAll($select);
        //TODO Nochmal anschauen!

        return $dbMonate;
    }

    public function saveArbeitsmonat($mitarbeiterId, Zend_Date $monat, Azebo_Model_Saldo $saldo, $urlaub = 0) {
        $arbeitsmonat = $this->createRow();
        $arbeitsmonat->mitarbeiter_id = $mitarbeiterId;
        $arbeitsmonat->setMonat($monat);
        $arbeitsmonat->setSaldo($saldo);
        $arbeitsmonat->urlaub = $urlaub;
        $arbeitsmonat->save();
    }

}

