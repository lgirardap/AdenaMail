<?php

namespace Adena\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class XhrController extends Controller
{
    public function validationAction(Request $request){
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

        return new JsonResponse([
            'template' => $this->renderView('AdenaMailBundle:MailingList:add_form.html.twig', [
                'form' => $form->createView(),
            ])
        ], 400);
    }
}
