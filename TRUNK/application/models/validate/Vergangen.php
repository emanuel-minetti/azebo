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
 * Prüft ob eine Arbeitszeitregelung den vergangenen Monat, oder vorher,
 * betrifft.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Vergangen extends Zend_Validate_Abstract {
    
    const VERGANGEN = 'vergangen';
    
    protected $_messageTemplates = array(
        self::VERGANGEN => 'Sie Können keine Arbeitszeitregelungen für die Vergangenheit ändern!',
    );
    
    public function isValid($value, $context = null) {
        $this->_setValue($value);
        
        if (is_array($context)) {
            $filter = new Azebo_Filter_DatumAlsDate();
            $von = $filter->filter($context['von']);
            $jetzt = new Zend_Date();
            if(($jetzt->compareMonth($von) == 1 && $jetzt->compareYear($von) == 0) ||
                    $jetzt->compareYear($von) == 1) {
                $this->_error(self::VERGANGEN);
                return false;
            }
        }
        return true;
    }
    
}

