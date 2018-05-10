<?php

namespace AdAuthBundle;

use AdAuthBundle\DependencyInjection\AdAuthExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdAuthBundle extends Bundle {
    public function getContainerExtension() {
        return new AdAuthExtension();
    }
}
