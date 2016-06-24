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
 * Description of RegelEindeutig
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_RegelEindeutig extends Zend_Validate_Abstract {

    const NICHT_EINDEUTIG = 'nichtEindeutig';

    protected $_messageTemplates = array(
        self::NICHT_EINDEUTIG => '',
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);

        //$log = Zend_Registry::get('log');

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
            $lfdNr = 0;
            $kollisionen = array();

            foreach ($arbeitsregeln as $arbeitsregel) {
                $lfdNr++;
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
                            if ($von->compareDate($arbeitsregel->getBis()) != 1) {
                                $ueberschneidung = true;
                            }
                        } else {
                            //beide Regeln beginnen am selben Tag
                            $ueberschneidung = true;
                        }
                    }

                    if (!$ueberschneidung) {
                        continue;
                    } else {
                        //von und bis überschneiden sich, also prüfe Wochentag 
                        //und Kalenderwoche
                        if ($wochentag == 'alle' ||
                                strtolower($arbeitsregel->wochentag) == 'alle') {
                            if ($kw == 'alle' ||
                                    $arbeitsregel->kalenderwoche == 'alle' ||
                                    $kw == $arbeitsregel->kalenderwoche) {
                                $kollisionen[] = $lfdNr;
                            }
                        } else {
                            // beide Wochentage != 'alle'
                            if ($wochentag ==
                                    strtolower($arbeitsregel->wochentag)) {
                                if ($kw == 'alle' ||
                                        $arbeitsregel->kalenderwoche == 'alle' ||
                                        $kw == $arbeitsregel->kalenderwoche) {
                                    $kollisionen[] = $lfdNr;
                                }
                            }
                        }
                    } //else (ueberschneidung == true)
                } // if id != arbeitsregel->id
            } // foreach
            if (count($kollisionen) == 0) {
                return true;
            }

            if (count($kollisionen) == 1) {
                $message = 'Die eingegebene Arbeitszeitregel kollidiert mit der Regel Nummer ' . $kollisionen[0];
            } else {
                $message = 'Die eingegebene Arbeitszeitregel kollidiert mit den Regeln Nummer ';
                for ($index = 0; $index < count($kollisionen) - 2; $index++) {
                    $message .= $kollisionen[$index] . ', ';
                }
                $message .= $kollisionen[count($kollisionen) - 2] . ' und ' .
                        $kollisionen[count($kollisionen) - 1] . '.';
            }

            $this->setMessage($message, self::NICHT_EINDEUTIG);
            $this->_error(self::NICHT_EINDEUTIG);
            return false;
        }
        return true;
    }

}
