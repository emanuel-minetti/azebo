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

    const UNGUELTIGE_UHRZEIT = 'Bitte geben Sie die Uhrzeit als vierstellige Zahl ein!';
    const UNGUELTIGE_OPTION = 'Bitte wählen Sie eine der Optionen aus!';

    private $beginnElement;
    private $endeElement;

    public function init() {
        //$log = Zend_Registry::get('log');

        $authService = new Azebo_Service_Authentifizierung();
        $mitarbeiter = $authService->getIdentity();
        $datum = new Zend_Date();
        $datum->setYear($this->getView()->jahr)
                ->setMonth($this->getView()->monat)
                ->setDay($this->getView()->tag);
        $arbeitstag = $mitarbeiter->getArbeitstagNachTag($datum);

        $this->addElementPrefixPath(
                'Azebo_Filter', APPLICATION_PATH . '/models/filter/', 'filter');
        $this->addElementPrefixPath(
                'Azebo_Validate', APPLICATION_PATH . '/models/validate/', 'validate');

        $this->beginnElement = new Zend_Dojo_Form_Element_TimeTextBox('beginn', array(
                    'label' => 'Beginn',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'AlsDatum',),
                    'validators' => array(
                        'Beginn',),
                    'tabindex' => 1,
                    'autofocus' => true,
                ));
        
        $this->endeElement = new Zend_Dojo_Form_Element_TimeTextBox('ende', array(
                    'label' => 'Ende',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'AlsDatum'),
                    'validators' => array(
                        'EndeNachBeginn',
                        'Feiertag',
                        'Ende',
                        'ZehnStunden',
                        ),
                    'tabindex' => 2,
                ));

        $befreiungService = new Azebo_Service_Befreiung();
        $befreiungOptionen = $befreiungService->getOptionen($mitarbeiter);
        $befreiungElement = new Zend_Dojo_Form_Element_FilteringSelect('befreiung', array(
                    'label' => 'Dienstbefreiung',
                    'multiOptions' => $befreiungOptionen,
                    'invalidMessage' => self::UNGUELTIGE_OPTION,
                    'filters' => array('StringTrim', 'Alpha'),
                    'tabindex' => 4,
                ));

        $bemerkungElement = new Zend_Dojo_Form_Element_Textarea('bemerkung', array(
                    'label' => 'Bemerkung',
                    'required' => false,
                    'style' => 'width: 300px;',
                    'filters' => array('StringTrim'),
                    'tabindex' => 5,
                ));

        $pauseElement = new Zend_Dojo_Form_Element_CheckBox('pause', array(
                    'label' => 'Ohne Pause',
                    'required' => false,
                    'checkedValue' => 'x',
                    'uncheckedValue' => '-',
                    'filters' => array('StringTrim'),
                    'validators' => array('Pause',),
                ));

        $tagElement = new Zend_Form_Element_Hidden('tag');

        // Bevölkere das Formular
        if ($arbeitstag !== null) {
            if ($arbeitstag->beginn !== null) {
                $this->setBeginn($arbeitstag->beginn);
            }
            if ($arbeitstag->ende !== null) {
                $this->setEnde($arbeitstag->ende);
            }
            if ($arbeitstag->befreiung !== null) {
                $befreiungElement->setValue($arbeitstag->befreiung);
            }
            if ($arbeitstag->bemerkung !== null) {
                $bemerkungElement->setValue($arbeitstag->bemerkung);
            }
            if ($arbeitstag->pause !== null) {
                if ($arbeitstag->pause == 'x') {
                    $pauseElement->setChecked(true);
                } else {
                    $pauseElement->setChecked(false);
                }
            }
            $tagElement->setValue($arbeitstag->getTag()->toString('dd.MM.YYYY'));
        }

        $this->addElement($this->beginnElement);
        $this->addElement($this->endeElement);
        $this->addElement($befreiungElement);
        $this->addElement($bemerkungElement);
        $this->addElement($pauseElement);
        $this->addElement($tagElement);

        $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
            'tabindex' => 3,
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
            'tabindex' => 6,
        ));
    }

    public function setBeginn($beginn) {
        $displayedValue = $beginn === null ? '' : $beginn->toString('HHmm');
        $this->beginnElement->setDijitParam('displayedValue', $displayedValue);
    }

    public function setEnde($ende) {
        $displayedValue = $ende === null ? '' : $ende->toString('HHmm');
        $this->endeElement->setDijitParam('displayedValue', $displayedValue);
    }

}
