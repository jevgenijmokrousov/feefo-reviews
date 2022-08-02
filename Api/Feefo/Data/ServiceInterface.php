<?php

namespace Feefo\Reviews\Api\Feefo\Data;

/**
 * Interface MerchantInterface
 *
 * Data Service Contract that describes service information about the store of Feefo service
 */
interface ServiceInterface
{
    public const PLUGIN_ID = 'pluginId';

    public const REGISTRATION_URL = 'registrationUrl';

    public const REDIRECT_URL = 'redirectUrl';

    public const REGISTRATION_URI = 'registrationUri';

    public const CONFIGURATION_URL = 'configurationUrl';

    public const CONFIGURATION_URI = 'configurationUri';

    /**
     * Retrieve plugin ID
     *
     * @return string
     */
    public function getPluginId();

    /**
     * Retrieve either registration or configuration page URL depends on which state of the flow you are
     *
     * @return string
     */
    public function getPageUrl();

    /**
     * @return string
     */
    public function getConfigurationUri();

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @return string
     */
    public function getIdForRegisteredPlugin();
}
