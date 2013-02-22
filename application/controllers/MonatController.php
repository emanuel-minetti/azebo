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

class MonatController extends AzeboLib_Controller_Abstract {

    public $tag;
    public $monat;
    public $jahr;
    public $tageImMonat;

    /**
     * @var Azebo_Service_Zeitrechner
     */
    public $zeitrechner;

    /**
     * @var Zend_Session_Namespace
     */
    public $ns;

    /**
     * @var Zend_Date 
     */
    public $zuBearbeitendesDatum;

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_Interface 
     */
    public $mitarbeiter;

    /**
     * @var boolean
     */
    public $bearbeitbar;

    public function init() {
        parent::init();

        $heute = new Zend_Date();

        // Hohle die Parameter
        $this->tag = $this->_getParam('tag', $heute->get(Zend_Date::DAY));
        $this->monat = $this->_getParam('monat', $heute->get(Zend_Date::MONTH));
        $this->jahr = $this->_getParam('jahr', $heute->get(Zend_Date::YEAR));

        $this->view->tag = $this->tag;
        $this->view->monat = $this->monat;
        $this->view->jahr = $this->jahr;

        // Setze das Datum
        $this->zuBearbeitendesDatum = new Zend_Date(array(
                    'day' => $this->tag,
                    'month' => $this->monat,
                    'year' => $this->jahr,
                ));

        // Ermittle die Anzahl der Tage des Monats
        $this->tageImMonat = $this->zuBearbeitendesDatum
                ->get(Zend_Date::MONTH_DAYS);
        $this->view->tageImMonat = $this->tageImMonat;

        // Initialisiere den Feiertagsservice mit dem zu bearbeitendem Jahr
        // und übergebe den Service an die Session. Arbeitstag_Item greift
        // darauf zu.
        $feiertagsservice = new Azebo_Service_Feiertag($this->jahr);
        $ns = new Zend_Session_Namespace();
        $ns->feiertagsservice = $feiertagsservice;
        $this->ns = $ns;

        // Aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect')
                ->requireModule('dijit.Tooltip');

        // Lade den Mitarbeiter
        $this->mitarbeiter = $ns->mitarbeiter;

        // Stelle den Zeitrechner-Service zur Verfügung
        $this->zeitrechner = new Azebo_Service_Zeitrechner();

        //Salden setzen
        $this->saldoBisher = $this->mitarbeiter->getSaldoBisher(
                $this->zuBearbeitendesDatum);
        $this->view->saldoBisher = $this->saldoBisher->getString();
        $this->saldo = $this->mitarbeiter->getSaldo(
                $this->zuBearbeitendesDatum, true);
        $this->view->saldo = $this->saldo->getString();
        $this->saldoGesamt = Azebo_Model_Saldo::copy($this->saldoBisher);
        $this->saldoGesamt->add($this->saldo, true);
        $this->view->saldoGesamt = $this->saldoGesamt->getString();
        if ($this->mitarbeiter->getHochschule() == 'hfm' &&
                $this->saldoBisher->getRest()) {
            $this->view->hatRest = true;
            $this->view->saldoBisher2007 = $this->saldoBisher->getRestString();
            $this->view->saldoGesamt2007 = $this->saldoGesamt->getRestString();
        }
        $this->view->urlaubBisher = $this->mitarbeiter->getUrlaubBisher();
        $this->view->urlaub = $this->mitarbeiter->getUrlaubNachMonat(
                $this->zuBearbeitendesDatum);

        //prüfe ob bereits abgeschlossen
        $this->bearbeitbar = true;
        $arbeitmonate = $this->mitarbeiter->getArbeitsmonate();
        foreach ($arbeitmonate as $arbeitsmonat) {
            if ($this->zuBearbeitendesDatum->compareMonth(
                            $arbeitsmonat->getMonat()) == 0) {
                $this->bearbeitbar = false;
                break;
            }
        }
        $this->view->bearbeitbar = $this->bearbeitbar;

        //übergebe dem View die Hochschule
        $this->view->hochschule = $this->mitarbeiter->getHochschule();
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function druckAction() {
        $this->_erzeugePDF();
    }

    public function indexAction() {
        $request = $this->getRequest();
        $abschlussForm = $this->_getMitarbeiterAbschlussForm();
        if ($request->isPost()) {
            // ist Post-Request, also prüfen ob 'prüfen' gedrückt wurde
            $postDaten = $request->getPost();
            if (isset($postDaten['pruefen'])) {
                $valid = $abschlussForm->isValid($postDaten);
                if ($valid) {
                    $daten = $abschlussForm->getValues();
                    $monat = new Zend_Date($daten['monat'], 'MM.yyyy');
                    $this->view->saldo =
                            $this->mitarbeiter->getSaldo($monat)->getString();
                    // markiere den Monat in der Session als geprüft
                    $ns = new Zend_Session_Namespace();
                    $ns->geprueft[$monat->toString('MM-yyyy')] = true;
                    //TODO Mehr als 10 Defizitstunden
                    // lade die Form neu, um den richtigen Button anzuzeigen
                    $abschlussForm = $this->_getMitarbeiterAbschlussForm();
                }
            } elseif (isset($postDaten['abschliessen'])) {
                // lege den Monat in der DB ab, falls er noch nicht vorhanden
                // ist (= möglich bei 'Resend')
                $valid = $abschlussForm->isValid($postDaten);
                if ($valid && $this->mitarbeiter->getArbeitsmonat($this->zuBearbeitendesDatum) === null) {
                    $daten = $abschlussForm->getValues();
                    $monat = new Zend_Date($daten['monat'], 'MM.yyyy');
                    $saldo = $this->mitarbeiter->getSaldo($monat);
                    $urlaub = $this->mitarbeiter->getUrlaubNachMonat($monat);
                    $this->view->saldo = $saldo->getString();
                    $this->mitarbeiter->saveArbeitsmonat(
                            $monat, $saldo, $urlaub);
                    $this->bearbeitbar = false;

                    // aktualisiere den View
                    $this->view->bearbeitbar = false;
                    $this->view->urlaubBisher = $this->mitarbeiter->
                            getUrlaubBisher();
                    $abschlussForm = $this->_getMitarbeiterAbschlussForm();
                }
            }
        }

        $datum = new Zend_Date($this->zuBearbeitendesDatum);

        // setze den Seitennamen
        $this->erweitereSeitenName($datum->toString(' MMMM yyyy'));

        // befülle die Reihen der Tabelle
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);
        $erster->setDay(1);
        $letzter->setDay($this->tageImMonat);
        $tabelle = $this->_helper->
                MonatsTabelle($erster, $letzter, $this->mitarbeiter);

        // füge die Tabelle dem View hinzu
        $this->view->monatsDaten = $tabelle['tabellenDaten'];
        $this->view->hoheTageImMonat = $tabelle['hoheTage'];
        $this->view->extraZeilen = $tabelle['extraZeilen'];

        // die Form für den Monatsabschluss hinzufügen
        $this->view->monatForm = $abschlussForm;
    }

    public function editAction() {
        $request = $this->getRequest();

        // falls der Monat nicht bearbeitbar ist, gibt es keinen Link hierher.
        // Der User versucht etwas Böses!
        if (!$this->bearbeitbar) {
            $errors = new ArrayObject();
            $errors->type =
                    Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $errors->request = $request;
            $errors->exception = new AzeboLib_Exception(
                            'Auf diese Seite haben Sie keinen Zugriff!',
                            null, null);
            $request->setParam('error_handler', $errors);
            $this->_forward('nichterlaubt', 'error');
        }


        $form = $this->_getMitarbeiterTagForm();
        $form->setNachmittag();

        if ($request->isPost()) {
            $postDaten = $request->getPost();

            if (!isset($postDaten['zuruecksetzen'])) {
                // bevölkere das Beginn- und Ende-Element
                $filter = new Azebo_Filter_ZeitAlsDate();
                $form->setBeginn($filter->filter($postDaten['beginn']));
                $form->setEnde($filter->filter($postDaten['ende']));
                if (!isset($postDaten['nachmittagButton']) &&
                        $postDaten['nachmittag']) {
                    $form->setBeginn($filter->filter($postDaten['beginnnachmittag']), true);
                    $form->setEnde($filter->filter($postDaten['endenachmittag']), true);
                }

                if (isset($postDaten['absenden'])) {
                    // 'absenden' wurde gedrückt, also Daten filtern und validieren!
                    $valid = $form->isValid($postDaten);
                    $daten = $form->getValues();

                    if ($valid) {
                        // für HfM die Pause setzen
                        if ($this->mitarbeiter->getHochschule() == 'hfm') {
                            //TODO Nachmittag
                            if ($daten['beginn'] !== null && $daten['beginn'] != '' &&
                                    $daten['ende'] !== null && $daten['ende'] != '') {
                                $anwesend = $this->zeitrechner->
                                        anwesend($daten['beginn'], $daten['ende']);
                                $pause = $this->ns->zeiten->pause;
                                if ($anwesend->compareTime($pause->kurz->ab) != 1) {
                                    $daten['pause'] = 'x';
                                } else {
                                    $daten['pause'] = '-';
                                }
                                $this->_log->debug('Anwesend: ' . $anwesend);
                            }
                        }
                        $this->_log->debug('Pause: ' . $daten['pause']);

                        // speichen, in der Session als ungeprüft
                        // markieren und redirect
                        $this->mitarbeiter->saveArbeitstag(
                                $this->zuBearbeitendesDatum, $daten);
                        $ns = new Zend_Session_Namespace();
                        $ns->geprueft[
                                $this->zuBearbeitendesDatum->toString('MM-yyyy')] =
                                false;
                        $redirector = $this->_helper->getHelper('Redirector');
                        $redirector->gotoRoute(array(
                            'jahr' => $this->jahr,
                            'monat' => $this->monat,
                                ), 'monat');
                    }
                    // nicht valide, also tue nichts und rendere die Seite mit
                    // Fehlermeldungen neu.
                } elseif (isset($postDaten['nachmittagButton'])) {
                    // Nachmittag wurde gedrückt, also
                    // schalte das DB-Feld um und passe die Form an
                    $this->mitarbeiter->
                            getArbeitstagNachTag($this->zuBearbeitendesDatum)->
                            toggleNachmittag();
                    $form->setNachmittag();
                }
            }
            // 'zurücksetzen' wurde gedrückt, also tue nichts sondern, rendere
            // einfach die Seite neu
        }

        // rendere die Seite
        $this->view->tagForm = $form;
        // setze den Seitennamen
        $this->erweitereSeitenName(' - Bearbeite ');
        $this->erweitereSeitenName($this->zuBearbeitendesDatum
                        ->toString('d.M.yy'));

        // initialisiere zwei Daten
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);

        // befülle die obere Tabelle
        if ($this->tag != 1) {
            $erster->setDay(1);
            $letzter->setDay($this->tag - 1);
            $tabelle = $this->_helper->
                    MonatsTabelle($erster, $letzter, $this->mitarbeiter);
            $this->view->monatsDatenOben = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatOben = $tabelle['hoheTage'];
            $this->view->extraZeilenOben = $tabelle['extraZeilen'];
        } else {
            $this->view->monatsDatenOben = null;
            $this->view->hoheTageImMonatOben = 0;
            $this->view->extraZeilenOben = 0;
        }

        //befülle die untere Tabelle
        if ($this->tag != $this->tageImMonat) {
            $erster->setDay($this->tag + 1);
            $letzter->setDay($this->tageImMonat);
            $tabelle = $this->_helper->
                    MonatsTabelle($erster, $letzter, $this->mitarbeiter);
            $this->view->monatsDatenUnten = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatUnten = $tabelle['hoheTage'];
            $this->view->extraZeilenUnten = $tabelle['extraZeilen'];
        } else {
            $this->view->monatsDatenUnten = null;
            $this->view->hoheTageImMonatUnten = 0;
            $this->view->extraZeilenUnten = 0;
        }
    }

    private function _getMitarbeiterTagForm() {
        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterTag');
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'tag' => $this->tag,
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monatEdit', true);
        $url .= '#form';
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('tagForm');
        return $form;
    }

    private function _getMitarbeiterAbschlussForm() {
        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterAbschluss');
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monat', true);
        $url .= '#monatForm';
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('monatForm');

        // entferne die 'Prüfen'-, 'Abschließen'-, oder 'Ausdrucken'-Buttons,
        // je nachdem ob der Monat bereits geprüft bzw. abgeschlossen ist.
        $ns = new Zend_Session_Namespace();
        $geprueft = $ns->geprueft;
        $index = $this->zuBearbeitendesDatum->toString('MM-yyyy');
        if (!$this->bearbeitbar) {
            $form->removeElement('pruefen');
            $form->removeElement('abschliessen');

            $druckElement = $form->getElement('ausdrucken');
            $druckElement->setAttrib('onclick', 'drucke();');
        } elseif ($geprueft !== null && isset($geprueft[$index]) &&
                $geprueft[$index]) {
            $form->removeElement('ausdrucken');
            $form->removeElement('pruefen');
        } else {
            $form->removeElement('ausdrucken');
            $form->removeElement('abschliessen');
        }

        $monatElement = $form->getElement('monat');
        $monatElement->setValue($this->zuBearbeitendesDatum->toString('MM.yyyy'));
        return $form;
    }

    private function _erzeugePDF() {
        $pdf = new Azebo_Service_BogenPDF();
        $pdf->SetTitle('Arbeitszeitbogen');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        
        $pdf->Cell(95, 15, 'Arbeitszeiterfassung', 0, 0, 'L');
        $pdf->Cell(95, 15, $this->mitarbeiter->getHochschulString(), 0, 0, 'R');
        $pdf->Ln(10);
        $pdf->Cell(95, 15, $this->zuBearbeitendesDatum->toString('MMMM YYYY'), 0, 0, 'L');
        $pdf->Cell(95, 15, $this->mitarbeiter->getName(), 0, 0, 'R');
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200);
        $pdf->SetWidths(array(30, 14, 14, 30, 45, 14, 14, 14, 16));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->Row(array(
            'Datum',
            'Beginn',
            'Ende',
            'Befreiung',
            'Bemerkung',
            'Anwe-send',
            'Ist',
            'Soll',
            'Saldo',
                ), true);

        $pdf->SetFont('Times', '', 10);
        $pdf->SetAligns(array('C', 'C', 'C', 'L', 'L', 'C', 'C', 'C', 'L'));
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);
        $erster->setDay(1);
        $letzter->setDay($this->tageImMonat);
        $tabelle = $this->_helper->
                MonatsTabelle($erster, $letzter, $this->mitarbeiter);

        foreach ($tabelle['tabellenDaten'] as $row) {
            $fill = $row['feiertag'] == null ? false : true;
            $pdf->Row(array(
                $row['datum'],
                $row['beginn'],
                $row['ende'],
                $row['befreiung'],
                $row['bemerkung'],
                $row['anwesend'],
                $row['ist'],
                $row['soll'],
                $row['saldo']), $fill);
        }

        $pdf->Ln(6);

        $saldoString = $this->saldo->getString();
        if ($this->mitarbeiter->getHochschule() == 'hfm' &&
                $this->saldoBisher->getRest()) {
            $saldoBisherString = $this->saldoBisher->getString() .
                    '     (Rest 2007: ' . $this->saldoBisher->getRestString() .
                    ')';
            $saldoGesamtString = $this->saldoGesamt->getString() .
                    '     (Rest 2007: ' . $this->saldoGesamt->getRestString() .
                    ')';
        } else {
            $saldoBisherString = $this->saldoBisher->getString();
            $saldoGesamtString = $this->saldoGesamt->getString();
        }
        
        $pdf->MultiCell(0, 5, 'Saldo Vormonat: ' . $saldoBisherString, 0, 'L');
        $pdf->MultiCell(0, 5, 'Saldo dieses Monats: ' . $saldoString, 0, 'L');
        $pdf->MultiCell(0, 5, 'Saldo gesamt: ' . $saldoGesamtString, 0, 'L');
        $pdf->MultiCell(0, 5, 'Resturlaub bisher: ' . $this->mitarbeiter->getUrlaubBisher(), 0, 'L');
        $pdf->MultiCell(0, 5, 'Urlaubstage in diesem Monat: ' . $this->mitarbeiter->getUrlaubNachMonat($this->zuBearbeitendesDatum), 0, 'L');
        $pdf->Ln(8);
        
        $pdf->SetWidths(array(60, 60, 60));
        $pdf->SetAligns(array('L', 'C', 'R'));
        $pdf->Row(array("_____________________________\nUnterschrift Beschäftigte/r",
            '' , "_____________________________\n      Unterschrift Fachvorgesetzte/r"), false, false);

        $pdf->AutoPrint();

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="bogen.pdf"');

        $pdf->Output();
    }

}
