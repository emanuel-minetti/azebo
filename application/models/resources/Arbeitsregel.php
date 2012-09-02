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
 * Schnittstelle zur MySQL-Tabelle 'arbeitstag'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsregel
extends AzeboLib_Model_Resource_Db_Table_Abstract
implements Azebo_Resource_Arbeitsregel_Interface {
    
    protected $_name = 'arbeitsregel';
    protected $_id = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitsregel_Item';
    protected $_referenceMap = array(
        'Arbeitsregel' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );
    
}

