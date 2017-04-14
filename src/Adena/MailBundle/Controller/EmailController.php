<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Entity\Email;
use Adena\MailBundle\Form\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EmailController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Home:index.html.twig');
    }

    public function viewAction(){

    }

    public function listAction(){

    }

    public function addAction(Request $request){
        $email = new Email();
        $form = $this->createForm(EmailType::class, $email);
        $this->addFlash('success', 'Email successfully added');
        // Check if the form is valid
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->persist($email);
            $em->flush();

            $this->addFlash('success', 'Email successfully added');

            return $this->redirectToRoute('adena_mail_email_view', [
                'id' => $email->getId()
            ]);
        }

        return $this->render('AdenaMailBundle:Email:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
