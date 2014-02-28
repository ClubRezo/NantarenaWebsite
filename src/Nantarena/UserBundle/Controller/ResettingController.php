<?php

namespace Nantarena\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\ResettingController as BaseController;


class ResettingController extends BaseController
{
    use ControllerFilters;

    public function requestAction()
    {
        if (null !== ($response = $this->anonymousOnlyFilter()))
            return $response;

        return parent::requestAction();
    }

    public function sendEmailAction(Request $request)
    {
        if (null !== ($response = $this->anonymousOnlyFilter()))
            return $response;

        return parent::sendEmailAction($request);
    }

    public function checkEmailAction(Request $request)
    {
        if (null !== ($response = $this->anonymousOnlyFilter()))
            return $response;

        return parent::checkEmailAction($request);
    }

    public function resetAction(Request $request, $token)
    {
        if (null !== ($response = $this->anonymousOnlyFilter()))
            return $response;

        return parent::resetAction($request, $token);
    }
}
