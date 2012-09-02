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
 * Description of EndeNachBeginn
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_EndeNachBeginn extends Zend_Validate_Abstract {
    
    const BEGINN_NACH_ENDE = 'BeginnNachEnde';
    
     protected $_messageTemplates = array(
        self::BEGINN_NACH_ENDE => 'Der eingegebene Beginn liegt nach dem Ende!',
    );
    
    public function isValid($value, $context = null) {
        
        $this->_setValue($value);
        
        $log = Zend_Registry::get('log');
        $log->debug(__METHOD__);
        
        if(is_array($context)) {
            if(isset($context['beginn'])) {
                $ende = new Zend_Date($value);
                //TODO Kommentieren!
                $contextBeginn = $context['beginn'];
                $beginnWert = $contextBeginn == '' ? null : substr($contextBeginn, 1);
                $beginn = new Zend_Date($beginnWert, Zend_Date::TIME_MEDIUM);
                //$beginn = new Zend_Date($context['beginn']);
                $log->debug('Ende: ' . $ende->toString());
                $log->debug('Beginn: ' . $beginn->toString());
                $log->debug('EndeWert: ' . $value);
                $log->debug('ContextBeginn: ' . $contextBeginn);
                if($ende->isEarlier($beginn, Zend_Date::TIME_MEDIUM)) {
                    $this->_error(self::BEGINN_NACH_ENDE);
                    return false;
                }
            }
        }
        
        return true;
    }
    
}

