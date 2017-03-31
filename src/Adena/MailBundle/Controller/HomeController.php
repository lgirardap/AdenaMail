<?php

namespace Adena\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Home:index.html.twig');
    }
}
