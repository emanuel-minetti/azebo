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
 * Description of LoginController
 *
 * @author Emanuel Minetti
 */
class LoginController extends AzeboLib_Controller_Abstract {

    protected $_model;
    protected $_authService;

    public function init() {
        parent::init();
        $this->_model = new Azebo_Model_Mitarbeiter();
        $this->_authService = new Azebo_Service_Authentifizierung();

        $this->view->loginForm = $this->getLoginForm();
    }
    
    public function getSeitenName() {
        return 'Login';
    }

    public function loginAction() {
        $request = $this->getRequest();
        $form = $this->_forms['login'];

        //TODO Logging überarbeiten!
        if ($request->isPost()) {
            if (!$form->isValid($request->getPost())) {
                $this->_log->info("Anmeldung fehlgeschlagen: Validation gescheitert! {$form->getValues()}");
                $form->setDescription(
                        'Anmeldung fehlgeschlagen! Bitte beachten Sie die Fehlermedungen:');
                return $this->render('login');             
            }
            
            $ergebnis = $this->_authService->authentifiziere($form->getValues());

            if ($ergebnis === 'FehlerLDAP') {
                 $this->_log->info('Anmeldung fehlgeschlagen:' . print_r($form->getValues(), true));
                $form->setDescription(
                        'Anmeldung fehlgeschlagen! Bitte versuchen Sie es erneut.');
                return $this->render('login');
            } else if ($ergebnis === 'FehlerDB') {
                $this->_log->info('Anmeldung fehlgeschlagen: ' . print_r($form->getValues(), true));
                $form->setDescription(
                        'Sie sind noch nicht für den Arbeitszeitbogen' . 
                        ' registriert! Bitte informieren Sie Ihre Büroleitung.');
                return $this->render('login');
                
            } else if ($ergebnis === 'Erfolg') {
                $this->_log->debug('Anmeldung erfolgreich: '  . print_r($form->getValues(), true));
            }

            return $this->_helper->redirector->gotoSimple('index', 'index', 'default');
        } else {
            $form->setDescription('');
        }
    }
    
     public function logoutAction() {
         $this->_authService->clear();
         $ns = new Zend_Session_Namespace();
         $ns->unsetAll();
     }

    public function getLoginForm() {
        $urlHelper = $this->_helper->getHelper('url');

        $this->_forms['login'] = $this->_model->getForm('mitarbeiterLogin');
        $this->_forms['login']->setAction($urlHelper->url(array(
                    'controller' => 'login',
                    'action' => 'login',
                        ), 'default'
                ));
        $this->_forms['login']->setMethod('post');
        $this->_forms['login']->setName('loginForm');

        return $this->_forms['login'];
    }

}

