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
 *
 * @author Emanuel Minetti
 */
interface Azebo_Resource_Arbeitstag_Item_Interface {

    /**
     * @return Zend_Date|null 
     */
    public function getBeginn();

    /**
     * @return Zend_Date|null 
     */
    public function getEnde();

    public function setBeginn($beginn);

    public function setEnde($ende);

    /**
     * @return Zend_Date|null 
     */
    public function getTag();

    public function setTag($tag);

    /**
     * Prüft ob ein Datum ein gesetzlicher Feiertag in Berlin ist.
     * Berücksichtigt auch Samstage und Sonntage.
     * 
     * Liefert ein Array mit den Eigenschaften 'name' und 'feiertag'
     * zurück. 'name' ist ein string mit dem Namen des Feiertags.
     * 'feiertag' ist ein boolean, der true ist falls das Datum ein
     * Feiertag ist.
     * 
     * @return array 
     */
    public function getFeiertag();
    
    public function getRegel();
    
    public function getAnwesend();

    public function getIst();
    
    public function getSaldo();
    
    public function getNachmittag();
    
    public function toggleNachmittag();
    
    public function getBeginnNachmittag();
    
    public function setBeginnNachmittag($beginn);
    
    public function getEndeNachmittag();
    
    public function setEndeNachmittag($beginn);
    
}

