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
 * Description of Saldo
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Saldo2007 extends Zend_Validate_Abstract {

    const FORMAT = 'format';
    const NEGATIV = 'negativ';

    protected $_messageTemplates = array(
        self::FORMAT => 'Bitte geben Sie das Saldo im Format \'+/- hh:mm\' ein.',
        self::NEGATIV => 'Das Saldo 2007 darf nicht negativ sein!',
    );

    public function isValid($value) {
        $preg = '^(\+|-) (\d{1,3}):(\d{1,2})$';
        $parts = array();
        if (preg_match("/$preg/", $value, $parts)) {
            if ($parts[1] == '-') {
                $this->_error(self::NEGATIV);
                return false;
            }
        } else {
            $this->_error(self::FORMAT);
            return false;
        }
        return true;
    }

}

