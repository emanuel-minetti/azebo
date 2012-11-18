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
 * Description of zehnStunden
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_ZehnStunden extends Zend_Validate_Abstract {

    const MEHR_ALS_ZEHN_STUNDEN = 'MehrAlsZehnStunden';

    protected $_messageTemplates = array(
        self::MEHR_ALS_ZEHN_STUNDEN => 'Die tägliche Arbeitszeit darf 10 Stunden nicht überschreiten!
            Bitte geben Sie eine Bemerkung an!'
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);

        if (is_array($context)) {
            if (isset($context['beginn'])) {
                $ende = new Zend_Date($value);
                // In context liegen die Daten ungefiltert vor, also filtere
                // selber
                $contextBeginn = $context['beginn'];
                if ($contextBeginn == '') {
                    // Kein Beginn eingetragen, also gültig.
                    return true;
                } else {
                    // Beginn und Ende eingetragen, also teste
                    $beginnWert = substr($contextBeginn, 1);
                    $beginn = new Zend_Date($beginnWert, Zend_Date::TIMES);
                    $zeitService = new Azebo_Service_Zeitrechner();
                    $anwesend = $zeitService->anwesend($beginn, $ende);
                    $bemerkung = $context['bemerkung'];
                    $bemerkung = trim($bemerkung);
                    if ($anwesend !== null) {
                        if ($anwesend->compareTime('10:00:00', 'HH:mm:ss') == 1 &&
                                $bemerkung == '') {
                            $this->_error(self::MEHR_ALS_ZEHN_STUNDEN);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

}

