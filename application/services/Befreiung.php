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
 *     Copyright 2012-17 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Stellt die Befreiungsoptionen für einen Mitarbeiter zur Verfügung.
 * 
 * Diese Klasse definiert die Klassenkonstanten, die wiederum die einzelnen
 * zulässigen Werte in der Tabelle 'arbeitstag' definieren.
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Befreiung {
    
    //Konstanten
    const KEINE = 'keine';
    const URLAUB = 'urlaub';
    const AZV = 'azv';
    const REISE = 'reise';
    const FA = 'fa';
    const FORTBILDUNG = 'fortbildung';
    const KRANKHEIT = 'krankheit';
    const KUR = 'kur';
    const SONSTIGE = 'sonstige';
    const SONDER = 'sonder';
    const FT = 'ft';
//    const GLEITZEIT = 'gleitzeit';
    
    /**
     * Gibt die für einen Mitarbeiter zulässigen Befreiungsoptionen zurück.
     * Das zurückgelieferte Array enthält die zugehörige Konstante als Index
     * und das zugehörige Label als Wert.
     * 
     * @param Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter
     * @return array 
     */
    public function getOptionen(
            Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter) {
        $optionen = array(
            self::KEINE => '',
            self::URLAUB => 'Urlaub',
            self::REISE => 'Dienstreise',
            self::FA => 'Freizeitausgleich',
            self::FORTBILDUNG => 'Fortbildung',
            self::KRANKHEIT => 'Krankheit',
            self::KUR => 'Kur',
            self::SONSTIGE => 'Sonstige Dienstbefreiung',
            self::SONDER => 'Sonderurlaub',
//            self::GLEITZEIT => 'Gleitzeittag',
        );
        
        if($mitarbeiter->getBeamter() === true) {
            $optionen[self::AZV] = 'AZV'; 
        } else {
            $optionen[self::FT] = 'Freistellungstag';
        }
        
        return $optionen;
    }

}
