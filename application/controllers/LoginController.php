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

    protected $_model;
    protected $_authService;

    public function init() {
        $this->_model = new Azebo_Model_Mitarbeiter();
        $this->_authService = new Azebo_Service_Authentication();

        $this->view->loginForm = $this->getLoginForm();
    }

    public function loginAction() {
        
    }

    public function authenticateAction() {
        $request = $this->getRequest();
        
        if(!$request->isPost()) {
            return $this->_helper->redirector('login');
        }
        
        $form = $this->_forms['login'];
        if(!$form->isValid($request->getPost())) {
            return $this->render('login');
        }
        
        //TODO warum 'false === ...'
        if(!$this->_authService->authenticate($form->getValues())) {
            $form->setDescription(
                    'Login fehlgeschlagen! Bitte versuchen Sie es erneut!');
            return $this->render('login');
        }
        
        return $this->_helper->redirector->gotoSimple('index','index','default');
    }

    public function getLoginForm() {
        $urlHelper = $this->_helper->getHelper('url');

        $this->_forms['login'] = $this->_model->getForm('mitarbeiterLogin');
        $this->_forms['login']->setAction($urlHelper->url(array(
                    'controller' => 'login',
                    'action' => 'authenticate',
                        ), 'default'
                ));
        $this->_forms['login']->setMethod('post');
        
        return $this->_forms['login'];
    }

}

