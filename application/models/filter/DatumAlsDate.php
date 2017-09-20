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
 *     Copyright 2012-17 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Description of DatumAlsDate
 *
 * @author Emanuel Minetti
 */
class Azebo_Filter_DatumAlsDate implements Zend_Filter_Interface {
    
    public function filter($wert) {
        $wert = $wert == '' ? null: $wert;
        
        //return NULL oder Zend_Date
        if ($wert === null) {
            $date = null;
        } else {
            $date = new Zend_Date();
            $date->setDate($wert, 'yyyy-MM-dd');
        }
        return $date;
    }
}

