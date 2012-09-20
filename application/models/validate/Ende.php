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
 * Description of RahmenBeginn
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Ende extends Zend_Validate_Abstract {
    //Zeiten
    //TODO Konfiguration für die Zeiten!

    const RAHMEN_ENDE = '20:00:00';
    const KERN_ENDE_NORM = '14:30:00';
    const KERN_ENDE_FR = '14:00:00';

    //Fehlermeldungen
    const NACH_RAHMEN = 'EndeNachRahmen';
    const VOR_KERN = 'EndeVorKern';

    protected $_messageTemplates = array(
        self::NACH_RAHMEN => 'Das eingetragene Ende liegt nach dem Ende der
            Rahmenarbeitszeit! Bitte geben Sie eine Bemerkung ein.',
        self::VOR_KERN => 'Das eingetragene Ende liegt vor dem Ende der
            Kernarbeitszeit! Bitte geben Sie eine Bemerkung und/oder
            Dienstbefeiung ein.',
    );

    public function isValid($value, $context = null) {
        //hole den Tag und prüfe auf Feiertag
        $tag = new Zend_Date($context['tag'], 'dd.MM.YYYY');
        $ns = new Zend_Session_Namespace();
        $feiertagsservice = $ns->feiertagsservice;
        $feiertag = $feiertagsservice->feiertag($tag);

        if ($feiertag['feiertag'] == false) {
            //kein Feiertag, also prüfen
            //hole die Daten
            $rahmenEnde = new Zend_Date(self::RAHMEN_ENDE, Zend_Date::TIMES);
            $kernEndeNorm = new Zend_Date(self::KERN_ENDE_NORM, Zend_Date::TIMES);
            $kernEndeFr = new Zend_Date(self::KERN_ENDE_FR, Zend_Date::TIMES);
            $bemerkung = $context['bemerkung'];
            $bemerkung = trim($bemerkung);
            $befreiung = $context['befreiung'];
            $befreiung = trim($befreiung);
            if ($tag->get(Zend_Date::WEEKDAY_DIGIT) == 5) {
                $kernEnde = $kernEndeFr;
            } else {
                $kernEnde = $kernEndeNorm;
            }

            //prüfe
            if ($value->compareTime($rahmenEnde) == 1) {
                //nach Ende der Rahmenarbeitszeit
                if ($bemerkung == '') {
                    $this->_error(self::NACH_RAHMEN);
                    return false;
                }
            }
            if ($value->compareTime($kernEnde) == -1) {
                //vor Ende der Kernarbeitszeit
                if ($bemerkung == '' && $befreiung == 'keine') {
                    $this->_error(self::VOR_KERN);
                    return false;
                }
            }
        }

        return true;
    }

}

