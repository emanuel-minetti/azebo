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
 * Prüft ob das Ende eines Arbeitstages nach dem Beginn liegt.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_EndeNachBeginn extends Zend_Validate_Abstract {

    const BEGINN_NACH_ENDE = 'BeginnNachEnde';

    protected $_messageTemplates = array(
        self::BEGINN_NACH_ENDE => 'Das eingegebene Ende liegt vor dem Beginn!',
    );

    public function isValid($value, $context = null) {

        $this->_setValue($value);
        $filter = new Azebo_Filter_ZeitAlsDate();
        $zeitService = new Azebo_Service_Zeitrechner();

        if (is_array($context)) {
            if (isset($context['beginn']) && isset($context['ende']) &&
                    $context['beginn'] != '' && $context['ende'] != '') {
                $ende = $filter->filter($context['ende']);
                $beginn = $filter->filter($context['beginn']);
                if ($zeitService->anwesend($beginn, $ende) === null) {
                    $this->_error(self::BEGINN_NACH_ENDE);
                    return false;
                }
            }
        }

        return true;
    }

}

