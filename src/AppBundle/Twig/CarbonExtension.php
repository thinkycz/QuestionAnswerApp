<?php

namespace AppBundle\Twig;

use Carbon\Carbon;

class CarbonExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'carbon_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('diffForHumans', array($this, 'diffForHumans')),
        );
    }

    public function diffForHumans(\DateTime $datetime)
    {
        Carbon::setLocale('cs');
        return Carbon::createFromTimestamp($datetime->getTimestamp())->diffForHumans();
    }
}