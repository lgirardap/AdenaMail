<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Entity\Sender;
use Adena\MailBundle\Form\SenderType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SenderController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Sender:index.html.twig');
    }

    public function addAction( Request $request )
    {
        $sender = new Sender();
        $form = $this->get('form.factory')->create(SenderType::class, $sender);

        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist( $sender );
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'New sender created.');

            return $this->redirectToRoute('adena_mail_sender_add');

        }

        return $this->render('AdenaMailBundle:Sender:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
