<?php

namespace Adena\MailBundle\Controller;

use Adena\CoreBundle\Controller\CoreController;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Form\CampaignType;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends CoreController
{
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
