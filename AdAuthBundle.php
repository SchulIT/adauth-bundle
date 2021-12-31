<?php

namespace AdAuthBundle;

use AdAuthBundle\DependencyInjection\AdAuthExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdAuthBundle extends Bundle {
    public function getContainerExtension(): ?ExtensionInterface {
        return new AdAuthExtension();
    }
}
