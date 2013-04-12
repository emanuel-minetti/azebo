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
 * Description of Block
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Block extends AzeboLib_Form_Abstract {

    const UNGUELTIGES_DATUM = 'Bitte geben Sie das Datum im Format dd.mm.jjjj an!';
    const UNGUELTIGE_OPTION = 'Bitte wählen Sie eine der Optionen aus!';

    public function init() {
        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');

        $this->addElement('DateTextBox', 'von', array(
            'label' => 'Von: ',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
            'required' => true,
            'missingMessage' => 'Bitte geben Sie einen Wert ein!',
            //'filters' => array('StringTrim', 'DatumAlsDate', ),
            'autofocus' => true,
        ));

        $this->addElement('DateTextBox', 'bis', array(
            'label' => 'Bis: ',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
            'required' => true,
            'missingMessage' => 'Bitte geben Sie einen Wert ein!',
            //'filters' => array('StringTrim', 'DatumAlsDate'),
            'validators' => array('BisNachVon'),
        ));
        
        $befreiungOptionen = array(
            'keine' => '',
            'urlaub' => 'Urlaub',
            'krankheit' => 'Krankheit',
        );
        $this->addElement('FilteringSelect', 'befreiung', array(
                    'label' => 'Dienstbefreiung: ',
                    'multiOptions' => $befreiungOptionen,
                    'invalidMessage' => self::UNGUELTIGE_OPTION,
                    'filters' => array('StringTrim', 'Alpha'),
                    'validators' => array(
                        'BefreiungArbeitsfrei',
                        'BefreiungNachmittag',
                    ),
                    'tabindex' => 8,
                ));

        $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
            'decorators' => array(
                'DijitElement',
                'Errors',
                array('HtmlTag', array('tag' => 'dd')),
            //array('HtmlTag', array('tag' => 'dt')),
            ),
            'validators' => array('RegelEindeutig'),
                //TODO Decoratoren anpassen!
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
        ));
    }

}

