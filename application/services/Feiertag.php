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
 * Stellt die gesetzlichen Feiertage in Berlin zur Verfügung
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Feiertag {

    public $karfreitag;
    public $ostermontag;
    public $himmelfahrt;
    public $pfingstmontag;
    public $hochschule;
    public $tdotSa;
    public $tdotSo;

    /**
     * Stellt die beweglichen Feiertage des Jahres zur Verfügung
     * 
     * @param Zend_Date $jahr 
     */
    public function __construct($jahr) {

        $tageNach21Maerz = easter_days($jahr);
        $ostersonntag = new Zend_Date('21.3.' . $jahr);
        $ostersonntag->add($tageNach21Maerz, Zend_Date::DAY);

        $this->karfreitag = new Zend_Date($ostersonntag);
        $this->karfreitag->add('-2', Zend_Date::DAY);
        $this->ostermontag = new Zend_Date($ostersonntag);
        $this->ostermontag->add(1, Zend_Date::DAY);
        $this->himmelfahrt = new Zend_Date($ostersonntag);
        $this->himmelfahrt->add(39, Zend_Date::DAY);
        $this->pfingstmontag = new Zend_Date($ostersonntag);
        $this->pfingstmontag->add(50, Zend_Date::DAY);

        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $this->hochschule = $mitarbeiter->getHochschule();
        if ($this->hochschule == 'khb') {
            $zeitenConfig = $ns->zeiten;
            $jahrString = "jahr$jahr";
            $tdotSa = $zeitenConfig->tagdotuer->sa->$jahrString;
            $tdotSo = $zeitenConfig->tagdotuer->so->$jahrString;
            if (isset($tdotSa)) {
                $this->tdotSa = new Zend_Date($tdotSa, 'dd.MM.yyyy');
            }
            if (isset($tdotSo)) {
                $this->tdotSo = new Zend_Date($tdotSo, 'dd.MM.yyyy');
            }
        }
    }

    /**
     * Prüft ob ein Datum ein gesetzlicher Feiertag in Berlin ist.
     * Berücksichtigt auch Samstage und Sonntage.
     * 
     * Liefert ein Array mit den Eigenschaften 'name' und 'feiertag'
     * zurück. 'name' ist ein string mit dem Namen des Feiertags.
     * 'feiertag' ist ein boolean, der true ist falls das Datum ein
     * Feiertag ist.
     * 
     * @param Zend_Date $datum
     * @return array 
     */
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
        $feiertag = array(
            'feiertag' => false,
            'name' => '',
        );

        //Neujahr
        if ($datum->get(Zend_Date::MONTH) == 1) {
            if ($datum->get(Zend_Date::DAY) == 1) {
                $feiertag['name'] = 'Neujahr';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //Tag der Arbeit
        if ($datum->get(Zend_Date::MONTH) == 5) {
            if ($datum->get(Zend_Date::DAY) == 1) {
                $feiertag['name'] = 'Tag der Arbeit';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //Tag der dt. Einheit
        if ($datum->get(Zend_Date::MONTH) == 10) {
            if ($datum->get(Zend_Date::DAY) == 3) {
                $feiertag['name'] = 'Tag der dt. Einheit';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //Heilig Abend
        if ($datum->get(Zend_Date::MONTH) == 12) {
            if ($datum->get(Zend_Date::DAY) == 24) {
                $feiertag['name'] = 'Weihnachten';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //1. Weihnachtsfeiertag
        if ($datum->get(Zend_Date::MONTH) == 12) {
            if ($datum->get(Zend_Date::DAY) == 25) {
                $feiertag['name'] = '1. Feiertag';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //2. Weihnachtsfeiertag
        if ($datum->get(Zend_Date::MONTH) == 12) {
            if ($datum->get(Zend_Date::DAY) == 26) {
                $feiertag['name'] = '2. Feiertag';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //Silvester
        if ($datum->get(Zend_Date::MONTH) == 12) {
            if ($datum->get(Zend_Date::DAY) == 31) {
                $feiertag['name'] = 'Silvester';
                $feiertag['feiertag'] = true;
                return $feiertag;
            }
        }

        //Karfreitag
        if ($datum->compareDate($this->karfreitag) == 0) {
            $feiertag['name'] = 'Karfreitag';
            $feiertag['feiertag'] = true;
            return $feiertag;
        }

        //Ostermontag
        if ($datum->compareDate($this->ostermontag) == 0) {
            $feiertag['name'] = 'Ostermontag';
            $feiertag['feiertag'] = true;
            return $feiertag;
        }

        //Christi Himmelfahrt
        if ($datum->compareDate($this->himmelfahrt) == 0) {
            $feiertag['name'] = 'Himmelfahrt';
            $feiertag['feiertag'] = true;
            return $feiertag;
        }

        //Pfingstmontag
        if ($datum->compareDate($this->pfingstmontag) == 0) {
            $feiertag['name'] = 'Pfingstmontag';
            $feiertag['feiertag'] = true;
            return $feiertag;
        }

        //Samstag und Sonntag
        if ($datum->get(Zend_Date::WEEKDAY_DIGIT) == 0 ||
                $datum->get(Zend_Date::WEEKDAY_DIGIT) == 6) {
            $feiertag['feiertag'] = true;
        }

        if ($this->hochschule == 'khb') {
            if (($this->tdotSa !== null &&
                    $datum->compareDate($this->tdotSa) == 0) ||
                    ($this->tdotSo !== null &&
                    $datum->compareDate($this->tdotSo) == 0)) {
                $feiertag['name'] = 'Tag der offenen Tür';
            }
        }

        return $feiertag;
    }

}
