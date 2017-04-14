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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(){

        $em = $this->getDoctrine()->getManager();
        $senderRepository = $em->getRepository('AdenaMailBundle:Sender');

        $result = $senderRepository->findAll();

        return $this->render('AdenaMailBundle:Sender:list.html.twig', array(
            'senders' => $result
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

            $request->getSession()->getFlashBag()->add('notice', 'Sender updated.');

            return $this->redirectToRoute('adena_mail_sender_list');

        }

        return $this->render('AdenaMailBundle:Sender:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
