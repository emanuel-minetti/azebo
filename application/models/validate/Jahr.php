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
 *     Copyright 2012-19 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft ob alle Monate des abgelaufenen Jahres, in denen der Mitarbeiter
 * eine Arbeitsregel hatte, abgeschlossen sind.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Jahr extends Zend_Validate_Abstract {

    const FEHLEND = 'fehlend';

    protected $_messageTemplates = array(
        self::FEHLEND => '',
    );

    public function isValid($value, $context = null) {

        // hole die Daten
        $monat = new Zend_Date($context['monat'], 'MM.yyyy');
        $monat->subYear(1);
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        // ermittle ab wann die Monate abgeschlossen sein müssen
        $beginn = new Zend_Date($arbeitsregelTabelle->
                                getArbeitsbeginnNachMitarbeiterIdUndJahr(
                                        $mitarbeiter->id, $monat));

        // teste
        $fehlMonate = array();
        $fehler = false;
        $arbeitsmonatTabelle = new Azebo_Resource_Arbeitsmonat();
        while ($beginn->compareYear($monat) == 0) {
            $arbeitsmonat = $arbeitsmonatTabelle->
                    getArbeitsmonatNachMitabeiterIdUndMonat(
                    $mitarbeiter->id, $beginn);
            if ($arbeitsmonat === null) {
                $fehlMonate[] = $beginn->toString(Zend_Date::MONTH_NAME);
                $fehler = true;
            }
            $beginn->addMonth(1);
        }

        // falls Monate fehlten, bastele die Fehlermeldung zusammen
        if ($fehler) {
            $anzahl = count($fehlMonate);
            if ($anzahl == 1) {
                $meldung = 'Sie müssen den Monat ' . $fehlMonate[0];
            } elseif ($anzahl == 2) {
                $meldung = 'Sie müssen die Monate ' . $fehlMonate[0] . ' und ' .
                        $fehlMonate[1];
            } else {
                $meldung = 'Sie müssen die Monate ';
                for ($index = 0; $index < $anzahl - 2; $index++) {
                    $meldung .= $fehlMonate[$index] . ', ';
                }
                $meldung .= $fehlMonate[$anzahl - 2] . ' und ' .
                        $fehlMonate[$anzahl - 1];
            }
            $meldung .= ' abschließen, um das Jahr abzuschließen!';
            $this->setMessage($meldung, self::FEHLEND);
            $this->_error(self::FEHLEND);
            return false;
        }
        return true;
    }

}
