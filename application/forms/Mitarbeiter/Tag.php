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
 * Description of TagBearbeiten
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Tag extends AzeboLib_Form_Abstract {

    /**
     * @var Azebo_Resource_Arbeitstag_Item_Interface 
     */
    protected $_arbeitstag;

    public function init() {
        $log = Zend_Registry::get('log');


        $authService = new Azebo_Service_Authentication();
        $mitarbeiter = $authService->getIdentity();

        $datum = new Zend_Date();
        $datum->setYear($this->getView()->jahr)
                ->setMonth($this->getView()->monat)
                ->setDay($this->getView()->tag);

        $arbeitstag = $mitarbeiter->getArbeitstagNachTag($datum);

        $beginnElement = new Zend_Dojo_Form_Element_TimeTextBox('beginn', array(
                    'label' => 'Beginn',
                    'timePattern' => 'HH:mm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => 'Bitte geben Sie die Uhrzeit im Format ss:mm ein!',
                ));

        $endeElement = new Zend_Dojo_Form_Element_TimeTextBox('ende', array(
                    'label' => 'Ende',
                    'timePattern' => 'HH:mm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => 'Bitte geben Sie die Uhrzeit im Format ss:mm ein!',
                ));

        //TODO in die Datenbank verschieben!
        $befreiungOptionen = array(
            'keine' => '',
            'urlaub' => 'Urlaub',
            'azv' => 'AZV',
        );

        $befreiungElement = new Zend_Dojo_Form_Element_FilteringSelect('befreiung', array(
                    'label' => 'Dienstbefreiung',
                    'multiOptions' => $befreiungOptionen,
                    'invalidMessage' => 'Bitte wählen Sie eine der Optionen aus!',
                ));

        $begruendungElement = new Zend_Dojo_Form_Element_Textarea('begruendung', array(
                    'label' => 'Begündung',
                    'style' => 'width: 300px;',
                ));

        $pauseElement = new Zend_Dojo_Form_Element_CheckBox('pause', array(
                    'label' => 'Ohne Pause',
                    'checkedValue' => 'x',
                    'uncheckedValue' => '-',
                ));

        if ($arbeitstag !== null) {
            if ($arbeitstag->beginn !== null) {
                $beginnElement->setValue('T' . $arbeitstag->beginn);
            }
            if ($arbeitstag->ende !== null) {
                $endeElement->setValue('T' . $arbeitstag->ende);
            } else {
                if ($arbeitstag->beginn !== null) {
                    $endeElement->setValue('T' . $arbeitstag->beginn);
                }
            }
            if ($arbeitstag->befreiung !== null) {
                $befreiungElement->setValue($arbeitstag->befreiung);
            }
        }

        $this->addElement($beginnElement);
        $this->addElement($endeElement);
        $this->addElement($befreiungElement);
        $this->addElement($begruendungElement);
        $this->addElement($pauseElement);

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

