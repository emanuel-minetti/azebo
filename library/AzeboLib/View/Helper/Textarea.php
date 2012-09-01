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
 * Description of AzeboTextarea
 *
 * @author Emanuel Minetti
 */
class AzeboLib_View_Helper_Textarea extends Zend_Dojo_View_Helper_Textarea {
    //TODO Kommentieren!

    /**
     * dijit.form.Textarea
     *
     * @param  int $id
     * @param  mixed $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function textarea($id, $value = null, array $params = array(), array $attribs = array()) {

        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }
        $attribs['name'] = $id;

        $params = $this->_dijitParams($params);
        $attribs = $this->_prepareDijit($attribs, $params, 'textarea');

        $html = '<textarea' . $this->_htmlAttribs($attribs) . '>'
                . $value
                . "</textarea>\n";

        return $html;
    }

    protected function _dijitParams($params) {
        if (isset($params['required']) && $params['required'] == false) {
            unset($params['required']);
        }
        return $params;
    }

}

