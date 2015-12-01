<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private $container;

    public function __construct(HttpUtils $httpUtils, array $options, ContainerInterface $container) {
        parent::__construct( $httpUtils, $options );
        $this->container = $container;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->container->get('thinky.appbundle.sweet_alert')->success('Vítejte zpět !', 'Byli jste úspěšně přihlášeni.');

        return parent::onAuthenticationSuccess( $request, $token );;
    }
}
