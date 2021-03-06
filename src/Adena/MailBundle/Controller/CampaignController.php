<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Form\CampaignTestMailingListType;
use Adena\MailBundle\Form\CampaignType;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends CoreController
{
    public function getCampaignAJAXAction(Request $request)
    {
        if(!$request->isXmlHttpRequest()){
            return;
        }

        $emails = $this->getDoctrine()
            ->getRepository('AdenaMailBundle:Email')
            ->getEmailsQueryBuilder($request->query->get('q'))
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($emails as $email){
            $result[] = [
                'id'=>$email['id'],
                'text'=>$email['name']
            ];
        }
        
        return $this->json($result);
    }

    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Campaign:index.html.twig');
    }

    public function viewAction(Campaign $campaign){
        // For the IN_PROGRESS campaigns, let's find out how many emails are remaining in the queue
        $remainingEmails = 0;
        if(Campaign::STATUS_IN_PROGRESS == $campaign->getStatus()){
            $remainingEmails = $this->getDoctrine()->getRepository('AdenaMailBundle:Queue')->countByCampaign($campaign);
        }

        return $this->render('AdenaMailBundle:Campaign:view.html.twig', [
            'campaign' => $campaign,
            'remainingEmails' => $remainingEmails
        ]);
    }

    public function testAction(Request $request, Campaign $campaign)
    {
        $campaignActionControl = $this->get('adena_mail.action_control.campaign');
        if(!$campaignActionControl->isAllowed('test', $campaign)){

            $this->addFlash('warning', 'This campaign is already sent and you cannot test it again.');
            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
        }

        $form = $this->get('form.factory')->create(CampaignTestMailingListType::class, $campaign);

        try {
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                // Save the Mailing lists Tests for this campaign
                $this->getDoctrine()->getManager()->flush();

                // Send the (test) campaign
                $this->get('adena_mail.entity_helper.campaign_tester')->test($campaign, true);

                $this->addFlash('success', 'Your test campaign will be sent shortly.');

                $redirectUrl = $this->generateUrl('adena_mail_campaign_list');

                if ($request->isXmlHttpRequest()) {
                    return $this->jsonRedirect($redirectUrl);
                }
                return $this->redirect($redirectUrl);
            }
        }catch(\Exception $e){
            $this->addFlash('danger', $e->getMessage());
        }

        if($request->isXmlHttpRequest()){
            return $this->jsonRender('AdenaMailBundle:Campaign:test_form.html.twig', [
                'campaign' => $campaign,
                'form' => $form->createView(),
            ], 400);
        }

        return $this->render('AdenaMailBundle:Campaign:test.html.twig', array(
            'campaign' => $campaign,
            'form' => $form->createView()
        ));
    }

    public function sendAction(Campaign $campaign, Request $request){

        $campaignActionControl = $this->get('adena_mail.action_control.campaign');
        if(!$campaignActionControl->isAllowed('start_resume', $campaign)){

            $this->addFlash('warning', 'Campaign already started or not tested.');
            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
        }

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            // Launch the actual send in a different process through the console command
            $this->get('adena_core.tool.background_runner')->runConsoleCommand('adenamail:campaign:send '.$campaign->getId());

            $this->addFlash('success', 'Your campaign will be sent shortly.');

            return $this->redirectToRoute('adena_mail_campaign_list');
        }

        return $this->render('@AdenaMail/Campaign/send.html.twig', array(
            'campaign'    => $campaign,
            'form'        => $form->createView(),
        ));
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

            $redirectUrl = $this->generateUrl('adena_mail_campaign_list');

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

    public function pauseAction( Request $request, Campaign $campaign )
    {
        $campaignActionControl = $this->get('adena_mail.action_control.campaign');
        if(!$campaignActionControl->isAllowed('pause', $campaign)){

            $this->addFlash('warning', 'You cannot pause this campaign');
            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
        }

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $this->get("adena_mail.entity_helper.campaign_sender")->pause($campaign);
            $this->addFlash('success', 'Your campaign has been paused.');

            return $this->redirectToRoute('adena_mail_campaign_list');
        }

        return $this->render('@AdenaMail/Campaign/pause.html.twig', array(
            'campaign'    => $campaign,
            'form'        => $form->createView(),
        ));
    }

    /**
     * @param $page
     *
     * @param bool $showCompleted
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page, $showCompleted = false){
        try {
            if(!$showCompleted) {
                $query = $this->getDoctrine()->getManager()->getRepository('AdenaMailBundle:Campaign')->getActiveCampaignsQuery();
            }else{
                $query = $this->getDoctrine()->getManager()->getRepository('AdenaMailBundle:Campaign')->getCompletedCampaignsQuery();
            }

            $campaigns = $this->get('adena_paginator.paginator.paginator')->paginate($query, $page);

            $campaignActionControl = $this->get("adena_mail.action_control.campaign");
            return $this->render('AdenaMailBundle:Campaign:list.html.twig', array(
                'campaigns' => $campaigns,
                'campaignActionControl' => $campaignActionControl,
                'showCompleted' => $showCompleted
            ));
        }catch(\InvalidArgumentException $e){
            throw $this->createNotFoundException($e->getMessage());
        }
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

            $this->addFlash('success', "This campaign has been deleted");

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

        $campaignActionControl = $this->get('adena_mail.action_control.campaign');
        if(!$campaignActionControl->isAllowed('edit', $campaign)){
            $this->addFlash('warning', 'This campaign is already sent and you cannot edit it.');
            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
        }

        $form = $this->get('form.factory')->create(CampaignType::class, $campaign);
        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            // Set the status back to NEW if we edit it. We must test it again.
            $campaign->setStatus(Campaign::STATUS_NEW);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Campaign updated.');

            $redirectUrl = $this->generateUrl('adena_mail_campaign_list');

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
