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
 *     Copyright 2012-17 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft, ob ein Monatsabschluss zurückgenommen werden kann.
 * Es wird geprüft, ob der zurückzunehmende Abschluss vor dem letzten
 * Übertrag liegt. und falls das der Fall ist, ob ein Eintrag in der
 * Vorjahrestabelle vorliegt. Falls kein Eintrag vorliegt, kann der Abschluss
 * nicht zurückgenomen werden. Außerdem kann der Monat nicht zurückgenommen
 * werden, falls er im Jahr vor dem Vorjahr liegt.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Zuruecknahme extends Zend_Validate_Abstract {

    const KEIN_VORJAHR = 'KeinVorjahr';
    const ZU_ALT = 'ZuAlt';

    protected $_messageTemplates = array(
        self::KEIN_VORJAHR => 'Der Abschluss kann nicht zurückgenommen werden.
            Es können nur Jahresabschlüsse zurückgenommen werden, die nach
            dem 15.4.2015 vorgenommen wurden!',
        self::ZU_ALT => 'Der Abschluss kann nicht zurückgenommen werden. Es
            können nur Abschlüsse zurückgenommen werden, die höchstens ein Jahr
            vor dem letzten Jahresabschluss liegen!',
    );

    public function isValid($value, $context = null) {
        $mitarbeiter = $context['mitarbeiter'];
        $monat = $context['monat'];
        if ($mitarbeiter->getUebertragenBis()->compareYear($monat) === 0) {
            // Der Monat liegt vor dem letzten Übertrag, also prüfe, ob
            // es ein Vorjahr für den Mitarbeiter in der DB gibt.
            // getVorjahr() liefert immer ein Item zurück, also prüfe ob schon
            // Daten eingetragen sind
            if ($mitarbeiter->getVorjahr()->getUrlaub() === null) {
                 $this->_error(self::KEIN_VORJAHR);
                 return false;
            }
        } elseif ($mitarbeiter->getUebertragenBis()->compareYear(
                        $monat) === 1) {
            // Der Monat liegt vor dem Vorjahr.
            $this->_error(self::ZU_ALT);
            return false;
        }
        return true;
        
    }
}
