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
 * Description of RahmenNachKern
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_RahmenNachKern extends Zend_Validate_Abstract {
    
    const RAHMEN_NACH_KERN = 'RahmenNachKern';

    protected $_messageTemplates = array(
        self::RAHMEN_NACH_KERN => 'Das eingegebene Kernarbeitszeit-Ende liegt
            nach dem eingegebenen Rahmenarbeitszeit-Ende!',
    );
    
    public function isValid($value, $context = null) {

        $this->_setValue($value);
        if(is_array($context) && isset($context['rahmenEnde']) && $context['rahmenEnde'] != '') {
            $filter = new Azebo_Filter_ZeitAlsDate();
            $rahmenEnde = $filter->filter($context['rahmenEnde']);
             $kernEnde = $filter->filter($value);
            if($kernEnde->compareTime($rahmenEnde) == 1) {
                $this->_error(self::RAHMEN_NACH_KERN);
                return false;
            }
        }
        return true;
    }
}

