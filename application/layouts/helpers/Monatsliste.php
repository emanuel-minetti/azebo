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
 * Monatsnavigation.
 * 
 * Sucht das gegenwärtige Jahr, und setzt die Links entsprechend.
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Monatsliste extends Zend_View_Helper_Abstract {

    public function monatsliste() {
        $html = "";
        $date = new Zend_Date();
        
        $date->setDate($date->getYear());
        for ($i = 0; $i < 12; $i++) {
            $html .= '<li><a href="';
            $html .= $this->view->url(array(
                'monat' => $date->toString('M'),
                'jahr' => $date->toString('yyyy'),
                    ), 'monat', true);
            $html .= '">';
            $html .= $date->toString('MMMM');
            $html .= "</a></li>\n";
            $date->addMonth(1);
        }
        return $html;
    }

}

