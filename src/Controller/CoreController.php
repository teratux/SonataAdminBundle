<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

// NEXT_MAJOR: remove this file

@trigger_error(sprintf(
    'The %1$s\CoreController class is deprecated since version 3.36 and will be removed in 4.0.'
    .' Use %1$s\SearchAction or %1$s\DashboardAction instead.',
    __NAMESPACE__
), \E_USER_DEPRECATED);

use Sonata\AdminBundle\Action\DashboardAction;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CoreController extends AbstractController
{
    /**
     * @return Response
     */
    public function dashboardAction()
    {
        $dashboardAction = $this->container->get(DashboardAction::class);

        return $dashboardAction($this->getCurrentRequest());
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @throws \RuntimeException
     *
     * @return JsonResponse|Response
     */
    public function searchAction(Request $request)
    {
        $searchAction = $this->container->get(SearchAction::class);

        return $searchAction($request);
    }

    /**
     * Get the request object from the container.
     *
     * This method is compatible with both Symfony 2.3 and Symfony 3
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.0, to be removed in 4.0 and action methods will be adjusted.
     *             Use Symfony\Component\HttpFoundation\Request as an action argument
     *
     * @return Request
     */
    public function getRequest()
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since 3.0 and will be removed in 4.0.'
            .' Inject the %s into the actions instead.',
            __METHOD__,
            Request::class
        ), \E_USER_DEPRECATED);

        return $this->getCurrentRequest();
    }

    /**
     * @return Pool
     */
    protected function getAdminPool()
    {
        $pool = $this->container->get('sonata.admin.pool');
        \assert($pool instanceof Pool);

        return $pool;
    }

    /**
     * @return SearchHandler
     */
    protected function getSearchHandler()
    {
        $searchHandler = $this->get('sonata.admin.search.handler');
        \assert($searchHandler instanceof SearchHandler);

        return $searchHandler;
    }

    /**
     * @return string
     */
    protected function getBaseTemplate()
    {
        if ($this->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->getTemplateRegistry()->getTemplate('ajax');
        }

        return $this->getTemplateRegistry()->getTemplate('layout');
    }

    private function getTemplateRegistry(): TemplateRegistryInterface
    {
        $templateRegistry = $this->container->get('sonata.admin.global_template_registry');
        \assert($templateRegistry instanceof TemplateRegistryInterface);

        return $templateRegistry;
    }

    private function getCurrentRequest(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
