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
 * Description of Login
 *
 * @author Emanuel Minetti
 */
class Azebo_Form_Mitarbeiter_Login extends AzeboLib_Form_Abstract{

    public function init() {
        $this->addElement('text', 'benutzername', array(
            //TODO validieren!
//            'filters' => array('StringTrim', 'StringToLower'),
//            'validators' => array(
//                array('StringLength', true, array(3, 128)),
//                array('EmailAddress'),
//            ),
            'required' => true,
            'label' => 'Benutzername',
        ));

        $this->addElement('password', 'passwort', array(
//            'filters' => array('StringTrim'),
//            'validators' => array(
//                array('StringLength', true, array(6, 128))
//            ),
            'required' => true,
            'label' => 'Passwort',
        ));

        $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Login',
        ));
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend', 'class' => 'error')),
            'Form'
        ));
    }

}

