<?php
namespace Adena\PaginatorBundle\Twig;


use Adena\PaginatorBundle\Tools\Pagination\AdenaPaginator;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationExtension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;
    private $range = 8;

    public function __construct(RequestStack $requestStack)
    {

        $this->requestStack = $requestStack;
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_Function('adena_pagination_render', array($this, 'render'), array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    public function render( \Twig_Environment $env, AdenaPaginator $paginator )
    {

        $request = $this->requestStack->getMasterRequest();

        $query  = array_merge($request->query->all(), $request->attributes->get('_route_params', array()));
        foreach ($query as $key => $param) {
            if (substr($key, 0, 1) == '_') {
                unset($query[$key]);
            }
        }

        if ($this->range > $paginator->getNbPages()) {
            $this->range = $paginator->getNbPages();
        }

        $delta = ceil($this->range / 2);
        if ($paginator->getPage() - $delta > $paginator->getNbPages() - $this->range) {
            $pages = range($paginator->getNbPages() - $this->range + 1, $paginator->getNbPages());
        } else {
            if ($paginator->getPage() - $delta < 0) {
                $delta = $paginator->getPage();
            }
            $offset = $paginator->getPage() - $delta;
            $pages = range($offset + 1, $offset + $this->range);
        }

        $params = array(
            'route' => $request->get('_route'),
            'query' => $query,
            'nbPages'   => $paginator->getNbPages(),
            'startPage' => min($pages),
            'endPage' => max($pages),
            'page' => $paginator->getPage(),
            'pageParameterName' => 'page',
            'pagesInRange' => $pages
        );

        if ($paginator->getPage() - 1 > 0) {
            $params['previous'] = $paginator->getPage() - 1;
        }
        if ($paginator->getPage() + 1 <= $paginator->getNbPages()) {
            $params['next'] = $paginator->getPage() + 1;
        }


        // Get the mandatory parameters and calculate some sh**
        return $env->render(
            '@AdenaPaginator/Pagination/pagination.html.twig',
            $params
            );
    }
}