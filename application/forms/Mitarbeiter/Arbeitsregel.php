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
 *     Copyright 2012-19 Emanuel Minetti (e.minetti (at) posteo.de)
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
        
        $this->addElement('DateTextBox', 'von', array(
            'label' => 'Gültig Von: ',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
            'required' => true,
            'missingMessage' => 'Bitte geben Sie einen Wert ein!',
            'autofocus' =>true,
        ));

        $this->addElement('DateTextBox', 'bis', array(
            'label' => 'Gültig Bis: (Für "Bis auf Weiteres" bitte leer lassen)',
            'datePattern' => 'dd.MM.yyyy',
            'invalidMessage' => self::UNGUELTIGES_DATUM,
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
            'required' => true,
            'missingMessage' => 'Bitte wählen Sie einen Wert aus!',
        ));

        $kalenderwochen = array(
            'alle' => 'Alle',
            'gerade' => 'Gerade',
            'ungerade' => 'Ungerade',
        );

        $this->addElement('FilteringSelect', 'kw', array(
            'label' => 'Kalenderwoche: ',
            'multiOptions' => $kalenderwochen,
            'invalidMessage' => 'Ungültiger Wert!',
            'filters' => array('StringTrim', 'Alpha'),
            'value' => 'alle',
            'required' => true,
            'missingMessage' => 'Bitte wählen Sie einen Wert aus!',
        ));

        $this->addElement('TimeTextBox', 'rahmenAnfang', array(
            'label' => 'Rahmen-Anfang: (Für Standardzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
        ));

        $this->addElement('TimeTextBox', 'kernAnfang', array(
            'label' => 'Kern-Anfang: (Für Standardzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'validators' => array('KernNachRahmen'),
        ));

        $this->addElement('TimeTextBox', 'kernEnde', array(
            'label' => 'Kern-Ende: (Für Standardzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'validators' => array('KernEndeNachAnfang', 'RahmenNachKern'),
        ));

        $this->addElement('TimeTextBox', 'rahmenEnde', array(
            'label' => 'Rahmen-Ende: (Für Standardzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'required' => false,
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'validators' => array('RahmenEndeNachAnfang'),
        ));

        $this->addElement('TimeTextBox', 'soll', array(
            'label' => 'Soll-Arbeitszeit: (Bei Mitarbeitern in Vollzeit bitte leer lassen)',
            'timePattern' => 'HHmm',
            'visibleRange' => 'T02:00:00',
            'visibleIncrement' => 'T00:10:00',
            'clickableIncrement' => 'T00:10:00',
            'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
            'autofocus' =>true,
        ));
        
        $this->addElement('CheckBox', 'ohneKern', array(
            'label' => 'Ohne Kernarbeitszeit',
            'checkedValue' => 'ja',
            'uncheckedValue' => 'nein',
            'checked' => false,
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
            'validators' => array('RegelEindeutig'),
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
        ));

        $this->addElement('SubmitButton', 'loeschen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Löschen',
        ));
        
        $this->addElement('Hidden', 'benutzername', array());
        
        $this->addElement('Hidden', 'id', array());
    }

}
