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
 * Prüft ob durch den Abschluss eines Monats der Resturlaub des laufenden Jahres
 * negative Werte annehmen würde.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Azv extends Zend_Validate_Abstract {
    
    const ZU_VIEL = 'zuViel';
    
    protected $_messageTemplates = array(
        self::ZU_VIEL => 'Die in diesem Monat eingetragenen AZV-Tage
            überschreiten Ihren Rest. Bitte passen Sie die Eintäge
            entsprechend an!',
    );
    
    public function isValid($value, $context = null) {

        // hole die Daten
        $monat = new Zend_Date($context['monat'], 'MM.yyyy');
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $rest = $mitarbeiter->getAzvTage() -$mitarbeiter->getAzvTageBisher($monat);
        $rest -= $mitarbeiter->getAzvTageNachMonat($monat);
        
        // teste
        if($rest < 0) {
            $this->_error(self::ZU_VIEL);
            return false;
        }
        return true;
    }
}
