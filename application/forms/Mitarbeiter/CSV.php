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
//        $this->addElementPrefixPath(
//                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');
        $this->setAttrib('enctype', 'multipart/form-data');
        
        // Das Input-File-Element erzeugen und konfigurieren.
        // Dieses Element wird via CSS verborgen, da es nicht gestylt werden kann.
        $fileElement = new Zend_Form_Element_File('file');
        $fileElement->setLabel('Datei auswählen');
        $fileElement->setRequired(true);
        $fileElement->setDestination('/var/www/data/uploads/');
        $fileElement->setValueDisabled(true);
        $fileElement->setAttrib('accept', 'text/csv');
        $fileElement->addValidator('Count', false, 1);
        $fileElement->addValidator('Size', false, 50000);
        $fileElement->addValidator('Extension', false, 'csv');
        $fileElement->addValidator('MimeType', false, array('text/csv', 'text/plain'));
        
        // Das Label zum Input-File-Element erzeugen und konfigurieren.
        // Dieses Label wird durch CSS zum Button, der entprechend gestylt werden kann.
        $fileElement->getDecorator('label')->setOption('class', 'custom-file-upload');
        $fileElement->getDecorator('label')->setOption('tag', 'dd');
        $fileElement->getDecorator('HtmlTag')->setOption('tag', 'dt');
        
        $this->addElement($fileElement);
        
        $this->addElement('text', 'filename', array(
            'value' => 'Keine Datei ausgewählt!',
            'disabled' => 'true',
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
        
         $this->addElement('Hidden', 'monat', array());
    }
}

