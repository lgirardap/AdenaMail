<?php

namespace Adena\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CoreController extends Controller
{
    /**
     * @param string $view The view name
     * @param array  $parameters
     * @param int    $status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @internal param \Symfony\Component\Form\FormInterface $form
     */
    public function jsonRender($view, $parameters = array(),  $status = 200){
        return new JsonResponse([
            'view' => $this->renderView($view, $parameters)
        ], $status);
    }

    /**
     * @param string   $redirectUrl
     * @param int $status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function jsonRedirect($redirectUrl, $status = 200){
        return new JsonResponse([
            'url'=>$redirectUrl
        ], $status);
    }

    public function redirectToReferer() {
        return $this->redirect(
            $this->get('request_stack')->getCurrentRequest()
                ->headers
                ->get('referer')
        );
    }
}
