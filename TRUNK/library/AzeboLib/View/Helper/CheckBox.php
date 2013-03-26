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
 * Behebt einen Bug im Zusammenspiel von HTML5, ZF 1.12 und Dojo 1.8.
 * 
 * In HTML5 wird der Wert des Attributes 'required' nicht mehr ausgewertet,
 * sondern das Attribut funktioniert als Schalter. Dojo 1.8 wertet das
 * HTML-Required-Attribut gar nicht mehr aus, sonder verwendet einen eigenen
 * Namensraum. ZF 1.12 setzt aber noch das HTML-Attribut. Dieser angepasste
 * View-Helper verhindert das.
 *
 * @author Emanuel Minetti
 */
class AzeboLib_View_Helper_CheckBox extends Zend_Dojo_View_Helper_CheckBox {
     public function checkBox($id, $value = null, array $params = array(), array $attribs = array(), array $checkedOptions = null)
    {
         // Hier wird der Bug behoben.
         if(isset($params['required']) && $params['required'] != true) {
             unset($params['required']);
         }
         
         
        // Prepare the checkbox options
        require_once 'Zend/View/Helper/FormCheckbox.php';
        $checked = false;
        if (isset($attribs['checked']) && $attribs['checked']) {
            $checked = true;
        } elseif (isset($attribs['checked'])) {
            $checked = false;
        }
        $checkboxInfo = Zend_View_Helper_FormCheckbox::determineCheckboxInfo($value, $checked, $checkedOptions);
        $attribs['checked'] = $checkboxInfo['checked'];
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }

        $attribs = $this->_prepareDijit($attribs, $params, 'element');

        // strip options so they don't show up in markup
        if (array_key_exists('options', $attribs)) {
            unset($attribs['options']);
        }

        // and now we create it:
        $html = '';
        if (!strstr($id, '[]')) {
            // hidden element for unchecked value
            $html .= $this->_renderHiddenElement($id, $checkboxInfo['uncheckedValue']);
        }

        // and final element
        $html .= $this->_createFormElement($id, $checkboxInfo['checkedValue'], $params, $attribs);

        return $html;
    }
}

