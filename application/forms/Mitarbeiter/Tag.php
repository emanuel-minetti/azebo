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

    private $beginnNachmittagElement;
    private $endeNachmittagElement;

    public function init() {
        //$log = Zend_Registry::get('log');

        $authService = new Azebo_Service_Authentifizierung();
        //TODO Stellvertreter!
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

        $beginnElement = new Zend_Dojo_Form_Element_TimeTextBox('beginn', array(
                    'label' => 'Beginn',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'ZeitAlsDate',),
                    'validators' => array(
                        'Beginn',),
                    'tabindex' => 1,
                    'autofocus' => true,
                ));
        
        $endeElement = new Zend_Dojo_Form_Element_TimeTextBox('ende', array(
                    'label' => 'Ende',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'ZeitAlsDate'),
                    'validators' => array(
                        'EndeNachBeginn',
                        'Feiertag',
                        'Ende',
                        'ZehnStunden',
                        'IstArbeitstag',
                        ),
                    'tabindex' => 2,
                ));
        
        $this->beginnNachmittagElement = new Zend_Dojo_Form_Element_TimeTextBox(
                'beginnnachmittag', array(
                    'label' => 'Beginn Nachmittag',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'ZeitAlsDate',),
                    'validators' => array(
                        'Beginn',),
                ));
        
        $this->endeNachmittagElement = new Zend_Dojo_Form_Element_TimeTextBox(
                'endenachmittag', array(
                    'label' => 'Ende Nachmittag',
                    'timePattern' => 'HHmm',
                    'required' => false,
                    'visibleRange' => 'T02:00:00',
                    'visibleIncrement' => 'T00:10:00',
                    'clickableIncrement' => 'T00:10:00',
                    'invalidMessage' => self::UNGUELTIGE_UHRZEIT,
                    'filters' => array('StringTrim', 'ZeitAlsDate'),
                    'validators' => array(
                        'EndeNachBeginn',
                        'Feiertag',
                        'Ende',
                        'ZehnStunden',
                        'IstArbeitstag',
                        ),
                    //'tabindex' => 2,
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

        // falls hier was geändert wird, muss es auch in setNachmittag()
        // geändert werden!
        $this->addElement($beginnElement);
        $this->addElement($endeElement);
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
        
        $this->addElement('SubmitButton', 'nachmittag', array(
            'required' => false,
            'ignore' => true,
            'tabindex' => 7,
        ));
    }

    public function setBeginn($beginn) {
        $displayedValue = $beginn === null ? '' : $beginn->toString('HHmm');
        $this->getElement('beginn')->setDijitParam('displayedValue', $displayedValue);
    }

    public function setEnde($ende) {
        $displayedValue = $ende === null ? '' : $ende->toString('HHmm');
        $this->getElement('ende')->setDijitParam('displayedValue', $displayedValue);
    }
    
    public function setNachmittag($nachmittag) {
        if($nachmittag) {
            // füge die Elemente hinzu
            $this->addElement($this->beginnNachmittagElement);
            $this->addElement($this->endeNachmittagElement);
            $elemente = $this->getElements();
            
            // setze die Label neu
            $elemente['nachmittag']->setLabel('Nachmittag entfernen');
            $elemente['beginn']->setLabel('Beginn Vormittag');
            $elemente['ende']->setLabel('Ende Vormittag');
            
            // bringe die Elemente in die richtige Reihenfolge
            $elemente['beginnnachmittag']->setOrder(3);
            $elemente['endenachmittag']->setOrder(4);
            $elemente['befreiung']->setOrder(5);
            $elemente['bemerkung']->setOrder(6);
            $elemente['pause']->setOrder(7);
            $elemente['tag']->setOrder(8);
            $elemente['absenden']->setOrder(9);
            $elemente['zuruecksetzen']->setOrder(10);
            $elemente['nachmittag']->setOrder(11);
        } else {
            // setze die Label neu
            $this->getElement('nachmittag')->setLabel('Nachmittag hinzufügen');
            $this->getElement('beginn')->setLabel('Beginn');
            $this->getElement('ende')->setLabel('Ende');
            
            // entferne ggf. die unnötigen Elemente
            $this->removeElement('beginnnachmittag');
            $this->removeElement('endenachmittag');
        }
    }

}
