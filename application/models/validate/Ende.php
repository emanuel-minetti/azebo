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
 * Prüft das eingetragene Ende der Arbeitszeit gegen das in der DB oder der
 * Konfiguration festgelegte Rahmen- und Kernende. An Feiertagen wird nicht
 * geprüft.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Ende extends Zend_Validate_Abstract {

    const NACH_RAHMEN = 'EndeNachRahmen';
    const VOR_KERN = 'EndeVorKern';

    protected $_messageTemplates = array(
        self::NACH_RAHMEN => 'Das eingetragene Ende liegt nach dem Ende der
            Rahmenarbeitszeit! Bitte geben Sie eine Bemerkung ein.',
        self::VOR_KERN => 'Das eingetragene Ende liegt vor dem Ende der
            Kernarbeitszeit! Bitte geben Sie eine Bemerkung und/oder
            Dienstbefeiung ein.',
    );

    public function isValid($value, $context = null) {
        //hole den Tag und prüfe auf Feiertag
        $tag = new Zend_Date($context['tag'], 'dd.MM.YYYY');
        $ns = new Zend_Session_Namespace();
        $feiertagsservice = $ns->feiertagsservice;
        $feiertag = $feiertagsservice->feiertag($tag);

        if ($feiertag['feiertag'] == false) {
            //kein Feiertag, also prüfen
            //hole die Daten
            $zeitenConfig = new Zend_Config_Ini(
                            APPLICATION_PATH . '/configs/zeiten.ini', 'alle');
            $rahmenEndeAlle = $zeitenConfig->rahmen->ende->normal;
            $kernEndeNormalAlle = $zeitenConfig->kern->ende->normal;
            $kernEndeFreitagAlle = $zeitenConfig->kern->ende->freitag;
            $mitarbeiter = $ns->mitarbeiter;
            $arbeitstag = $mitarbeiter->getArbeitstagNachTag($tag);
            $arbeitsregel = $arbeitstag->getRegel();
            $rahmenEnde = null;
            $kernEnde = null;
            
            if ($arbeitsregel !== null) {
                // Mitarbeiter hat indviduelle Arbeitszeitregelung, also anwenden
                $rahmenEnde = $arbeitsregel->getRahmenEnde();
                $kernEnde = $arbeitsregel->getKernEnde();
            } else {
                //TODO Nachfragen, was passieren soll!!
                return true;
            }
            if ($rahmenEnde === null) {
                // Mitarbeiter hat keine indviduelle Arbeitszeitregelung,
                // also Normalfall anwenden
                $rahmenEnde = new Zend_Date($rahmenEndeAlle, Zend_Date::TIMES);
            }
            if ($kernEnde === null) {
                // Mitarbeiter hat keine indviduelle Arbeitszeitregelung,
                // also Normalfall anwenden
                $kernEndeNorm = new Zend_Date($kernEndeNormalAlle, Zend_Date::TIMES);
                $kernEndeFr = new Zend_Date($kernEndeFreitagAlle, Zend_Date::TIMES);
                if ($tag->get(Zend_Date::WEEKDAY_DIGIT) == 5) {
                    $kernEnde = $kernEndeFr;
                } else {
                    $kernEnde = $kernEndeNorm;
                }
            }
            $bemerkung = $context['bemerkung'];
            $bemerkung = trim($bemerkung);
            $befreiung = $context['befreiung'];
            $befreiung = trim($befreiung);

            //Uhrzeiten für die 3-Uhr-Regelung
            $mitternacht = new Zend_Date('00:00:00', Zend_Date::TIMES);
            $dreiUhr = new Zend_Date('03:00:00', Zend_Date::TIMES);

            //prüfe
            if ($value->compareTime($rahmenEnde) == 1 ||
                    ($value->compareTime($mitternacht) != -1 &&
                    $value->compareTime($dreiUhr) != 1)) {
                // ende nach dem Rahmenende oder zwischen 0:00 und 3:00
                if ($bemerkung == '') {
                    $this->_error(self::NACH_RAHMEN);
                    return false;
                }
            }
            if ($value->compareTime($kernEnde) == -1) {
                //vor Ende der Kernarbeitszeit
                if ($bemerkung == '' && $befreiung == 'keine') {
                    $this->_error(self::VOR_KERN);
                    return false;
                }
            }
        }
        return true;
    }

}
