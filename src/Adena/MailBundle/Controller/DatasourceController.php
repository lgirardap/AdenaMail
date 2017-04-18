<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Entity\Datasource;
use Adena\MailBundle\Form\DatasourceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DatasourceController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdenaMailBundle:Datasource:index.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request )
    {
        $datasource = new Datasource();
        $form = $this->get('form.factory')->create(DatasourceType::class, $datasource);

        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist( $datasource );
            $em->flush();

            $this->addFlash('success', 'Datasource successfully added');

            return $this->redirectToRoute('adena_mail_datasource_add');

        }

        return $this->render('AdenaMailBundle:Datasource:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(){

        $em = $this->getDoctrine()->getManager();
        $datasourceRepository = $em->getRepository('AdenaMailBundle:Datasource');

        $datasources = $datasourceRepository->findAll();

        return $this->render('AdenaMailBundle:Datasource:list.html.twig', array(
            'datasources' => $datasources
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Datasource       $datasource
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param \Adena\MailBundle\Entity\Sender $sender
     *
     */
    public function deleteAction(Request $request, Datasource $datasource)
    {

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($datasource);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "This datasource has been deleted");

            return $this->redirectToRoute('adena_mail_datasource_list');
        }

        return $this->render('@AdenaMail/Datasource/delete.html.twig', array(
            'datasource'    => $datasource,
            'form'          => $form->createView(),
        ));
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Adena\MailBundle\Entity\Datasource       $datasource
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param \Adena\MailBundle\Entity\Sender $sender
     *
     */
    public function editAction( Request $request, Datasource $datasource )
    {
        $form = $this->get('form.factory')->create(DatasourceType::class, $datasource);
        if( $request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Datasource updated.');

            return $this->redirectToRoute('adena_mail_datasource_list');

        }

        return $this->render('AdenaMailBundle:Datasource:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
