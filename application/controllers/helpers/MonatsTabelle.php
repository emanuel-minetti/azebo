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
 * Description of MonatsTabelle
 *
 * @author Emanuel Minetti
 */
class Azebo_Action_Helper_MonatsTabelle extends Zend_Controller_Action_Helper_Abstract {

    public function direct(Zend_Date $erster, Zend_Date $letzter, Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter) {

        // Hole die Befreiungsoptionen für diesen Mitarbeiter
        $befreiungService = new Azebo_Service_Befreiung();
        $befreiungOptionen = $befreiungService->getOptionen($mitarbeiter);
        $arbeitstage = $mitarbeiter->getArbeitstageNachMonat($erster);

        // Initialisiere die Daten
        $tabellenDaten = new Zend_Dojo_Data();
        $tabellenDaten->setIdentifier('datum');
        $anzahlHoheTage = 0;
        $extraZeilen = 0;

        // Iteriere über die Tage
        foreach ($arbeitstage as $arbeitstag) {
            if ($arbeitstag->tag->compare($erster, Zend_Date::DATE_MEDIUM)
                    != -1 &&
                    $arbeitstag->tag->compare($letzter, Zend_Date::DATE_MEDIUM)
                    != 1) {

                $tag = $arbeitstag->tag;
                $feiertag = $arbeitstag->feiertag;
                $nachmittag = $arbeitstag->nachmittag;
                $beginn = null;
                $ende = null;
                $befreiung = null;
                $anwesend = null;
                $ist = null;
                $soll = null;
                $saldo = null;

                $datum = $feiertag['name'] . ' ' . $tag->toString('EE, dd.MM.yyyy');
                $pdfTag = $tag->toString('EE');
                $pdfDatum = $feiertag['name'] != '' ?
                        $feiertag['name'] . ' ' . $tag->toString('dd.MM.') :
                        $tag->toString('dd.MM.');
                if ($nachmittag) {
                    $datum .= ' Vormittag';
                    $pdfDatum .= ' Vormittag';
                    $anzahlHoheTage++;
                }

                if ($arbeitstag->beginn !== null) {
                    $beginn = $arbeitstag->beginn->toString('HH:mm');
                }
                if ($arbeitstag->ende !== null) {
                    $ende = $arbeitstag->ende->toString('HH:mm');
                }
                if ($arbeitstag->befreiung !== null) {
                    $befreiung = $befreiungOptionen[$arbeitstag->befreiung];
                }
                if ($arbeitstag->getRegel() !== null && !$nachmittag) {
                    $soll = $arbeitstag->regel->soll->toString('HH:mm');
                }

                $anwesend = $arbeitstag->getAnwesend();
                $ist = $arbeitstag->getIst();
                $saldoErg = $arbeitstag->getSaldo();
                if ($anwesend !== null && !$nachmittag) {
                    $anwesend = $anwesend->toString('HH:mm');
                    $ist = $ist->toString('HH:mm');
                    $saldo = $saldoErg->getString();
                } else {
                    $anwesend = null;
                    $ist = null;
                    $saldo = null;
                    if ($arbeitstag->befreiung == 'fa') {
                        $saldo = $saldoErg->getString();
                    }
                }

                $tabellenDaten->addItem(array(
                    'datum' => $datum,
                    'pdfTag' => $pdfTag,
                    'pdfDatum' => $pdfDatum,
                    'tag' => $tag->toString('dd'),
                    'feiertag' => $feiertag['feiertag'],
                    'beginn' => $beginn,
                    'ende' => $ende,
                    'befreiung' => $befreiung,
                    'bemerkung' => $arbeitstag->bemerkung,
                    'pause' => $arbeitstag->pause,
                    'anwesend' => $anwesend,
                    'ist' => $ist,
                    'soll' => $soll,
                    'saldo' => $saldo,
                ));

                //Neujahr und Karfreitag passen in eine Zeile mit dem Wochentag,
                //sind also keine 'hohen' Tage.
                if ($feiertag['name'] != '') {
                    if ($feiertag['name'] != 'Neujahr' &&
                            $feiertag['name'] != 'Karfreitag' && $feiertag['name'] != 'Weihnachten') {
                        $anzahlHoheTage++;
                        // 'Tag der offenen Tür braucht zwei Zeilen'
                        if ($feiertag['name'] == 'Tag der offenen Tür') {
                            $anzahlHoheTage++;
                        }
                    }
                }
                if ($nachmittag) {
                    // füge die Zeile für den Nachmittag hinzu
                    $datum = $feiertag['name'] . ' ' .
                            $tag->toString('EE, dd.MM.yyyy') . ' Nachmittag';
                    $pdfTag = $tag->toString('EE');
                    $pdfDatum = $feiertag['name'] != '' ?
                            $feiertag['name'] . ' ' . $tag->toString('dd.MM.') :
                            $tag->toString('dd.MM.');
                    $pdfDatum .= ' Nachmittag';
                    $anzahlHoheTage++;

                    $beginn = null;
                    $ende = null;
                    $befreiung = null;
                    $anwesend = null;
                    $ist = null;
                    $soll = null;
                    $saldo = null;

                    if ($arbeitstag->getBeginnNachmittag() !== null) {
                        $beginn = $arbeitstag->getBeginnNachmittag()->toString('HH:mm');
                    }
                    if ($arbeitstag->getEndeNachmittag() !== null) {
                        $ende = $arbeitstag->getEndeNachmittag()->toString('HH:mm');
                    }
                    if ($arbeitstag->getRegel() !== null) {
                        $soll = $arbeitstag->regel->soll->toString('HH:mm');
                    }
                    $anwesend = $arbeitstag->getAnwesend();
                    $ist = $arbeitstag->getIst();
                    $saldoErg = $arbeitstag->getSaldo();
                    if ($anwesend !== null) {
                        $anwesend = $anwesend->toString('HH:mm');
                        $ist = $ist->toString('HH:mm');
                        $saldo = $saldoErg->getString();
                    }
                    $tabellenDaten->addItem(array(
                        'datum' => $datum,
                        'pdfTag' => $pdfTag,
                        'pdfDatum' => $pdfDatum,
                        'tag' => $tag->toString('dd'),
                        'feiertag' => $feiertag['feiertag'],
                        'beginn' => $beginn,
                        'ende' => $ende,
                        'befreiung' => $befreiung,
                        'bemerkung' => null,
                        'pause' => $arbeitstag->pause,
                        'anwesend' => $anwesend,
                        'ist' => $ist,
                        'soll' => $soll,
                        'saldo' => $saldo,
                    ));
                    $extraZeilen++;
                }
            }
        }

        return array(
            'tabellenDaten' => $tabellenDaten,
            'hoheTage' => $anzahlHoheTage,
            'extraZeilen' => $extraZeilen,
        );
    }

}
