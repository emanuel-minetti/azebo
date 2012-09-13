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
 * Berechnet Arbeitszeiten und berücksichtigt dabei, dass der Arbeitstag um
 * 03:01 beginnt und um 03:00 endet.
 *
 * @author Emanuel Minetti
 */

class Azebo_Service_Zeitrechner {
    
    /**
     * Gibt bei gegebenen Beginn und Ende die Anwesenheitszeit zurück, oder NULL
     * falls die gegebenen Zeiten nicht gültig sind. Der Arbeitstag beginnt
     * dabei um 03:01 und endet um 03:00.
     * 
     * @param Zend_Date $beginn
     * @param Zend_Date $ende
     * @return Zend_Date|null 
     */
    public function anwesend(Zend_Date $beginn, Zend_Date $ende) {
        if(($beginn->compareTime('03:01:00') != -1 && $ende->compareTime('03:01:00') != -1) || 
                ($beginn->compareTime('03:00:00') != 1 && $ende->compareTime('03:00:00') != 1)) {
            // Beginn und Ende beide nach 03:00 bzw. beide vor oder gleich 03:00,
            // also alles normal
            if($beginn->compareTime($ende) == -1) {
                // Beginn vor Ende, also gültig
                $anwesend = new Zend_Date($ende);
                $anwesend->subTime($beginn);
                return $anwesend;
            } else {
                // Beginn gleich oder nach Ende, also ungültig
                return null;
            } 
        } else {
            //Eine Zeit vor oder gleich 03:00, die andere danach
            if($beginn->compareTime('03:00:00') != 1) {
                //Beginn vor oder gleich 03:00 und Ende nach 03:00, also ungültig
                return null;
            } else {
                //Beginn nach 03:00 und Ende vor oder gleich 03:00, also rechnen
                $anwesend = new Zend_Date('00:00:00');
                $anwesend->subTime($beginn);
                $anwesend->addTime($ende);
                return $anwesend;
            }   
        }
    }
    
}
