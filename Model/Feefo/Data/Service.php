<?php

namespace Feefo\Reviews\Model\Feefo\Data;

use Feefo\Reviews\Api\Feefo\Data\ServiceInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Zend\Uri\Uri;

/**
 * Class Service
 */
class Service extends JsonableDataObject implements ServiceInterface
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @param Uri $uri
     * @param array $data
     */
    public function __construct(
        Uri $uri,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($jsonHelper, $data);

        $this->uri = $uri;
    }

    /**
     * Retrieve plugin ID
     *
     * @return string
     */
    public function getPluginId()
    {
        return $this->getData(static::PLUGIN_ID);
    }

    /**
     * Retrieve either registration or configuration page URL depends on which state of the flow you are
     *
     * @return string
     */
    public function getPageUrl()
    {
        if ($this->hasData(static::REGISTRATION_URL)) {
            return $this->getData(static::REGISTRATION_URL);
        } elseif ($this->hasData(static::CONFIGURATION_URI)) {
            return $this->getData(static::CONFIGURATION_URI);
        } elseif ($this->hasData(static::REGISTRATION_URI)) {
            return $this->getData(static::REGISTRATION_URI);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getConfigurationUri()
    {
        return $this->getData(static::CONFIGURATION_URI);
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->getData(static::REDIRECT_URL);
    }

    /**
     * @return string
     */
    public function getIdForRegisteredPlugin()
    {
        $configurationUri = $this->getConfigurationUri();

        if (!$configurationUri) {
            return null;
        }

        $params = explode('?', $configurationUri);

        if (!isset($params[1])) {
            return null;
        }

        $this->uri->setQuery($params[1]);
        $parsedParams = $this->uri->getQueryAsArray();

        if (!isset($parsedParams[self::PLUGIN_ID])) {
            return null;
        }

        return $parsedParams[self::PLUGIN_ID];
    }
}
