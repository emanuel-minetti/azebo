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
 * Description of BisNachVon
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Vertreter extends Zend_Validate_Abstract {

    const SELBST = 'selbst';

    protected $_messageTemplates = array(
        self::SELBST => "Sie können sich nicht selbst als Vertreter auswählen!
            Bitte wählen Sie 'Zurück', und wählen einen anderen Vertreter!",
    );

    public function isValid($value, $context = null) {

        $this->_setValue($value);

        if (is_array($context) && isset($context['vertreter'])) {
            $ns = new Zend_Session_Namespace();
            $mitarbeiter = $ns->mitarbeiter;
            $mitarbeiterBN = $mitarbeiter->benutzername;
            $vertreterBN = $context['vertreter'];
            if($vertreterBN == $mitarbeiterBN) {
                $this->_error(self::SELBST);
                return false;
            }
        }
        return true;
    }

}

