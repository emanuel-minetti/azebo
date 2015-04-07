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
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Prüft, ob ein Monat abgeschlossen werden kann. Im Einzelnen werden die
 * folgenden zwei Punkte geprüft:
 * Erstens: Prüft, ob alle Monate vorher, die noch nicht übertragen waren,
 * abgeschlossen sind.
 * Zweitens: Prüft, ob an jedem Tag des Monats, an dem der Mitarbeiter eine Sollarbeitszeit
 * hat, ein Eintrag vorhanden ist. Also ob entweder eine Dienstbefreiung 
 * angegeben ist oder Beginn und Ende für diesen Tag eingetragen wurden. Wurde
 * außerdem ein Nachmittag hinzugefügt, so müssen auch bei diesem Beginn und
 * Ende eingetragen sein.
 * 
 * @author Emanuel Minetti
 */
class Azebo_Validate_Monat extends Zend_Validate_Abstract {
    
    const FEHLT_TAG = 'FehltTag';
    const FEHLT_MONAT = 'FehltMonat';

    protected $_messageTemplates = array(
        self::FEHLT_TAG => '',
        self::FEHLT_MONAT => '',
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
        
        //die nicht abgeschlossenen Monate holen ...
        $log = Zend_Registry::get('log');
        $monate = $mitarbeiter->getFehlmonateBis($monat);
        //$log->debug('Monate: ' . print_r($monate,true));
        //und die Fehlermeldung (für die Monate) zusammenbasteln
        if (count($monate) > 0) {
            $isValid = false;
            $message = '';
            if (count($monate) == 1) {
                $message = 'Der Monat ' . $monate[0]->toString('MMMM YYYY') . ' ist'
                        . ' noch nicht abgeschlossen!';
            } else {
                $message = 'Die Monate ';
                for ($i = 0; $i < count($monate) - 2; $i++) {
                    $message .= $monate[$i]->toString('MMMM YYYY') . ', ';
                }
                $message .= $monate[count($monate) - 2]->toString('MMMM YYYY');
                $message .= ' und ' . $monate[count($monate) - 1]->toString('MMMM YYYY');
                $message .= ' sind noch nicht abgeschlossen!';
            }
            
            $message .= ' Bitte schließen sie die'
                        . ' Monate in chronologischer Reihenfolge ab.';
            $this->setMessage($message, self::FEHLT_MONAT);
            $this->_error(self::FEHLT_MONAT);
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
        
        //Fehlermeldung (für die Tage) zusammenbasteln
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
                $message .= $fehltage[count($fehltage) -2]->toString('dd.MM') . 
                        ' und ' .
                        $fehltage[count($fehltage) -1]->toString('dd.MM') .
                        '! Bitte tragen Sie für diese Tage Ihre Arbeitszeit
                            oder eine Dienstbefreiung ein.';
            }
            $this->setMessage($message, self::FEHLT_TAG);
            $this->_error(self::FEHLT_TAG);
        }

        return $isValid;
    }

}
