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
    //TODO Kernzeit validieren

    const NACH_RAHMEN = 'EndeNachRahmen';

    protected $_messageTemplates = array(
        self::NACH_RAHMEN => 'Das eingetragene Ende liegt nach dem Ende der
            Rahmenarbeitszeit! Bitte geben Sie eine Bemerkung ein.',
    );
    
    public function isValid($value, $context = null) {
        $rahmenEnde = new Zend_Date('20:00:00', Zend_Date::TIMES);
        if ($value->compareTime($rahmenEnde) == 1) {
            //nach Ende der Rahmenarbeitszeit
            $bemerkung = $context['bemerkung'];
            $bemerkung = trim($bemerkung);
            if ($bemerkung == '') {
                $this->_error(self::NACH_RAHMEN);
                return false;
            }
        }
        return true;
    }

}

