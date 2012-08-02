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
class LoginController extends Zend_Controller_Action {

    protected $_logger;
    protected $_model;
    protected $_authService;

    public function init() {
        $this->_logger = Zend_Registry::get('log');
        $this->_logger->info('LoginController ' . __METHOD__);

        $this->_model = new Azebo_Model_Mitarbeiter();
        $this->_authService = new Azebo_Service_Authentication();

        $this->view->loginForm = $this->getLoginForm();
    }

    public function loginAction() {
        $this->_logger->info('LoginController ' . __METHOD__);

        $request = $this->getRequest();
        $form = $this->_forms['login'];

        if ($request->isPost()) {
            if (!$form->isValid($request->getPost())) {
                return $this->render('login');
                $this->_logger->info("Anmeldung fehlgeschlagen: Validation gescheitert!");
            }

            if (!$this->_authService->authenticate($form->getValues())) {
                $form->setDescription(
                        'Anmeldung fehlgeschlagen! Bitte versuchen Sie es erneut!');
                return $this->render('login');
                $this->_logger->info("Anmeldung fehlgeschlagen: {$form->getValues()}");
            } else {
                $this->_logger->info("Anmeldung erfolgreich: {$form->getValues()}");
            }

            return $this->_helper->redirector->gotoSimple('index', 'index', 'default');
        } else {
            $form->setDescription('Bitte melden Sie sich mit Ihrem Benutzernamen und Passwort an.');
        }
    }
    
     public function logoutAction() {
         $this->_authService->clear();
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

        return $this->_forms['login'];
    }

}
