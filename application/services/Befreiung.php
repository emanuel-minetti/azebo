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
 * Stellt die Befreiungsoptionen für einen Mitarbeiter zur Verfügung.
 * 
 * Diese Klasse definiert die Klassenkonstanten, die wiederum die einzelnen
 * zulässigen Werte in der Tabelle 'arbeitstag' definieren.
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Befreiung {
    //TODO Dienstbefreiungsoptionen ergänzen!
    
    //Konstanten
    const KEINE = 'keine';
    const URLAUB = 'urlaub';
    const AZV = 'azv';

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
        return array(
            self::KEINE => '',
            self::URLAUB => 'Urlaub',
            self::AZV => 'AZV',
        );
    }

}

