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
        if (($beginn->compareTime('03:01:00') != -1 && $ende->compareTime('03:01:00') != -1) ||
                ($beginn->compareTime('03:00:00') != 1 && $ende->compareTime('03:00:00') != 1)) {
            // Beginn und Ende beide nach 03:00 bzw. beide vor oder gleich 03:00,
            // also alles normal
            if ($beginn->compareTime($ende) == -1) {
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
            if ($beginn->compareTime('03:00:00') != 1) {
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

    /**
     * Gibt die Ist-Arbeitszeit zurück.
     * Erwartet eine Anwesenheitszeit und optional eine Angabe, ob eine Pause
     * abgezogen werden soll oder nicht.
     * 
     * @param Zend_Date $anwesend Die Zeit, die der Mitarbeiter an diesem Tag
     * anwesend war.
     * @param boolean $ohnePause Optinal, ob ohne Pause gerechnet werden
     * soll oder nicht. Standard falsch.
     * @param null|boolean $pauseLang Falls die Länge der Pause nicht
     * standartmäßig berechnet werden soll (z.B. bei Nachmittagsarbeitszeit
     * für die HfM), kann hier angegeben werden ob die Pause kurz oder lang
     * sein soll. Dieser Parameter ist optional und der Standard ist null, also
     * das standardmäßige Berechnen der Pausenlänge.
     * 
     * @return Zend_Date 
     */
    public function ist(
    Zend_Date $anwesend, $ohnePause = false, $pauseLang = null) {

        $ns = new Zend_Session_Namespace();
        $pause = $ns->zeiten->pause;

        $ist = new Zend_Date($anwesend);
        if (!$ohnePause) {
            if ($pauseLang === null) {
                if ($anwesend->compare(
                                $pause->lang->ab, Zend_Date::TIMES) != 1) {
                    //weniger als lang anwesend
                    if ($anwesend->compareTime($pause->kurz->dauer) == 1) {
                        //mehr als eine kurze Pause anwesend, also ziehe Pause ab
                        $ist->sub($pause->kurz->dauer, Zend_Date::TIMES);
                    }
                } else {
                    $ist->sub($pause->lang->dauer, Zend_Date::TIMES);
                }
            } else {
                if ($pauseLang == false) {
                    $ist->sub($pause->kurz->dauer, Zend_Date::TIMES);
                } else {
                    $ist->sub($pause->lang->dauer, Zend_Date::TIMES);
                }
            }
        }
        return $ist;
    }


    //TODO Kommentieren!

    public function saldo($ist, $soll)
    {
        $sollKopie = new Zend_Date($soll);
        $istKopie = new Zend_Date($ist);
        if ($ist !== null) {
            if ($istKopie->compareTime($sollKopie) == -1) {
                // 'ist' < 'soll'
                $positiv = false;
                $saldo = $sollKopie->sub($istKopie, Zend_Date::TIMES);
            } else {
                $positiv = true;
                $saldo = $istKopie->sub($soll, Zend_Date::TIMES);
            }
        } else {
            $positiv = false;
            $saldo = $sollKopie;
        }
        $stunden = $saldo->get(Zend_Date::HOUR_SHORT);
        $minuten = $saldo->get(Zend_Date::MINUTE_SHORT);
        $saldo = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        return $saldo;
    }
}

