<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Entity\Email;
use Adena\MailBundle\Form\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmailController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Home:index.html.twig');
    }

    public function viewAction(Email $email){
        return $this->render('AdenaMailBundle:Email:view.html.twig', [
            'email' => $email
        ]);
    }

    public function listAction(){
        $emails = $this->getDoctrine()->getRepository('AdenaMailBundle:Email')->findAll();

        return $this->render('AdenaMailBundle:Email:list.html.twig', [
            'emails' => $emails
        ]);
    }

    public function deleteAction(Request $request, Email $email){
        // CSRF protection
        $form = $this->get('form.factory')->create();

        // Form has been submitted
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->remove($email);
            $em->flush();

            $this->addFlash('success', "Email successfully deleted.");

            return $this->redirectToRoute('adena_mail_email_list');
        }

        return $this->render('AdenaMailBundle:Email:delete.html.twig', [
            'email'=>$email,
            'form'=>$form->createView()
        ]);
    }

    public function addAction(Request $request){
        $email = new Email();
        $form = $this->createForm(EmailType::class, $email);

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

    public function editAction(Request $request, Email $email){
        $form = $this->createForm(EmailType::class, $email);

        // Check if the form is valid
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Email successfully modified');

            return $this->redirectToRoute('adena_mail_email_view', [
                'id' => $email->getId()
            ]);
        }

        return $this->render('AdenaMailBundle:Email:edit.html.twig', [
            'form' => $form->createView(),
            'email' => $email
        ]);
    }
}
