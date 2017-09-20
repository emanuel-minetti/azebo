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
 * Description of BlockMonat
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_BlockMonat extends Zend_Validate_Abstract {

    const BLOCK_MONAT = 'BlockMonat';

    public $monat = '';
    protected $_messageVariables = array(
        'monat' => 'monat',
    );
    protected $_messageTemplates = array(
        self::BLOCK_MONAT => 'Sie können nur Tage im Monat %monat% bearbeiten!',
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);
        $filter = new Azebo_Filter_DatumAlsDate();
        $wertDatum = $filter->filter($value);
        $monatDatum = $filter->filter($context['monat']);

        if ($wertDatum->getMonth() != $monatDatum->getMonth() ||
                $wertDatum->getYear() != $monatDatum->getYear()) {
            $this->monat = $monatDatum->toString('MMMM yyyy');
            $this->_error(self::BLOCK_MONAT);
            return false;
        }

        return true;
    }

}

