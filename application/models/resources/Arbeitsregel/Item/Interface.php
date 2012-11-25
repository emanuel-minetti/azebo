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
interface Azebo_Resource_Arbeitsregel_Item_Interface {

    public function getVon();
    
    public function setVon($von);
    
    public function getBis();
    
    public function setBis($bis);
    
    public function getSoll();
    
    public function getRahmenAnfang();
    
    public function getKernAnfang();
    
    public function getRahmenEnde();
    
    public function getKernEnde();
    
    public function getWochentag();
}

