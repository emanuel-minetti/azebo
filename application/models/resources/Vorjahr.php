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
 * Schnittstelle zur MySQL-Tabelle 'vorjahr'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Vorjahr extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Vorjahr_Interface {

    protected $_name = 'vorjahr';
    protected $_primary = 'id';
    protected $_rowClass = 'Azebo_Resource_Vorjahr_Item';
    protected $_referenceMap = array(
        'Vorjahr' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );
    
    
    public function getVorjahrNachMitarbeiterId($mitarbeiterId) {

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        $vorjahr = $this->fetchAll($select);

        return $vorjahr;
    }
    
}