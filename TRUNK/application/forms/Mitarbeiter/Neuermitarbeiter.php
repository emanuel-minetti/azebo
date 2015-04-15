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
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Description of Mitarbeiter
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Neuermitarbeiter extends AzeboLib_Form_Abstract {
    
    const UNGUELTIGE_OPTION = 'Bitte wählen Sie eine der Optionen aus!';
    
    public function init() {
        $auswahlElement = new Zend_Dojo_Form_Element_FilteringSelect('auswahl', array(
                    'label' => 'Neuer Mitarbeiter: ',
                    //'multiOptions' => $mitgliederOptions,
                    'invalidMessage' => Azebo_Form_Mitarbeiter_Neuermitarbeiter::UNGUELTIGE_OPTION,
                    'filters' => array('StringTrim', 'Alpha'),
                    'tabindex' => 1,
                    'autofocus' => true,
                ));
        $this->addElement($auswahlElement);
        $this->addElement('SubmitButton', 'hinzufügen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Hinzufügen',
            'decorators' => array('DijitElement'),
            'tabindex' => 2,
        ));
    }
}

