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
 *     Copyright 2012-16 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Erzeugt über die öffentliche Methode die Listenelemente füe die
 * Monatsnavigation.
 * 
 * Sucht das gegenwärtige Jahr, und setzt die Links entsprechend. Ist das
 * Vorjahr noch nicht abgeschlossen, werden die Links auf das Vorjahr gesetzt.
 *
 * @author Emanuel Minetti
 */
class Zend_View_Helper_Monatsliste extends Zend_View_Helper_Abstract {

    public function monatsliste() {
        // initialisiere den HTML-String
        $html = "";
        
        // hole den Januar des laufenden Jahres
        $date = new Zend_Date();
        $date->setDate($date->getYear());
        
        // hole den Mitarbeiter aus der Session
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        if($mitarbeiter !== null) {
            if($mitarbeiter->jahresabschlussFehlt($date)) {
                // falls das vergangene Jahr noch nicht abgeschlossen ist,
                // verlinke auf das vergangene Jahr, damit der Mitarbeiter
                // noch nicht abgeschlossene Monate bearbeiten und abschließen
                // kann
                $date->subYear(1);
            }
        } 
        
        // baue den HTML-String
        for ($i = 0; $i < 12; $i++) {
            $url = $this->view->url(array(
                'monat' => $date->toString('M'),
                'jahr' => $date->toString('yyyy'),
                    ), 'monat', true);
            $html .= '<li><a href="';
            $html .= $url . '"';
            if($url == $this->view->requestURI) {
                $html .= 'style="font-weight:bold;"';
            }
            $html .= '>';
            $html .= $date->toString('MMMM');
            $html .= "</a></li>\n";
            $date->addMonth(1);
        }
        return $html;
    }

}

