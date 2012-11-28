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
 * Description of RegelEindeutig
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_RegelEindeutig extends Zend_Validate_Abstract {

    const NICHT_EINDEUTIG = 'nichtEindeutig';

    protected $_messageVariables = array(
        'regelNr' => 0,
    );
    protected $_messageTemplates = array(
        self::NICHT_EINDEUTIG => 'Die eingegebene Arbeitszeitregel kollidiert
            mit der Arbeitszeitregel Nummer %regelNr%',
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);

        if (is_array($context)) {
            $id = $context['id'];
            $filter = new Azebo_Filter_DatumAlsDate();
            $von = $filter->filter($context['von']);
            $bis = $filter->filter($context['bis']);
            $wochentag = $context['wochentag'];
            $kw = $context['kw'];
            $benutzername = $context['benutzername'];
            $model = new Azebo_Model_Mitarbeiter();
            $arbeitsregeln = $model->getArbeitsregelnNachBenutzername($benutzername);
            foreach ($arbeitsregeln as $arbeitsregel) {
                if ($id != $arbeitsregel->id) {
                    $ueberschneidung = false;
                    if ($bis === null && $arbeitsregel->getBis() === null) {
                        // beide == null
                        $ueberschneidung = true;
                    } elseif ($arbeitsregel->getBis() === null) {
                        // $bis != null
                        if ($arbeitsregel->getVon()->compareDate($bis) != 1) {
                            $ueberschneidung = true;
                        }
                    } elseif ($bis === null) {
                        // $arbeitsregel->getBis() != null
                        if ($von->compareDate($arbeitsregel->getBis()) != 1) {
                            $ueberschneidung = true;
                        }
                    } else {
                        // beide != null
                        if ($von->compareDate($arbeitsregel->getVon()) == -1) {
                            //zu prüfende Regel beginnt vor der aktuellen Regel
                            if ($bis->compareDate($arbeitsregel->getVon()) != -1) {
                                $ueberschneidung = true;
                            }
                        } elseif ($von->compareDate($arbeitsregel->getVon()) == 1) {
                            //aktuelle Regel beginnt vor der zu prüfenden Regel
                            if ($bis->compareDate($arbeitsregel->getVon()) == 1) {
                                $ueberschneidung = true;
                            }
                        } else {
                            //beide Regeln beginnen am selben Tag
                            $ueberschneidung = true;
                        }
                    }
                    //TODO mit Logging testen!
                    $log = Zend_Registry::get('log');
                    $log->debug('Zu Testen Von: ' . $von);
                    $log->debug('Zu Testen Bis: ' . $bis);
                    $log->debug('Aktuell Von: ' . $arbeitsregel->getVon());
                    $log->debug('Aktuell Bis: ' . $arbeitsregel->getBis());
                    $log->debug('Überschneidung: ' . $ueberschneidung);

                    if (!$ueberschneidung) {
                        //break;
                    } else {
                        //von und bis überschneiden sich, also prüfe Wochentag und
                        //Kalenderwoche
                        //TODO Hier bin ich!
                    }
                } // if id != arbeitsregel->id
            } // foreach          
        }
        return true;
    }

}

