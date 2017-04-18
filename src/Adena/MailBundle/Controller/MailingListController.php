<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Form\MailingListEditType;
use Adena\MailBundle\Form\MailingListType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Adena\MailBundle\Entity\MailingList;

class MailingListController extends Controller
{
    public function viewAction(MailingList $mailingList){
        return $this->render('AdenaMailBundle:MailingList:view.html.twig', [
            'mailingList' => $mailingList
        ]);
    }

    public function listAction(){
        $mailingLists = $this->getDoctrine()->getRepository('AdenaMailBundle:MailingList')->findAll();

        return $this->render('AdenaMailBundle:MailingList:list.html.twig', [
            'mailingLists' => $mailingLists
        ]);
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

            return $this->redirectToRoute('adena_mail_mailing_list_view', [
                'id' => $mailingList->getId()
            ]);
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

            return $this->redirectToRoute('adena_mail_mailing_list_view', [
                'id' => $mailingList->getId()
            ]);
        }

        return $this->render('AdenaMailBundle:MailingList:edit.html.twig', [
            'form' => $form->createView(),
            'mailingList' => $mailingList
        ]);
    }
}
