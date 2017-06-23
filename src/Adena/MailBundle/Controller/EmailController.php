<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Email;
use Adena\MailBundle\Form\EmailType;
use Symfony\Component\HttpFoundation\Request;

class EmailController extends CoreController
{
    public function viewAction(Email $email){
        return $this->render('AdenaMailBundle:Email:view.html.twig', [
            'email' => $email
        ]);
    }

    public function listAction($page){

        try {
            $query = $this->getDoctrine()->getManager()->getRepository('AdenaMailBundle:Email')->getEmailsQuery();

            $emails = $this->get('adena_paginator.paginator.paginator')->paginate($query, $page);

            return $this->render('AdenaMailBundle:Email:list.html.twig', array(
                'emails'        => $emails
            ));
        }catch(\InvalidArgumentException $e){
            throw $this->createNotFoundException($e->getMessage());
        }

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

            $redirectUrl = $this->generateUrl('adena_mail_email_view', [
                'id' => $email->getId()
            ]);

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Email:add_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
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

            $redirectUrl = $this->generateUrl('adena_mail_email_view', [
                'id' => $email->getId()
            ]);

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Email:edit_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Email:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
