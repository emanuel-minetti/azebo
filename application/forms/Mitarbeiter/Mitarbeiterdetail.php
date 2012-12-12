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
 * Description of Mitarbeiterdetail
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Mitarbeiterdetail extends AzeboLib_Form_Abstract {
    //TODO HfM: Überstunden 2007 
    
    public function init() {
        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');
        
        $this->addElement('CheckBox', 'beamter', array(
            'label' => 'Beamter',
            'required' => false,
            'checkedValue' => 'ja',
            'uncheckedValue' => 'nein',
            'filters' => array('StringTrim'),
        ));
        
        $this->addElement('Text', 'saldo', array(
            'label' => 'Saldo Übertrag',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array('Saldo',),
        ));
        
        $this->addElement('Text', 'urlaub', array(
            'label' => 'Urlaub Übertrag',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array('Digits',),
        ));
        
         $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
        ));
         
         $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
        ));      
    }
}

