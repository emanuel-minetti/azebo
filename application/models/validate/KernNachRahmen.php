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
 * Description of KernNachRahmen
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_KernNachRahmen extends Zend_Validate_Abstract {
    
    const KERN_NACH_RAHMEN = 'KernNachRahmen';

    protected $_messageTemplates = array(
        self::KERN_NACH_RAHMEN => 'Der eingegebene Kernarbeitszeit-Anfang liegt
            vor dem eingegebenen Rahmenarbeitszeit-Anfang!',
    );
    
    public function isValid($value, $context = null) {

        $this->_setValue($value);
        if(is_array($context) && isset($context['rahmenAnfang']) && $context['rahmenAnfang'] != '') {
            $kernAnfang = $value;
            $filter = new Azebo_Filter_ZeitAlsDate();
            $rahmenAnfang = $filter->filter($context['rahmenAnfang']);
            if($kernAnfang->compareTime($rahmenAnfang) == -1) {
                $this->_error(self::KERN_NACH_RAHMEN);
                return false;
            }
        }
        return true;
    }
    
}

