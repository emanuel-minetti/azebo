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
 * Prüft ob an jedem Tag des Monats, an dem der Mitarbeiter eine Sollarbeitszeit
 * hat, ein Eintrag vorhanden ist. Also ob entweder eine Dienstbefreiung 
 * angegeben ist oder Beginn und Ende für diesen Tag eingetragen wurden. Wurde
 * außerdem ein Nachmittag hinzugefügt, so müssen auch bei diesem Beginn und
 * Ende eingetragen sein.
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_Monat extends Zend_Validate_Abstract {
    
    const FEHLT = 'Fehlt';
    const VORJAHR = 'Vorjahr';

    protected $_messageTemplates = array(
        self::FEHLT => '',
        self::VORJAHR => 'Der Monat kann nicht abgeschlossen werden, bevor das
            Vorjahr abgeschlossen wurde.',
    );

    public function isValid($value, $context = null) {
        $isValid = true;

        // hole die Daten
        $monat = new Zend_Date($context['monat'], 'MM.yyyy');
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        $model = new Azebo_Model_Mitarbeiter();
        $arbeitstage = $model->getArbeitstageNachMonatUndMitarbeiter(
                $monat, $mitarbeiter);
        
        // prüfen, ob das Vorjahr abgeschlossen ist
        if($mitarbeiter->getUebertragenBis()->get(Zend_Date::YEAR) <
                $monat->get(Zend_Date::YEAR) - 1) {
            $this->_error(self::VORJAHR);
            $isValid = false;
        }

        //prüfen, ob alle nötigen Tage ausgefüllt sind
        $fehltage = array();
        foreach ($arbeitstage as $arbeitstag) {
            if ($arbeitstag->getRegel() !== null &&
                    ($arbeitstag->befreiung == 'keine' ||
                    $arbeitstag->befreiung === null)) {
                // falls eine Regel vorliegt und keine Befreiung angegeben...
                if ($arbeitstag->getBeginn() === null ||
                        $arbeitstag->getEnde() === null) {
                    // ... dann müssen Beginn und Ende gesetzt sein
                      $fehltage[] = $arbeitstag->getTag();
                }
            }
            if($arbeitstag->getNachmittag()) {
                // falls einNachmittag hinzugefügt wurde...
                if($arbeitstag->getBeginn() === null ||
                        $arbeitstag->getEnde() === null ||
                        $arbeitstag->getBeginnNachmittag() === null ||
                        $arbeitstag->getEndeNachmittag() === null) {
                    // ... dann müssen beide Beginn und beide Ende gesetzt sein
                    $fehltage[] = $arbeitstag->getTag();
                }
            }
        }
        
        //Fehlermeldung zusammenbasteln
        if(count($fehltage) > 0) {
            $isValid = false;
            if(count($fehltage) == 1) {
                $message = 'Es fehlt ein Eintrag für den ' .
                        $fehltage [0]->toString('dd.MM') .
                        '! Bitte tragen Sie für diesen Tag Ihre Arbeitszeit
                            oder eine Dienstbefreiung ein.';
            } else {
                $message = 'Es fehlt ein Eintrag für die Tage ';
                for ($index = 0; $index < count($fehltage) -2 ; $index++) {
                    $message .= $fehltage[$index]->toString('dd.MM., ');
                }
                $message .= $fehltage[count($fehltage) -2]->toString('dd.MM') . ' und ' .
                        $fehltage[count($fehltage) -1]->toString('dd.MM') .
                        '! Bitte tragen Sie für diese Tage Ihre Arbeitszeit
                            oder eine Dienstbefreiung ein.';
            }
            $this->setMessage($message, self::FEHLT);
            $this->_error(self::FEHLT);
        }

        return $isValid;
    }

}
