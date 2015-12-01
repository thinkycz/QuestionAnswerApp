<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SweetAlert
{
    private $container;

    /**
     * SweetAlert constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function show($title, $text, $type)
    {
        $flashBag = $this->container->get('session')->getFlashBag();
        $flashBag->add('swal.title', $title);
        $flashBag->add('swal.text', $text);
        $flashBag->add('swal.type', $type);
    }

    public function modal($title, $text, $type, $btn)
    {
        $flashBag = $this->container->get('session')->getFlashBag();
        $flashBag->add('swalm.title', $title);
        $flashBag->add('swalm.text', $text);
        $flashBag->add('swalm.type', $type);
        $flashBag->add('swalm.btn', $btn);
    }

    public function success($title, $text)
    {
        $this->show($title, $text, 'success');
    }

    public function error($title, $text)
    {
        $this->show($title, $text, 'error');
    }

    public function warning($title, $text)
    {
        $this->show($title, $text, 'warning');
    }

    public function info($title, $text)
    {
        $this->show($title, $text, 'info');
    }
}