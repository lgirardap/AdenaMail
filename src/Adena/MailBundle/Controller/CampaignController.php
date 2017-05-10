<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Form\CampaignType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends CoreController
{
    public function viewAction(Campaign $campaign){
        return $this->render('AdenaMailBundle:Campaign:view.html.twig', [
            'campaign' => $campaign
        ]);
    }

    public function sendAction(Campaign $campaign){
//
//        if($campaign->getStatus() != Campaign::STATUS_NEW){
//            $this->addFlash('warning', 'Campaign already started.');
//            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
//        }

        // We use our Campaign to queue Library to add the campaign email to the queue table
        $campaignToQueue = $this->get("adena_mail.entity_helper.campaign_to_queue");
        $campaignToQueue->createQueue($campaign);

        // Change the campaign status to in_progress
        $campaign->setStatus(Campaign::STATUS_IN_PROGRESS);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // TODO create console command
        // exec console command

        $this->addFlash('success', 'Sending campaign.');

        return new Response('<body></body>');
    }

    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Campaign:index.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request )
    {
        $campaign = new Campaign();
        $form = $this->get('form.factory')->create(CampaignType::class, $campaign);

        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist( $campaign );
            $em->flush();

            $this->addFlash('success', 'Campaign successfully added');

            $redirectUrl = $this->generateUrl('adena_mail_campaign_add');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }
            return $this->redirect($redirectUrl);

        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Campaign:add_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Campaign:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(){

        $em = $this->getDoctrine()->getManager();
        $campaignRepository = $em->getRepository('AdenaMailBundle:Campaign');

        $campaigns = $campaignRepository->findAll();

        return $this->render('AdenaMailBundle:Campaign:list.html.twig', array(
            'campaigns' => $campaigns
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Campaign       $campaign
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param \Adena\MailBundle\Entity\Sender $sender
     *
     */
    public function deleteAction(Request $request, Campaign $campaign)
    {

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($campaign);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "This campaign has been deleted");

            return $this->redirectToRoute('adena_mail_campaign_list');
        }

        return $this->render('@AdenaMail/Campaign/delete.html.twig', array(
            'campaign'    => $campaign,
            'form'          => $form->createView(),
        ));
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Campaign       $campaign
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param \Adena\MailBundle\Entity\Sender $sender
     *
     */
    public function editAction( Request $request, Campaign $campaign )
    {
        $form = $this->get('form.factory')->create(CampaignType::class, $campaign);
        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Campaign updated.');

            $redirectUrl = $this->generateUrl('adena_mail_campaign_edit', [
                'id' => $campaign->getId()
            ]);

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }

            return $this->redirect($redirectUrl);
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Campaign:edit_form.html.twig', [
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Campaign:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
