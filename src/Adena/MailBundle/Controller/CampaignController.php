<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Form\CampaignTestMailingListType;
use Adena\MailBundle\Form\CampaignType;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends CoreController
{
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

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Send the (test) campaign
            $this->get('tool.background_runner')->runConsoleCommand('adenamail:campaign:test '.$campaign->getId());

            $this->addFlash('success', 'Your test campaign will be sent shortly : '.$campaign->getName());

            $redirectUrl = $this->generateUrl('adena_mail_campaign_list');

            if($request->isXmlHttpRequest()) {
                return $this->jsonRedirect($redirectUrl);
            }
            return $this->redirect($redirectUrl);
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

            // TODO make pretty message with ifs
            $this->addFlash('warning', 'Campaign already started or not tested.');
            return $this->redirectToRoute('adena_mail_campaign_view', ['id'=>$campaign->getId()]);
        }

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            // Launch the actual send in a different process through the console command
            $this->get('tool.background_runner')->runConsoleCommand('adenamail:campaign:send '.$campaign->getId());

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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(){

        $em = $this->getDoctrine()->getManager();
        $campaignRepository = $em->getRepository('AdenaMailBundle:Campaign');

        $campaigns = $campaignRepository->findAll();
        $campaignActionControl = $this->get("adena_mail.action_control.campaign");


        return $this->render('AdenaMailBundle:Campaign:list.html.twig', array(
            'campaigns' => $campaigns,
            'campaignActionControl' => $campaignActionControl
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
