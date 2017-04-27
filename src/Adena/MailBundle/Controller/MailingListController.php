<?php

namespace Adena\MailBundle\Controller;

use Adena\MailBundle\Form\MailingListEditType;
use Adena\MailBundle\Form\MailingListType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Adena\MailBundle\Entity\MailingList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\InvalidParameterException;

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

            return $this->redirectToRoute('adena_mail_mailing_list_view', [
                'id' => $mailingList->getId()
            ]);
        }

        dump($this->getErrorsFromForm($form));
        return $this->render('AdenaMailBundle:MailingList:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function xhrValidationAction(Request $request){
        if(!$request->isXmlHttpRequest() || !$request->isMethod('POST')){
            throw new NotFoundHttpException('Only accessible via XmlHttp POST');
        }

        $formData = $request->request->get($request->request->get('form_name'));

        $dataClass = $formData['data_class'];

        $entity = new $dataClass();
        $form = $this->createForm($formData['formtype_class'], $entity);

        if($form->handleRequest($request)->isValid()){
            return new JsonResponse([]);
        }

        return new JsonResponse($this->getErrorsFromForm($form), 400);
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

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $this->renderView('@AdenaMail/form.error.html.twig', [
                        'form'=>$form->createView(),
                        'element'=>$childForm->getName()
                    ]);
                }
            }else{
                //dump($childForm);
            }
        }
        return $errors;
    }
}
