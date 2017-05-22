<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Sender;
use Adena\MailBundle\Form\SenderType;
use Symfony\Component\HttpFoundation\Request;

class SenderController extends CoreController
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Sender:index.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request )
    {
        $sender = new Sender();
        $form = $this->get('form.factory')->create(SenderType::class, $sender);

        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Save it
            $em = $this->getDoctrine()->getManager();
            $em->persist( $sender );
            $em->flush();

            $this->addFlash('success', 'Sender successfully added');

            $redirectUrl = $this->generateUrl('adena_mail_sender_list');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Sender:add_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Sender:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(){

        $em = $this->getDoctrine()->getManager();
        $senderRepository = $em->getRepository('AdenaMailBundle:Sender');

        $senders = $senderRepository->findAll();

        return $this->render('AdenaMailBundle:Sender:list.html.twig', array(
            'senders' => $senders
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Sender           $sender
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, Sender $sender)
    {
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($sender);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "The sender has been deleted");

            return $this->redirectToRoute('adena_mail_sender_list');
        }

        return $this->render('@AdenaMail/Sender/delete.html.twig', array(
            'sender' => $sender,
            'form'   => $form->createView(),
        ));
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Sender           $sender
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Request $request, Sender $sender )
    {
        $form = $this->get('form.factory')->create(SenderType::class, $sender);
        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Sender updated.');

            $redirectUrl = $this->generateUrl('adena_mail_sender_list');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);

        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Sender:edit_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Sender:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
