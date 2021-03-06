<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Form\MailingListEditType;
use Adena\MailBundle\Form\MailingListType;
use Symfony\Component\HttpFoundation\Request;
use Adena\MailBundle\Entity\MailingList;

class MailingListController extends CoreController
{
    public function viewAction(MailingList $mailingList){
        return $this->render('AdenaMailBundle:MailingList:view.html.twig', [
            'mailingList' => $mailingList
        ]);
    }

    public function listAction($page){
        try {
            $query = $this->getDoctrine()->getManager()->getRepository('AdenaMailBundle:MailingList')->getMailingListsQuery();

            $mailingLists = $this->get('adena_paginator.paginator.paginator')->paginate($query, $page);

            return $this->render('AdenaMailBundle:MailingList:list.html.twig', array(
                'mailingLists' => $mailingLists
            ));
        }catch(\InvalidArgumentException $e){
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    public function testAction(MailingList $mailingList){
        $mailingTester = $this->get('adena_mail.entity_helper.mailing_list_tester');

        if(!$mailingTester->test($mailingList)) {
            $this->addFlash('danger', 'Error in the query: '.$mailingTester->getErrors());
        }else {
            $this->addFlash('success', 'Cool query bro');
        }

        return $this->redirectToReferer();
    }

    public function deleteAction(Request $request, MailingList $mailingList){
        // CSRF protection
        $form = $this->get('form.factory')->create();

        // Form has been submitted
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->remove($mailingList);
            $em->flush();

            $this->addFlash('success', "MailingList successfully deleted.");

            return $this->redirectToRoute('adena_mail_mailing_list_list');
        }

        return $this->render('AdenaMailBundle:MailingList:delete.html.twig', [
            'mailingList'=>$mailingList,
            'form'=>$form->createView()
        ]);
    }

    public function chooseAddAction()
    {
        return $this->render('AdenaMailBundle:MailingList:choose_add.html.twig');
    }
    
    public function addAction(Request $request, $type ){
        // We rely on setType to check if the type provided in the route is valid
        $mailingList = new MailingList();
        $mailingList->setType($type);

        $form = $this->createForm(MailingListType::class, $mailingList);

        // Check if the form is valid
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->persist($mailingList);
            $em->flush();

            $this->addFlash('success', 'MailingList successfully added');

            $redirectUrl = $this->generateUrl('adena_mail_mailing_list_view', [
                'id' => $mailingList->getId()
            ]);

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:MailingList:add_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:MailingList:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    public function editAction(Request $request, MailingList $mailingList){
        $form = $this->createForm(MailingListEditType::class, $mailingList);

        // Check if the form is valid
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'MailingList successfully modified');

            $redirectUrl = $this->generateUrl('adena_mail_mailing_list_view', [
                'id' => $mailingList->getId()
            ]);

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:MailingList:edit_form.html.twig', [
                    'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:MailingList:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
