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
 * Description of BisNachVon
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_BisNachVon extends Zend_Validate_Abstract {

    const VON_NACH_BIS = 'VonNachBis';

    protected $_messageTemplates = array(
        self::VON_NACH_BIS => 'Das eingegebene "Bis" liegt nicht nach dem "Von"!',
    );

    public function isValid($value, $context = null) {

        $this->_setValue($value);

        if (is_array($context) && isset($context['von']) && $context['von'] != '') {
            $filter = new Azebo_Filter_DatumAlsDate();
            $von = $filter->filter($context['von']);
            $bis = $filter->filter($value);
            if($von->compareDate($bis) != -1) {
                $this->_error(self::VON_NACH_BIS);
                return false;
            }
        }
        return true;
    }

}

