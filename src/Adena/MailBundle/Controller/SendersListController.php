<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Sender;
use Adena\MailBundle\Entity\SendersList;
use Adena\MailBundle\Form\SendersListType;
use Adena\MailBundle\Form\SenderType;
use Symfony\Component\HttpFoundation\Request;

class SendersListController extends CoreController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request )
    {
        $sendersList = new SendersList();
        $form = $this->get('form.factory')->create(SendersListType::class, $sendersList);

        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->persist( $sendersList );
            $em->flush();

            $this->addFlash('success', 'SendersList successfully added');

            $redirectUrl = $this->generateUrl('adena_mail_senders_list_list');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:SendersList:add_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:SendersList:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page){
        try {
            $query = $this->getDoctrine()->getManager()->getRepository('AdenaMailBundle:SendersList')->getSendersListsQuery();

            $sendersLists = $this->get('adena_paginator.paginator.paginator')->paginate($query, $page);

            return $this->render('AdenaMailBundle:SendersList:list.html.twig', array(
                'sendersLists' => $sendersLists
            ));
        }catch(\InvalidArgumentException $e){
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param SendersList $sendersList
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response     *
     */
    public function deleteAction(Request $request, SendersList $sendersList)
    {
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($sendersList);
            $em->flush();

            $this->addFlash('info', "The sendersList has been deleted");

            return $this->redirectToRoute('adena_mail_senders_list_list');
        }

        return $this->render('@AdenaMail/SendersList/delete.html.twig', array(
            'sendersList' => $sendersList,
            'form'   => $form->createView(),
        ));
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\SendersList           $sendersList
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Request $request, SendersList $sendersList )
    {
        $form = $this->get('form.factory')->create(SendersListType::class, $sendersList);
        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'SendersList updated.');

            $redirectUrl = $this->generateUrl('adena_mail_senders_list_list');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);

        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:SendersList:edit_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:SendersList:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
