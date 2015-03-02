<?php

namespace MB\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MBBundle:Default:index.html.twig', array());
    }
}
