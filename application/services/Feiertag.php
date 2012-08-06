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
 * Description of Feiertag
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Feiertag {
    
    public $log;
    
    public $karfreitag;
    public $ostermontag;
    public $himmelfahrt;
    public $pfingstmontag;


    public function __construct($jahr) {
        $this->log = Zend_Registry::get('log');
        
        $tageNach21Maerz = easter_days($jahr);
        $ostersonntag = new Zend_Date('21.3.' . $jahr);
        $ostersonntag->add($tageNach21Maerz, Zend_Date::DAY);
        
        $this->log->info('Ostersonntag ist: ' . $ostersonntag);
        $this->karfreitag = new Zend_Date($ostersonntag);
        $this->karfreitag->add('-2', Zend_Date::DAY);
        $this->ostermontag = new Zend_Date($ostersonntag);
        $this->ostermontag->add(1, Zend_Date::DAY);
        $this->himmelfahrt = new Zend_Date($ostersonntag);
        $this->himmelfahrt->add(39, Zend_Date::DAY);
        $this->pfingstmontag = new Zend_Date($ostersonntag);
        $this->pfingstmontag->add(50, Zend_Date::DAY);
    }

    public function feiertag(Zend_Date $datum) {
        /*
         * Die festen gesetzlichen Feiertage in Berlin sind:
         * 
         * -Neujahr (1.1.)
         * -Tag der Arbeit (1.5.)
         * -Tag der dt. Einheit (3.10.)
         * -1. Weihnachtsfeiertag (25.12.)
         * -2. Weihnachtsfeiertag (26.12.)
         * 
         * Die beweglichen gesetzlichen Feiertage in Berlin sind:
         * 
         * -Karfreitag (Ostersonntag - 2)
         * -Ostermontag (Ostersonntag + 1)
         * -Christi Himmelfahrt (Ostersonntag + 39)
         * -Pfingstmontag (Ostersonntag + 50)
         */
        
        //Normalfall
        $erg = array(
            'feiertag' => false,
            'name' => '',
        );
        
        //Neujahr
        if($datum->compareDate(
                new Zend_Date(array(
                    'day' => 1,
                    'month' => 1,
                    'year' => $datum->toString('yyyy')))) == 0) {
            $erg['name'] = 'Neujahr';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Tag der Arbeit
        if($datum->compareDate(
                new Zend_Date(array(
                    'day' => 1,
                    'month' => 5,
                    'year' => $datum->toString('yyyy')))) == 0) {
            $erg['name'] = 'Tag der Arbeit';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Tag der dt. Einheit
        if($datum->compareDate(
                new Zend_Date(array(
                    'day' => 3,
                    'month' => 10,
                    'year' => $datum->toString('yyyy')))) == 0) {
            $erg['name'] = 'Tag der dt. Einheit';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //1. Weihnachtsfeiertag
        if($datum->compareDate(
                new Zend_Date(array(
                    'day' => 25,
                    'month' => 12,
                    'year' => $datum->toString('yyyy')))) == 0) {
            $erg['name'] = '1. Weihnachtsfeiertag';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //2. Weihnachtsfeiertag
        if($datum->compareDate(
                new Zend_Date(array(
                    'day' => 26,
                    'month' => 12,
                    'year' => $datum->toString('yyyy')))) == 0) {
            $erg['name'] = '2. Weihnachtsfeiertag';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Karfreitag
        if($datum->compareDate($this->karfreitag) == 0) {
            $erg['name'] = 'Karfreitag';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Ostermontag
        if($datum->compareDate($this->ostermontag) == 0) {
            $erg['name'] = 'Ostermontag';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Christi Himmelfahrt
        if($datum->compareDate($this->himmelfahrt) == 0) {
            $erg['name'] = 'Christi Himmelfahrt';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Pfingstmontag
        if($datum->compareDate($this->pfingstmontag) == 0) {
            $erg['name'] = 'Pfingstmontag';
            $erg['feiertag'] = true;
            return $erg;
        }
        
        //Samstag und Sonntag
        if($datum->get(Zend_Date::WEEKDAY_DIGIT) == 0 ||
                $datum->get(Zend_Date::WEEKDAY_DIGIT) == 6) {
            $erg['feiertag'] = true;
        }
        
        return $erg;
    }

}

