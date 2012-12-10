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
 * Description of IstArbeitstag
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_IstArbeitstag extends Zend_Validate_Abstract {

    const KEIN_ARBEITSTAG = 'KeinArbeitstag';

    protected $_messageTemplates = array(
        self::KEIN_ARBEITSTAG => 'An diesem Tag haben sie keinen Arbeitstag.
            Bitte geben Sie eine Bemerkung an!',
    );

    public function isValid($value, $context = null) {
        //hole den Tag und prüfe auf Feiertag
        $tag = new Zend_Date($context['tag'], 'dd.MM.yyyy');
        $ns = new Zend_Session_Namespace();
        $feiertagsservice = $ns->feiertagsservice;
        $feiertag = $feiertagsservice->feiertag($tag);
        $beginn = $context['beginn'];
        $beginn = substr($beginn, 1);
        if ($beginn != '') {
            $beginn = new Zend_Date($beginn, Zend_Date::TIMES);
        }

        if ($feiertag['feiertag'] == false) {
            //kein Feiertag
            if ($beginn != '') {
                //Beginn und Ende gesetzt also prüfen
                $mitarbeiter = $ns->mitarbeiter;
                $arbeitstag = $mitarbeiter->getArbeitstagNachTag($tag);
                $arbeitsregel = $arbeitstag->getRegel();
                $bemerkung = $context['bemerkung'];
                $bemerkung = trim($bemerkung);
                if($arbeitsregel === null && $bemerkung == '') {
                    $this->_error(self::KEIN_ARBEITSTAG);
                    return false;
                }
            }
        }
        return true;
    }

}

