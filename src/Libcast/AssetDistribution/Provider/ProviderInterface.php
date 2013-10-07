<?php

namespace Libcast\AssetDistribution\Provider;

interface ProviderInterface
{
    /**
     * Loads static data configuration
     */
    public function configure();
    
    /**
     * Declare if the user has been authenticated and/or if the application has been
     * authorized to connect the provider.
     * 
     * @return boolean True if connected
     */
    public function isAuthorized();
}