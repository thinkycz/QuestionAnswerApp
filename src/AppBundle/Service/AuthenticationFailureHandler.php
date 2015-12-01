<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    private $container;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null, ContainerInterface $container)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->container = $container;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->container->get('thinky.appbundle.sweet_alert')->error('Stala se chyba', 'Přihlášení bylo neúspěšné, zkuste to prosím znova.');
        return parent::onAuthenticationFailure($request, $exception);
    }

}