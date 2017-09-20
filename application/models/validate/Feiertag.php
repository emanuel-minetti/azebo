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
 * Description of Feiertag
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Feiertag extends Zend_Validate_Abstract {

    const WE_OHNE_BEM = 'WochenendeOhneBemerkung';
    const FT_OHNE_BEM = 'FeiertagOhneBemerkung';

    protected $_messageTemplates = array(
        self::WE_OHNE_BEM => 'An einem Wochenende kann nur Arbeitszeit eingetragen werden, falls eine Bemerkung angegeben wird!',
        self::FT_OHNE_BEM => 'An einem Feiertag kann nur Arbeitszeit eingetragen werden, falls eine Bemerkung angegeben wird!',
    );

    public function isValid($value, $context = null) {
        //hole und filtere die Daten
        $tag = new Zend_Date($context['tag'], 'dd.MM.yyyy');
        $beginn = $context['beginn'];
        $beginn = substr($beginn, 1);
        if ($beginn != '') {
            $beginn = new Zend_Date($beginn, Zend_Date::TIMES);
        }
        $bemerkung = $context['bemerkung'];
        $bemerkung = trim($bemerkung);

        //teste
        $ns = new Zend_Session_Namespace();
        $feiertagsservice = $ns->feiertagsservice;
        $feiertag = $feiertagsservice->feiertag($tag);
        if ($feiertag['feiertag'] == true) {
            //ist Feiertag, also teste
            if ($beginn != '') {
                //beginn und ende gesetzt, also teste
                if ($bemerkung == '') {
                    if ($feiertag['name'] == '') {
                        $this->_error(self::WE_OHNE_BEM);
                        return false;
                    } elseif($feiertag['name'] != 'Tag der offenen Tür') {
                        $this->_error(self::FT_OHNE_BEM);
                        return false;
                    }
                } 
            }
        }
        
        return true;
    }

}

