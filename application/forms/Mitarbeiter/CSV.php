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
 * Description of CSV
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_CSV extends AzeboLib_Form_Abstract {


    public function init() {
        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');
        
        $fileElement = new Zend_Form_Element_File('file');
        $fileElement->setLabel('Datei auswählen')
                ->setRequired(true);
        $fileElement->getDecorator('label')->setOption('class', 'custom-file-upload');
        $fileElement->getDecorator('label')->setOption('tag', 'dd');
        $fileElement->getDecorator('HtmlTag')->setOption('tag', 'dt');
        $this->addElement($fileElement);
        
        $this->addElement('text', 'filename', array(
            'label' => 'Ausgewählte Datei:',
            'value' => 'Keine Datei ausgewählt!',
            'disabled' => 'true',
        ));

        $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
            'decorators' => array(
                'DijitElement',
                'Errors',
                array('HtmlTag', array('tag' => 'dd')),
            ),
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
        ));
        
         $this->addElement('Hidden', 'monat', array());
    }

}
