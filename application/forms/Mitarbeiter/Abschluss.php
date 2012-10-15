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
 * Dieses Formular fügt zwei Buttons für 'Monat Prüfen' und 'Monat Abschließen'
 * hinzu.
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Abschluss extends AzeboLib_Form_Abstract {

    public function init() {
        //TODO 'Monat Abschließen' hinzufügen!

        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');
        
        $this->addElement('Hidden', 'monat', array());

        $this->addElement('SubmitButton', 'pruefen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Monat prüfen',
            'validators' => array('Monat',),
            'decorators' => array('DijitElement','Errors',),
            'tabindex' => 1,
        ));
    }

}

