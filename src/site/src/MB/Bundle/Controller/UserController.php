<?php

namespace MB\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    public function loginAction()
    {
        return $this->render('MBBundle:Default:login.html.twig', array('name'=>'STATIC'));
    }





}
