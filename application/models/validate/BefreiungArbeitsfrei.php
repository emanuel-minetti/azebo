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
 * Prüft ob eine Dienstbefreiung an einem Feiertag angegeben wurde ohne das eine
 * Bermerkung hinzugefügt wurde.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_BefreiungArbeitsfrei extends Zend_Validate_Abstract {

    const BEFREIUNG = 'befreiung';

    protected $_messageTemplates = array(
        self::BEFREIUNG => 'Wenn eine Dienstbefreiung an einem arbeitsfreien Tag
            angegeben wird, muss eine Bemerkung hinzugefügt werden.',
    );

    public function isValid($value, $context = null) {
        if (is_array($context)) {
            $tag = new Zend_Date($context['tag'], 'dd.MM.yyyy');
            $ns = new Zend_Session_Namespace();
            $feiertagsservice = $ns->feiertagsservice;
            $feiertag = $feiertagsservice->feiertag($tag);
            $mitarbeiter = $ns->mitarbeiter;
            $arbeitstag = $mitarbeiter->getArbeitstagNachTag($tag);
            if ($feiertag['feiertag'] == true || $arbeitstag->getRegel() === null) {
                // ist feiertag
                if (isset($context['befreiung']) && $context['befreiung'] != '' &&
                        $context['befreiung'] != 'keine') {
                    // hat Dienstbefreiung
                    if (!isset($context['bemerkung']) || $context['bemerkung'] == '') {
                        // hat keine Bemerkung
                        $this->_error(self::BEFREIUNG);
                        return false;
                    }
                }
            }

            return true;
        }
    }

}
