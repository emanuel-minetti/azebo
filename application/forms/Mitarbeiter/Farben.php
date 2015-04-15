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
 * Das Formular in dem die Mitarbeiter Farben für die Darstellung
 * auswählen können.
 * 
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Farben extends AzeboLib_Form_Abstract {

    public function init() {
        $this->addElement('Hidden', 'farbeKopf', array(
            'decorators' => array('DijitElement'),
        ));
        $this->addElement('Hidden', 'farbeHoover', array(
            'decorators' => array('DijitElement'),
        ));
        $this->addElement('Hidden', 'farbeLink', array(
            'decorators' => array('DijitElement'),
        ));
        $this->addElement('Hidden', 'farbeZeile', array(
            'decorators' => array('DijitElement'),
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
        ));
        
        $this->addElement('SubmitButton', 'uebernehmen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Übernehmen',
            'validators' => array('Jahr',),
        ));
    }

}
