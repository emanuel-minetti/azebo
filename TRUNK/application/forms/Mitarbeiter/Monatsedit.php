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
 * Description of Monatsedit
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Monatsedit extends AzeboLib_Form_Abstract {
    
    public function init() {
        $this->addElement('SubmitButton', 'ablegen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Monat Ablegen',
        ));
        
        $this->addElement('SubmitButton', 'zurueck', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Abschluss Zurücknehmen',
        ));
        
        $this->addElement('SubmitButton', 'anzeigen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Monat Anzeigen',
        ));
        
        $this->setMethod('post');
        $this->setName('monatseditForm');
    }
}

