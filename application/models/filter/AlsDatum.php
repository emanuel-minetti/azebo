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
 * Macht aus dem von Dojo übergebenen Wert entweder NULL oder ein Zend_Date.
 *
 * @author Emanuel Minetti
 */
class Azebo_Filter_AlsDatum implements Zend_Filter_Interface {

    public function filter($wert) {
        //Das 'T' entfernen, das Dojo vor die Uhrzeit setzt
        $wert = $wert == '' ? null : substr($wert, 1);
        
        //return NULL oder Zend_Date
        if ($wert === null) {
            $datum = null;
        } else {
            $datum = new Zend_Date();
            $datum->set($wert, Zend_Date::TIMES);
        }
        return $datum;
    }

}

