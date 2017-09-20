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
 * Description of MitarbeiterTabelle
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_MitarbeiterTabelle {

    /**
     * Befüllt ein Zend_Dojo_Data-Objekt mit den Mitarbeiternamen und den
     * zugehörigen Daten. Der Parameter $monat ist entweder 'null', dann wird
     * die Mitarbeiter-Tabelle befüllt, oder ein Zend_Date-Objekt, dann wird die
     * Monatsdetail-Tabelle befüllt.
     * Der Parameter $mitarbeiter wird benötigt, um die Hochschule, deren
     * Mitarbeiter angezeigt werden sollen, zu ermitteln. 
     * 
     * 
     * Zurückgegeben wird ein Array mit den Schlüsseln 'daten' für die 
     * Tabellendaten und 'zeilen' für die Anzahl der Zeilen.
     * 
     * @param Azebo_Resources_Mitarbeiter_Inerface $mitarbeiter
     * @param Zend_Date|null $monat
     * @return array
     */
    public function _getMitarbeiterTabellenDaten($mitarbeiter, $monat) {
        // intialisiere das Datenarray und die Tabellendaten
        $mitarbeiterDatenArray = array();
        $mitarbeiterDaten = new Zend_Dojo_Data();
        $mitarbeiterDaten->setIdentifier('mitarbeiter');
        $zeilen = 0;

        // hole die Mitarbeiter der Hochschule
        $mitarbeiterModel = new Azebo_Model_Mitarbeiter();
        $hsMitarbeiter = $mitarbeiterModel->getMitarbeiterNachHochschule(
                $mitarbeiter->getHochschule());

        // füge die Mitarbeiter dem Array hinzu
        if ($monat instanceof Zend_Date) {
            foreach ($hsMitarbeiter as $mitarbeiter) {
                $arbeitsmonat = $mitarbeiter->getArbeitsmonat($monat);
                if ($arbeitsmonat === null) {
                    $abgeschlossen = 'Nein';
                    $abgelegt = 'Nein';
                } else {
                    $abgeschlossen = 'Ja';
                    $abgelegt = $arbeitsmonat->abgelegt == 'ja' ? 'Ja' : 'Nein';
                }
                $mitarbeiterDatenArray[] = array(
                    'mitarbeiter' => $mitarbeiter->benutzername,
                    'mitarbeitername' => $mitarbeiter->getSortierName(),
                    'abgeschlossen' => $abgeschlossen,
                    'abgelegt' => $abgelegt,
                );
                $zeilen++;
            }
        } else {
            foreach ($hsMitarbeiter as $mitarbeiter) {
                $mitarbeiterDatenArray[] = array(
                    'mitarbeiter' => $mitarbeiter->benutzername,
                    'mitarbeitername' => $mitarbeiter->getSortierName(),
                    'abgeschlossen' => $mitarbeiter->getAbgeschlossenBis(),
                    'abgelegt' => $mitarbeiter->getAbgelegtBis(),
                );
                $zeilen++;
            }
        }

        // $mitarbeiterDatenArray nach Nach- und Vornamen sortieren!
        $namen = array();
        foreach ($mitarbeiterDatenArray as $key => $row) {
            $namen[$key] = $row['mitarbeitername'];
        }
        array_multisort($namen, SORT_ASC, $mitarbeiterDatenArray);

        // die Arraydaten den Tabellendaten hinzufügen
        foreach ($mitarbeiterDatenArray as $mitarbeiter) {
            $mitarbeiterDaten->addItem($mitarbeiter);
        }

        return array(
            'daten' => $mitarbeiterDaten,
            'zeilen' => $zeilen,
        );
    }

}

