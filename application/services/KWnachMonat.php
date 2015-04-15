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
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Description of Azebo_Service_KWnachMonat
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_KWnachMonat {

    /**
     * Berechnet die Ist-Arbeitszeiten der Kalenderwochen eines Monats,
     * gegeben als Zend_Date, und eines Mitarbeiters, gegeben als Id.
     * 
     * Gibt ein Array zurück. Die Indizes des Arrays sind die Kalenderwochen als
     * int zwischen 1 und 53, und die Werte sind die Ist-Arbeitszeiten der
     * Woche, als Azebo_Model_Saldo.
     * 
     * @param Zend_Date $monat
     * @param int $mitarbeiterId
     * @return array
     */
    public function getIstKwNachMonatundMitarbeiterId($datum, $mitarbeiterId) {
        $monat = new Zend_Date($datum);
        $monat->setDay(1);
        $kwAnfang = $monat->get(Zend_Date::WEEK);
        $monat->setDay($monat->get(Zend_Date::MONTH_DAYS));
        $kwEnde = $monat->get(Zend_Date::WEEK);
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        $ergebnis = array();
        for ($kw = $kwAnfang; $kw <= $kwEnde; $kw++) {
            $ergebnis[$kw] = $arbeitstagTabelle->
                    getIstNachKalenderwocheUndMitarbeiterId(
                    $kw, $mitarbeiterId, $datum->get(Zend_Date::YEAR));
        }
        return $ergebnis;
    }

}

