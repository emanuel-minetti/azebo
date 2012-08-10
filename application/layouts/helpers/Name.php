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
 * Prüft ob ein Bentzer angemeldet ist und gibt, falls ja, den vollen Namen
 * des Benutzers zurück.
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Name extends Zend_View_Helper_Abstract {
    
    public function name() {
        $authService = new Azebo_Service_Authentication();
        $identity = $authService->getIdentity();
        $name = '';
        if($identity !== null) {
            $name = $identity->getName();
        }
        return $name;
    }
}

