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
 * Description of TagBearbeiten
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Tag extends AzeboLib_Form_Abstract {

    const UNGUELTIGE_UHRZEIT = 'Bitte geben Sie die Uhrzeit als vierstellige Zahl ein!';
    const UNGUELTIGE_OPTION = 'Bitte wählen Sie eine der Optionen aus!';

    private $_beginnNachmittagElement;
    private $_endeNachmittagElement;
    private $_mitarbeiter;
    private $_datum;

    public function init() {
        $ns = new Zend_Session_Namespace();
        $this->_mitarbeiter = $ns->mitarbeiter;
        $this->_datum = new Zend_Date();
        $this->_datum->setYear($this->getView()->jahr)
                ->setMonth($this->getView()->monat)
                ->setDay($this->getView()->tag);
        $arbeitstag = $this->_mitarbeiter->getArbeitstagNachTag($this->_datum);
        $nachmittag = $arbeitstag->getNachmittag();

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
                        'Beginn',
                        'BefreiungZeiten',
                    ),
                    'tabindex' => 1,
                    'autofocus' => "autofocus",
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
                        'BefreiungZeiten',
                    ),
                    'tabindex' => 2,
                ));

        $this->_beginnNachmittagElement = new Zend_Dojo_Form_Element_TimeTextBox(
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
                        'NachNachVormittag',
                    ),
                    'tabindex' => 3,
                ));

        $this->_endeNachmittagElement = new Zend_Dojo_Form_Element_TimeTextBox(
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
                        'EndeNachBeginnNachmittag',
                        'Ende',
                        'ZehnStunden',
                    ),
                    'tabindex' => 4,
                ));

        $befreiungService = new Azebo_Service_Befreiung();
        $befreiungOptionen = $befreiungService->getOptionen($this->_mitarbeiter);
        $befreiungElement = new Zend_Dojo_Form_Element_FilteringSelect('befreiung', array(
                    'label' => 'Dienstbefreiung',
                    'multiOptions' => $befreiungOptionen,
                    'invalidMessage' => self::UNGUELTIGE_OPTION,
                    'filters' => array('StringTrim', 'Alpha'),
                    'validators' => array(
                        'BefreiungArbeitsfrei',
                        'BefreiungNachmittag',
                        ),
                    'tabindex' => 9,
                ));

        $bemerkungElement = new Zend_Dojo_Form_Element_Textarea('bemerkung', array(
                    'label' => 'Bemerkung',
                    'required' => false,
                    'style' => 'width: 300px;',
                    'filters' => array('StringTrim'),
                    'validators' => array(
                        array('StringLength', false, array(0, 255),)),
                    'tabindex' => 10,
                ));


        $pauseElement = new Zend_Dojo_Form_Element_CheckBox('pause', array(
            'label' => 'Ohne Pause',
            'required' => false,
            'checkedValue' => 'x',
            'uncheckedValue' => '-',
            'filters' => array('StringTrim'),
            'validators' => array('Pause',),
        ));

        // für HfM ausblenden
        if ($this->_mitarbeiter->getHochschule() == 'hfm') {
            $pauseElement->clearDecorators();
        }
        
        $tagElement = new Zend_Form_Element_Hidden('tag', array(
                    'decorators' => array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'dd')),
                    ),
                ));
        $nachmittagElement = new Zend_Form_Element_Hidden('nachmittag', array(
                    'decorators' => array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'dd')),
                    ),
                ));

        // füge die Elemente der Form hinzu
        // falls hier was geändert wird, muss es auch in setNachmittag()
        // geändert werden!
        $this->addElement($beginnElement);
        $this->addElement($endeElement);
        $this->addElement($this->_beginnNachmittagElement);
        $this->addElement($this->_endeNachmittagElement);
        $this->addElement($befreiungElement);
        $this->addElement($bemerkungElement);
        $this->addElement($pauseElement);
        $this->addElement($tagElement);
        $this->addElement($nachmittagElement);

        // Bevölkere das Formular
        if ($arbeitstag !== null) {
            if ($arbeitstag->beginn !== null) {
                $this->setBeginn($arbeitstag->beginn);
            }
            if ($arbeitstag->ende !== null) {
                $this->setEnde($arbeitstag->ende);
            }
            if ($arbeitstag->beginnnachmittag !== null) {
                $this->setBeginn($arbeitstag->beginnnachmittag, true);
            }
            if ($arbeitstag->endenachmittag !== null) {
                $this->setEnde($arbeitstag->endenachmittag, true);
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
            $tagElement->setValue($arbeitstag->getTag()->toString('dd.MM.yyyy'));
            $nachmittagElement->setValue($nachmittag);
        }

        $this->addElement('SubmitButton', 'absenden', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden',
            'tabindex' => 5,
        ));
        
        $this->addElement('SubmitButton', 'absendenWeiter', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Absenden <br/> und nächsten Tag bearbeiten',
            'tabindex' => 6,
        ));

        $this->addElement('SubmitButton', 'zuruecksetzen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Zurücksetzen',
            'tabindex' => 7,
        ));

        $this->addElement('SubmitButton', 'nachmittagButton', array(
            'required' => false,
            'ignore' => true,
            'tabindex' => 8,
        ));
    }

    public function setBeginn($beginn, $nachmittag = false) {
        $elementName = $nachmittag ? 'beginnnachmittag' : 'beginn';
        $displayedValue = $beginn === null ? '' : $beginn->toString('HHmm');
        $this->getElement($elementName)->
                setDijitParam('displayedValue', $displayedValue);
    }

    public function setEnde($ende, $nachmittag = false) {
        $elementName = $nachmittag ? 'endenachmittag' : 'ende';
        $displayedValue = $ende === null ? '' : $ende->toString('HHmm');
        $this->getElement($elementName)->
                setDijitParam('displayedValue', $displayedValue);
    }

    public function setNachmittag() {
        $nachmittag = $this->_mitarbeiter->getArbeitstagNachTag($this->_datum)->
                getNachmittag();
        if ($nachmittag) {
            // füge die Elemente hinzu
            $this->addElement($this->_beginnNachmittagElement);
            $this->addElement($this->_endeNachmittagElement);

            // passe die Elemente an
            $elemente = $this->getElements();
            $elemente['nachmittagButton']->setLabel('Nachmittag entfernen');
            $elemente['nachmittag']->setValue(true);
            $elemente['beginn']->setLabel('Beginn Vormittag');
            $elemente['ende']->setLabel('Ende Vormittag');
            $elemente['ende']->removeValidator('Ende');
            $elemente['ende']->removeValidator('ZehnStunden');

            // bringe die Elemente in die richtige Reihenfolge
            $elemente['beginnnachmittag']->setOrder(3);
            $elemente['endenachmittag']->setOrder(4);
            $elemente['befreiung']->setOrder(5);
            $elemente['bemerkung']->setOrder(6);
            $elemente['pause']->setOrder(7);
            $elemente['tag']->setOrder(8);
            $elemente['nachmittag']->setOrder(9);
            $elemente['absenden']->setOrder(10);
            $elemente['zuruecksetzen']->setOrder(11);
            $elemente['nachmittagButton']->setOrder(12);
        } else {
            // passe die Elemente an
            $this->getElement('nachmittagButton')->
                    setLabel('Nachmittag hinzufügen');
            $this->getElement('nachmittag')->setValue(false);
            $this->getElement('beginn')->setLabel('Beginn');
            $this->getElement('ende')->setLabel('Ende');
            $this->getElement('ende')->addValidator('Ende');
            $this->getElement('ende')->addValidator('ZehnStunden');

            // entferne ggf. die unnötigen Elemente
            $this->removeElement('beginnnachmittag');
            $this->removeElement('endenachmittag');
        }
    }

}
