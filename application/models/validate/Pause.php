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
 *     Copyright 2012-16 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft ob es zulässig ist 'OhnePause' auszuwählen.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Pause extends Zend_Validate_Abstract {

    const OHNE_ZEIT = 'OhneZeit';
    const ZU_KURZ = 'ZuKurz';
    const ZU_LANG = 'ZuLang';

    protected $_messageTemplates = array(
        self::OHNE_ZEIT => 'Um "Ohne Pause" auswählen zu können, müssen "Beginn" und "Ende" gesetzt sein!',
        self::ZU_LANG => 'Die eingetragene Arbeitszeit ist zu lang, um "Ohne Pause" auswählen zu können!',
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);

        if (!is_array($context)) {
            //keine Daten, also ungültig
            $this->_error(self::OHNE_ZEIT);
            return false;
        }

        $ohnePause = $value == '-' ? false : true;
        if (!$ohnePause) {
            //mit Pause, also alles klar
            return true;
        } else {
            //hole Beginn und Ende 
            $contextBeginn = $context['beginn'];
            $contextEnde = $context['ende'];
            if ($contextBeginn == '' || $contextEnde == '') {
                $this->_error(self::OHNE_ZEIT);
                return false;
            }
            $beginnWert = substr($contextBeginn, 1);
            $endeWert = substr($contextEnde, 1);
            $beginn = new Zend_Date($beginnWert, Zend_Date::TIMES);
            $ende = new Zend_Date($endeWert, Zend_Date::TIMES);

            //berechne die Anwesenheitszeit
            $zeitService = new Azebo_Service_Zeitrechner();
            $anwesend = $zeitService->anwesend($beginn, $ende);

            // hole die Zeiten
            $ns = new Zend_Session_Namespace();
            $pause = $ns->zeiten->pause;
            $mitarbeiter = $ns->mitarbeiter;

            //prüfe
            if ($mitarbeiter->getHochschule() == 'khb') {
                if ($anwesend->compareTime($pause->kurz->ab) == 1) {
                    $this->_error(self::ZU_LANG);
                    return false;
                }
            } else {
                if ($anwesend->compareTime($pause->kurz->ab) != -1) {
                    $this->_error(self::ZU_LANG);
                    return false;
                }
            }
        }
        return true;
    }

}

