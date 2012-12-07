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
 * Eine Hilfsklasse um Arbeitszeitsalden zu berechnen.
 *
 * @author Emanuel Minetti
 */
class Azebo_Model_Saldo {

    private $_stunden;
    private $_minuten;
    private $_positiv;

    function __construct($stunden, $minuten, $positiv) {
        $this->_stunden = $stunden;
        $this->_minuten = $minuten;
        $this->_positiv = $positiv;
    }

    public function add(Azebo_Model_Saldo $saldo) {
        $stunden = $saldo->getStunden();
        $minuten = $saldo->getMinuten();
        $positiv = $saldo->getPositiv();

        if ($this->_positiv) {
            if ($positiv) {
                $this->_minuten += $minuten;
                if ($this->_minuten >= 60) {
                    $this->_minuten -= 60;
                    $this->_stunden++;
                }
                $this->_stunden += $stunden;
                if ($this->_stunden >= 100) {
                    $this->_stunden = 100;
                    $this->_minuten = 0;
                }
            } else {
                // zu addierendes Saldo negativ
                if ($this->_minuten >= $minuten) {
                    $this->_minuten -= $minuten;
                } else {
                    $this->_minuten = 60 - ($minuten - $this->_minuten);
                    if ($this->_stunden != 0) {
                        $this->_stunden--;
                    }
                }
                if ($this->_stunden >= $stunden) {
                    $this->_stunden -= $stunden;
                } else {
                    $this->_stunden = $stunden - $this->_stunden;
                    $this->_minuten = 60 - $this->_minuten;
                    $this->_positiv = false;
                }
            }
        } else {
            // $this negativ
            if (!$positiv) {
                // zu addierendes Saldo negativ
                $this->_minuten += $minuten;
                if ($this->_minuten >= 60) {
                    $this->_minuten -= 60;
                    $this->_stunden++;
                }
                $this->_stunden += $stunden;
            } else {
                // zu addierendes Saldo positiv
                if ($this->_minuten >= $minuten) {
                    $this->_minuten -= $minuten;
                } else {
                    $this->_minuten = 60 - ($minuten - $this->_minuten);
                    $this->_stunden--;
                }
                if ($this->_stunden >= $stunden) {
                    $this->_stunden -= $stunden;
                } else {
                    $this->_stunden = $stunden - $this->_stunden;
                    $this->_minuten = 60 - $this->_minuten;
                    $this->_positiv = true;
                }
            }
        }
        return $this;
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

}

