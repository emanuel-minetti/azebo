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
 * Diese Klasse stellt ein paar Datenbank-bezogene Methoden bereit,
 * die von allen Datenbank-basierten Klassen implementiert wird.
 *
 * @author Emanuel Minetti
 */
abstract class AzeboLib_Model_Resource_Db_Table_Abstract
    extends Zend_Db_Table_Abstract
    implements AzeboLib_Model_Resource_Db_Interface{
    
    public function saveRow($info, $row = null) {
        if($row === null) {
            $row = $this->createRow();
        }
        
        $columns = $this->info('cols');
        foreach ($columns as $column) {
            if(array_key_exists($column, $info)) {
                $row->$column = $info[$column];
            }
        }
        
        $row->save();
    }
    
}
