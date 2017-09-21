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
 * Eine Hilfsklasse um Arbeitszeitsalden zu berechnen.
 *
 * @author Emanuel Minetti
 */
class Azebo_Model_Saldo {

    private $_stunden;
    private $_minuten;
    private $_positiv;
    private $_rest2007;
    private $_restStunden;
    private $_restMinuten;

    function __construct($stunden, $minuten, $positiv, $rest = false, $restStunden = 0, $restMinuten = 0) {
        $this->_stunden = $stunden;
        $this->_minuten = $minuten;
        $this->_positiv = $positiv;
        $this->_rest2007 = $rest;
        $this->_restStunden = $restStunden;
        $this->_restMinuten = $restMinuten;
    }

    public static function copy(Azebo_Model_Saldo $saldo) {
        $copy = new Azebo_Model_Saldo(0, 0, true);
        $copy->_stunden = $saldo->getStunden();
        $copy->_minuten = $saldo->getMinuten();
        $copy->_positiv = $saldo->getPositiv();
        $copy->_rest2007 = $saldo->getRest();
        $copy->_restStunden = $saldo->getRestStunden();
        $copy->_restMinuten = $saldo->getRestMinuten();

        return $copy;
    }
    
    /**
     * Addiert $saldo zu $this. Falls $monat == true ist, Wird auch die
     * 2007er-Regelung der HfM brücksichtigt. Gibt $this zurück für
     * zusammengesetzte Anweisungen.
     * 
     * @param Azebo_Model_Saldo $saldo
     * @param type $monat
     * @return \Azebo_Model_Saldo 
     */
    public function add(Azebo_Model_Saldo $saldo, $monat = false) {

        $stunden = $saldo->getStunden();
        $minuten = $saldo->getMinuten();
        $positiv = $saldo->getPositiv();

        if (($this->_positiv && $positiv) || (!$this->_positiv && !$positiv)) {
            // gleiches Vorzeichen
            $this->_minuten += $minuten;
            if ($this->_minuten >= 60) {
                $this->_minuten -= 60;
                $this->_stunden++;
            }
            $this->_stunden += $stunden;
        } else {
            // ungleiches Vorzeichen
            // finde größeren Wert
            if ($this->_stunden > $stunden) {
                $dieserIstGroesser = true;
            } elseif ($this->_stunden < $stunden) {
                $dieserIstGroesser = false;
            } else {
                $dieserIstGroesser = $this->_minuten >= $minuten;
            }

            if ($dieserIstGroesser) {
                if ($this->_minuten >= $minuten) {
                    $this->_minuten -= $minuten;
                } else {
                    $this->_minuten = $this->_minuten - $minuten + 60;
                    $this->_stunden--;
                }
                $this->_stunden -= $stunden;
            } else {
                // der andere ist größer
                if ($this->_minuten <= $minuten) {
                    $this->_minuten = $minuten - $this->_minuten;
                } else {
                    $this->_minuten = $minuten - $this->_minuten + 60;
                    $this->_stunden++;
                }
                $this->_stunden = $stunden - $this->_stunden;
                $this->_positiv = $positiv;
            }
        }

        if ($this->_minuten == 0 && $this->_stunden == 0) {
            $this->_positiv = true;
        }

        //2007-er Regelung für die HfM
        if ($monat && !$this->_positiv && $this->_rest2007) {
            if ($this->_restStunden > $this->_stunden) {
                $restIstGroesser = true;
            } elseif ($this->_restStunden < $this->_stunden) {
                $restIstGroesser = false;
            } else {
                $restIstGroesser = $this->_restMinuten >= $this->_minuten;
            }

            if ($restIstGroesser) {
                if ($this->_restMinuten >= $this->_minuten) {
                    $this->_restMinuten -= $this->_minuten;
                } else {
                    $this->_restMinuten = $this->_restMinuten - $this->_minuten + 60;
                    $this->_restStunden--;
                }
                $this->_restStunden -= $this->_stunden;
                $this->_minuten = 0;
                $this->_stunden = 0;
                $this->_positiv = true;
                if ($this->_restStunden == 0 && $this->_restMinuten == 0) {
                    $this->_restStunden = null;
                    $this->_restMinuten = null;
                    $this->_rest2007 = false;
                }
            } else {
                // restIstGroesser == false
                if ($this->_minuten >= $this->_restMinuten) {
                    $this->_minuten -= $this->_restMinuten;
                } else {
                    $this->_minuten = $this->_minuten - $this->_restMinuten + 60;
                    $this->_stunden--;
                }
                $this->_stunden -= $this->_restStunden;
                $this->_restStunden = null;
                $this->_restMinuten = null;
                $this->_rest2007 = false;
            }
        } //if ($monat && !$this->_positiv && $this->_rest2007)

        return $this;
    }

    /**
     * Gibt -1, 0 oder 1 zurück. Ist $this kleiner als $saldo wird -1
     * zurückgegeben, ist es größer 1 und ist es gleich 0.
     * 
     * @param Azebo_Model_Saldo $saldo
     * @return int 
     */
    public function vergleiche(Azebo_Model_Saldo $saldo) {

        if ($this->_stunden < $saldo->getStunden()) {
            return -1;
        } elseif ($this->_stunden > $saldo->getStunden()) {
            return 1;
        } else {
            // also: $this->_stunden == $saldo->getStunden()
            if ($this->_minuten < $saldo->getMinuten()) {
                return -1;
            } elseif ($this->_minuten > $saldo->getMinuten()) {
                return 1;
            } else {
                // also: $this->_minuten == $saldo->getMinuten()
                return 0;
            }
        }
    }

    public function getStunden() {
        return $this->_stunden;
    }

    public function getMinuten() {
        return $this->_minuten;
    }

    public function getPositiv() {
        return $this->_positiv;
    }

    public function getRest() {
        return $this->_rest2007;
    }

    public function getRestStunden() {
        return $this->_restStunden;
    }

    public function getRestMinuten() {
        return $this->_restMinuten;
    }

    public function getString() {
        if ($this->_stunden === null) {
            return '+ 0:00';
        } else {
            $saldoString = $this->_positiv == true ? '+ ' : '- ';
            $saldoString .= $this->_stunden . ':';
            if ($this->_minuten <= 9) {
                $saldoString .= '0' . $this->_minuten;
            } else {
                $saldoString .= $this->_minuten;
            }
            return $saldoString;
        }
    }

    public function getRestString() {
        if ($this->_restStunden === null || !$this->_rest2007) {
            return '+ 0:00';
        } else {
            $saldoString = '+' . $this->_restStunden . ':';
            if ($this->_restMinuten <= 9) {
                $saldoString .= '0' . $this->_restMinuten;
            } else {
                $saldoString .= $this->_restMinuten;
            }
        }
        return $saldoString;
    }

    public function getStringOhneVorzeichen() {
        if ($this->_stunden === null) {
            return '0:00';
        } else {
            $saldoString = $this->_stunden . ':';
            if ($this->_minuten <= 9) {
                $saldoString .= '0' . $this->_minuten;
            } else {
                $saldoString .= $this->_minuten;
            }
            return $saldoString;
        }
    }

}
