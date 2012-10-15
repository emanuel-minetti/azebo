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
 * Description of Monat
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Monat extends Zend_Validate_Abstract {

    const TEST = 'Test';

    protected $_messageTemplates = array(
        self::TEST => 'Dies ist ein Test!',
    );

    public function isValid($value, $context = null) {
        $log = Zend_Registry::get('log');
        $log->debug(__METHOD__);

        // hole die Daten
        $monat = new Zend_Date($context['monat'], 'MM.YYYY');
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $model = new Azebo_Model_Mitarbeiter();
        $arbeitstage = $model->getArbeitstageNachMonatUndMitarbeiter(
                $monat, $mitarbeiter);
        
        $log->debug('Zu prüfenden Monat: ' . $monat->toString('MM.YYYY'));
        $log->debug('Mitarbeiter: ' . $mitarbeiter->getName());
        foreach ($arbeitstage as $arbeitstag) {
            if ($arbeitstag !== null && $arbeitstag->getBeginn() !== null) {
                $log->debug('Arbeitstag: ' . $arbeitstag->getTag()->toString('dd.MM') . ' ' . $arbeitstag->getBeginn()->toString('HH:mm'));
            }
        }
        
        //TODO prüfen, ob alle nötigen Tage ausgefüllt sind
        foreach ($arbeitstage as $arbeitstag) {
            if($arbeitstag->getRegel() !== null) {
                
            }
        }


        $this->_error(self::TEST);
        return false;
    }

}

