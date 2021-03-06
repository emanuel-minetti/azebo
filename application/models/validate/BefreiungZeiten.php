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
 *     Copyright 2012-19 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft, ob bei einer Dienstbefreiung Beginn und/oder Ende gesetzt ist.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_BefreiungZeiten extends Zend_Validate_Abstract {

    const BEFREIUNG = 'befreiung';

    protected $_messageTemplates = array(
        self::BEFREIUNG => 'Wenn eine Dienstbefreiung angegeben wird, darf kein
            Beginn und Ende eingetragen werden.',
    );

    public function isValid($value, $context = null) {
        $ns = new Zend_Session_Namespace();
        $hochschule = $ns->mitarbeiter->getHochschule();
        if ($hochschule != 'hfm') {
            if (is_array($context)) {
                if (isset($context['befreiung']) &&
                        (isset($context['beginn'])) || isset($context['ende'])) {
                    if ($context['befreiung'] != 'keine' &&
                            ($context['beginn'] != '' || $context['ende'] != '')) {
                        $this->_error(self::BEFREIUNG);
                        return false;
                    }
                }
            }
        }
        return true;
    }

}

