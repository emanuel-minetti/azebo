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
 * Prüft ob ein Benutzer angemeldet ist und gibt, falls ja, die Farbpräferenzen
 * des Mitarbeiters als Array von Strings zurück. Falls nicht wird NULL
 * zurückgegeben.
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Farbe extends Zend_View_Helper_Abstract {
    
    public function farbe() {        
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $farben = null;
        if($mitarbeiter !== null) {
            $farben = $mitarbeiter->getFarben();
        }
        return $farben;
    }
}

