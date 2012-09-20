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
 * Description of Beginn
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Beginn extends Zend_Validate_Abstract {
    
    //Zeiten
    //TODO Konfiguration für die Zeiten!
    const RAHMEN_BEGINN = '07:00:00';
    const KERN_BEGINN = '09:30:00';

    //Fehlermeldungen
    const VOR_RAHMEN = 'BeginnVorRahmen';
    const NACH_KERN = 'BeginnNachKern';

    protected $_messageTemplates = array(
        self::VOR_RAHMEN => 'Der eingetragene Beginn liegt vor dem Beginn der
            Rahmenarbeitszeit! Bitte geben Sie eine Bemerkung ein.',
        self::NACH_KERN => 'Der eingetragene Beginn liegt nach dem Beginn der
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
            $rahmenBeginn = new Zend_Date(self::RAHMEN_BEGINN, Zend_Date::TIMES);
            $kernBeginn = new Zend_Date(self::KERN_BEGINN, Zend_Date::TIMES);
            $bemerkung = $context['bemerkung'];
            $bemerkung = trim($bemerkung);
            $befreiung = $context['befreiung'];
            $befreiung = trim($befreiung);

            //prüfe
            if ($value->compareTime($rahmenBeginn) == -1) {
                //vor Beginn der Rahmenarbeitszeit
                if ($bemerkung == '') {
                    $this->_error(self::VOR_RAHMEN);
                    return false;
                }
            }
            if ($value->compareTime($kernBeginn) == 1) {
                //nach Beginn der Kernarbeitszeit
                if ($bemerkung == '' && $befreiung == 'keine') {
                    $this->_error(self::NACH_KERN);
                    return false;
                }
            }
        }
        return true;
    }

}

