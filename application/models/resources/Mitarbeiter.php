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
 * Schnittstelle zur MySQL-Tabelle 'mitarbeiter'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Mitarbeiter
extends AzeboLib_Model_Resource_Db_Table_Abstract
implements Azebo_Resource_Mitarbeiter_Interface {

    protected $_name = 'mitarbeiter';
    protected $_primary = 'id';
    protected $_rowClass = 'Azebo_Resource_Mitarbeiter_Item';

    public function getMitarbeiterNachId($id) {
        return $this->find($id)->current();
    }

    public function getMitarbeiterNachBenutzername($benutzername) {
        $select = $this->select();
        $select->where('benutzername = ?', $benutzername);

        return $this->fetchRow($select);
    }

}

