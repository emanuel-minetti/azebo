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
 * Description of Bueroleitung
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Bueroleitung extends Zend_View_Helper_Abstract {

    public function bueroleitung() {
        $html = '';
        if ($this->view->istBueroleitung) {
            $url = $this->view->url(array(
                'controller' => 'bueroleitung',
                'action' => 'index',
                    ),'default',true);
            $html .= '<li><a href="';
            $html .= $url . '" ';
            if($url == $this->view->requestURI) {
                $html .= 'style="font-weight:bold;"';
            }
            $html .= 'onmouseover="azeboopen(\'Nav_Buero\')" onmouseout="azeboclosetime()">' . "\n";
            $html .= 'Büroleitung' . "\n";
            $html .= '</a><ul id="Nav_Buero" onmouseover="azebocancelclosetime()" onmouseout="azeboclosetime()">' . "\n";
            $html .= '<li><a href="';
            $url = $this->view->url(array(
                'controller' => 'bueroleitung',
                'action' => 'mitarbeiter',
                    ), 'default', true);
            $html .= $url . '"';
            if($url == $this->view->requestURI) {
                $html .= 'style="font-weight:bold;"';
            }
            $html .= ' >';
            $html .= 'Mitarbeiter';
            $html .= '</a></li>' . "\n";
            $html .= '<li><a href="';
            $url = $this->view->url(array(
                'controller' => 'bueroleitung',
                'action' => 'monate',
                    ), 'default', true);
            $html .= $url . '"';
            if($url == $this->view->requestURI) {
                $html .= 'style="font-weight:bold;"';
            }
            $html .= '>' . "\n";
            $html .= 'Monate' . "\n";
            $html .= '</a></li></ul></li>' . "\n";
        }
        return $html;
    }

}

