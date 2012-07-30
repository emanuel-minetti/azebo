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
 * Description of Mitarbeiter
 *
 * @author Emanuel Minetti
 */
class Azebo_Model_Mitarbeiter extends AzeboLib_Model_Abstract {

    public function getMitarbeiterNachId($id) {
        $id = (int) $id;
        return $this->getResource('Mitarbeiter')->getMitarbeiterNachId($id);
    }

    public function getMitarbeiterNachBenutzername($benutzername) {
        return $this->getResource('Mitarbeiter')
                ->getMitarbeiterNachBenutzername($benutzername);
    }
}

