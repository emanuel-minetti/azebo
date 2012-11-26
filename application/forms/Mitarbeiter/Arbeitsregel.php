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
 * Description of Arbeitsregel
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Arbeitsregel extends AzeboLib_Form_Abstract {
    
    const UNGUELTIGE_UHRZEIT = 'Bitte geben Sie die Uhrzeit als vierstellige Zahl ein!';
    const UNGUELTIGES_DATUM = 'Bitte geben Sie das Datum im Format dd.mm.jjjj an!';

    public function init() {
        $this->addElementPrefixPath(
                'Azebo_Filter', APPLICATION_PATH . '/models/filter/', 'filter');
        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');

        //TODO Validatoren hinzufügen!
        $this->addElement('DateTextBox', 'von', array(
            'label' => 'Gültig Von: ',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
            'filters' => array('StringTrim', 'DatumAlsDate'),
        ));

        $this->addElement('DateTextBox', 'bis', array(
            'label' => 'Gültig Bis: (Für "Bis auf Weiteres" bitte leer lassen)',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
            'filters' => array('StringTrim', 'DatumAlsDate'),
            'validators' => array('BisNachVon'),
        ));

        $wochentage = array(
            'montag' => 'Montag',
            'dienstag' => 'Dienstag',
            'mittwoch' => 'Mittwoch',
            'donnerstag' => 'Donnerstag',
            'freitag' => 'Freitag',
            'alle' => 'Alle',
        );

        $this->addElement('FilteringSelect', 'wochentag', array(
            'label' => 'Wochentag: ',
            'multiOptions' => $wochentage,
            'invalidMessage' => 'Ungültiger Wert!',
            'filters' => array('StringTrim', 'Alpha'),
            'value' => 'alle',
        ));

        $this->addElement('TimeTextBox', 'rahmenAnfang', array(
            'label' => 'Rahmen-Anfang: (Für Standartzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'filters' => array('StringTrim', 'ZeitAlsDate'),
        ));
        
        $this->addElement('TimeTextBox', 'kernAnfang', array(
            'label' => 'Kern-Anfang: (Für Standartzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'filters' => array('StringTrim', 'ZeitAlsDate'),
        ));
        
        $this->addElement('TimeTextBox', 'kernEnde', array(
            'label' => 'Kern-Ende: (Für Standartzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'filters' => array('StringTrim', 'ZeitAlsDate'),
        ));
        
        $this->addElement('TimeTextBox', 'rahmenEnde', array(
            'label' => 'Rahmen-Ende: (Für Standartzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'filters' => array('StringTrim', 'ZeitAlsDate'),
        ));
        
        $this->addElement('TimeTextBox', 'soll', array(
            'label' => 'Soll-Arbeitszeit',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'filters' => array('StringTrim', 'ZeitAlsDate'),
        ));
        
        $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
            //'tabindex' => 3,
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
            //'tabindex' => 6,
        ));
        
        $this->addElement('SubmitButton', 'loeschen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Löschen',
            //'tabindex' => 6,
        ));
    }

}
