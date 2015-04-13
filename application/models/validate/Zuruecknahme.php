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
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft den eingetragenen Beginn der Arbeitszeit gegen den in der DB oder der
 * Konfiguration festgelegten Rahmen- und Kernbeginn. An Feiertagen wird nicht
 * geprüft.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Zuruecknahme extends Zend_Validate_Abstract {

    const ZURUECK = 'JahresZuruecknahme';

    protected $_messageTemplates = array(
        self::ZURUECK => 'Der Abschluss kann nicht zurückgenommen werden.
            Es können nur Jahresabschlüsse zurückgenommen werden, die nach
            der Version 1.24 des Arbeitszeitbogens vorgenommen wurden!',
     );

    public function isValid($value, $context = null) {
        //TODO Hier muss getestet werden, ob das Zurücknehmen einen Jahresabschluss
        //TODO zurücknimmt. Falls das der Fall ist muss geprüft werden, ob ein Eintrag
        //TODO für das Vorjahr vorliegt. Falls nicht, darf die Zurücknahme nicht erfolgen!!
        $log = Zend_Registry::get('log');
        $log->debug('Hallo vom Zurücknahme-Validator!');
        $log->debug(print_r($context, TRUE));
    }

}