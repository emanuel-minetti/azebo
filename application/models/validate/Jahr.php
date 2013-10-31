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
 * Prüft ob durch den Abschluss eines Monats der Resturlaub des laufenden Jahres
 * negative Werte annehmen würde.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Jahr extends Zend_Validate_Abstract {

    const FEHLEND = 'fehlend';

    protected $_messageTemplates = array(
        self::FEHLEND => '',
    );

    public function isValid($value, $context = null) {
        //TODO Debugging entfernen!
        $log = Zend_Registry::get('log');
        $log->debug('Anfang');

        // hole die Daten
        $monat = new Zend_Date($context['monat'], 'MM.yyyy');
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        $beginn = new Zend_Date($arbeitsregelTabelle->
                                getArbeitsbeginnNachMitarbeiterIdUndJahr(
                                        $mitarbeiter->id, $monat));
        $log->debug('Beginn1: ' . $beginn->toString());
        $erster = new Zend_Date($monat);
        $erster->setMonth(1);
        $erster->setDay(1);
        if ($beginn->compareDate($erster) == -1) {
            $beginn = $erster;
        }
        $log->debug('Beginn2: ' . $beginn->toString());
        

        // teste
        $fehlMonate = array();
        $fehler = false;
        $arbeitsmonatTabelle = new Azebo_Resource_Arbeitsmonat();
        while ($beginn->compareYear($monat) == 0) {
            $log->debug('Monat geprüft: ' . $beginn->toString());
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
            $meldung .= ' abschließen, bevor Sie das Jahr abschließen können!';
            $log->debug($meldung);
            $this->setMessage($meldung, self::FEHLEND);
            $this->_error(self::FEHLEND);
            return false;
        }
        return true;
    }

}
