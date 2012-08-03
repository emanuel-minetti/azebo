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
 * Erzeugt über die öffentliche Methode die Listenelemente füe die
 * Übersichtsnavigation.
 * 
 * Sucht das gegenwärtige Jahr, und setzt die Links entsprechend.
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Jahresliste extends Zend_View_Helper_Abstract {
    public function jahresliste() {
        $html = "";
        $date = new Zend_Date();
        $date->setDate($date->getYear());
        
        for ($i = 0; $i < 2; $i++) {
            $html .= '<li><a href="';
            $html .= $this->view->url(array(
                'jahr' => $date->toString('yyyy'),
                    ), 'uebersicht', true);
            $html .= '">';
            $html .= $date->toString('yyyy');
            $html .= "</a></li>\n";
            $date->addYear(-1);
        }
        return $html;
    }
    
}

