<?php

namespace Adena\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Default:index.html.twig');
    }
}
