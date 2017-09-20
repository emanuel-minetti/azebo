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
 * Description of Kappung
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Kappung extends Zend_Validate_Abstract {

    const FORMAT = 'format';
    const ZU_GROSS = 'zuGross';
    const MINUTEN = 'minuten';

    protected $_messageTemplates = array(
        self::FORMAT => 'Bitte geben Sie das Saldo im Format \'[hh]h:mm\' ein.',
        self::MINUTEN => 'Die Minuten müssen weniger als 60 betragen!',
    );

    public function isValid($value) {
        $preg = '^(\d{1,3}):(\d{1,2})$';
        $parts = array();
        if (!preg_match("/$preg/", $value, $parts)) {
            $this->_error(self::FORMAT);
            return false;
        } elseif ($parts[2] >= 60) {
            $this->_error(self::MINUTEN);
            return false;
        }

        return true;
    }

}
