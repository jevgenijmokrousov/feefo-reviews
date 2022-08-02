<?php

namespace Feefo\Reviews\Helper;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Backend\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Store\Model\Information as StoreModelInformation;
use Magento\User\Model\User as UserModel;
use Psr\Log\LoggerInterface;
use Feefo\Reviews\Api\Feefo\Helper\ScopeInterface;
use Feefo\Reviews\Api\Feefo\Helper\StoreDetailsInterface;
use Feefo\Reviews\Api\Feefo\StorageInterface;
use Zend\Uri\Http;

/**
 * Interface StoreDetails
 *
 * Get information about store
 */
class StoreDetails extends DataObject implements StoreDetailsInterface
{
    const TEMPLATE_DEFAULT_MERCHANT_NAME = "%s Merchant Name";

    const DEFAULT_MERCHANT_LANGUAGE = "en_US";

    const TEMPLATE_DEFAULT_MERCHANT_DESCRIPTION = '%s Store';

    const ROUTE_CONFIGURATION_PAGE = 'feefo/options/index';

    const XPATH_GENERAL_EMAIL = 'trans_email/ident_general/email';

    const XPATH_GENERAL_EMAIL_NAME = 'trans_email/ident_general/name';

    const DEFAULT_STORE_OWNER = 'Store Owner';

    const DEFAULT_MERCHANT_EMAIL = 'default@email.com';

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Admin session
     *
     * @var AdminSession
     */
    protected $adminSession;

    /**
     * @var Http;
     */
    private $http;

    /**
     * StoreDetails constructor.
     *
     * @param ScopeInterface $scopeConfig
     * @param Url $urlBuilder
     * @param LoggerInterface $logger
     * @param StorageInterface $storage
     * @param AdminSession $adminSession
     * @param Http $http
     * @param array $data
     */
    public function __construct(
        ScopeInterface $scopeConfig,
        Url $urlBuilder,
        LoggerInterface $logger,
        StorageInterface $storage,
        AdminSession $adminSession,
        Http $http,
        array $data = []
    ) {
        parent::__construct($data);
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->adminSession = $adminSession;
        $this->http = $http;
    }

    /**
     * Configure a scope for getting data
     *
     * @param array $data
     *
     * @return void
     */
    public function initScope($data)
    {
        $this->scopeConfig->initScope($data);
    }

    /**
     * Retrieve merchant domain
     *
     * @return string
     */
    public function getMerchantDomain()
    {
        $url = $this->getMerchantUrl();
        $parsedUrl = $this->http->parse($url);

        return $parsedUrl->getHost();
    }

    /**
     * Retrieve merchant name
     *
     * @return string
     */
    public function getMerchantName()
    {
        $siteName = $this->scopeConfig->getConfig(StoreModelInformation::XML_PATH_STORE_INFO_NAME);
        if ($siteName === null) {
            $siteName = $this->getDefaultMerchantName();
        }

        return $siteName;
    }

    /**
     * Retrieve merchant description
     *
     * @return string
     */
    public function getMerchantDescription()
    {
        return $this->getDefaultMerchantDescription();
    }

    /**
     * Retrieve merchant URL
     *
     * @return mixed
     */
    public function getMerchantUrl()
    {
        return $this->storage->getWebsiteUrl();
    }

    /**
     * Retrieve merchant language
     *
     * @return string
     */
    public function getMerchantLanguage()
    {
        $lang = $this->scopeConfig->getConfig(Data::XML_PATH_DEFAULT_LOCALE);
        if ($lang === null) {
            $lang = $this->getDefaultMerchantLanguage();
        }

        return $lang;
    }

    /**
     * Retrieve merchant email
     *
     * @return string
     */
    public function getMerchantEmail()
    {
        $adminUser = $this->getAdminUser();
        if ($adminUser && $adminUser->getId()) {
            return $adminUser->getEmail();
        }

        return $this->getDefaultMerchantEmail();
    }

    /**
     * Retrieve merchant shop owner
     *
     * @return string
     */
    public function getMerchantShopOwner()
    {
        $adminUser = $this->getAdminUser();
        if ($adminUser && $adminUser->getId()) {
            return $adminUser->getName();
        }

        return $this->getDefaultMerchantName();
    }

    /**
     * Retrieve merchant image URL
     *
     * @return string
     */
    public function getMerchantImageUrl()
    {
        return "";
    }

    /**
     * Retrieve stores of a website with $websiteId
     *
     * @return string[]
     */
    public function getStoreIds()
    {
        return implode(',', $this->storage->getStoreIds());
    }

    /**
     * Retrieve redirect URL
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->urlBuilder->getUrl(static::ROUTE_CONFIGURATION_PAGE);
    }

    /**
     * Retrieve the first admin user
     *
     * @return UserModel
     */
    protected function getAdminUser()
    {
        /** @var UserModel $user */
        $user = $this->adminSession->getUser();

        return $user;
    }

    /**
     * Retrieve default merchant name
     *
     * @return string
     */
    protected function getDefaultMerchantName()
    {
        return sprintf(static::TEMPLATE_DEFAULT_MERCHANT_NAME, $this->getMerchantDomain());
    }

    /**
     * Retrieve default merchant language
     *
     * @return string
     */
    protected function getDefaultMerchantLanguage()
    {
        return static::DEFAULT_MERCHANT_LANGUAGE;
    }

    /**
     * Retrieve default merchant description
     *
     * @return string
     */
    protected function getDefaultMerchantDescription()
    {
        return sprintf(static::TEMPLATE_DEFAULT_MERCHANT_DESCRIPTION, $this->getMerchantDomain());
    }

    /**
     * Retrieve default merchant email
     *
     * @return string
     */
    protected function getDefaultMerchantEmail()
    {
        $merchantEmail = $this->scopeConfig->getConfig(static::XPATH_GENERAL_EMAIL);
        if ($merchantEmail === null) {
            $merchantEmail = static::DEFAULT_MERCHANT_EMAIL;
        }

        return $merchantEmail;
    }

    /**
     * Retrieve default shop owner
     *
     * @return string
     */
    protected function getDefaultShopOwner()
    {
        $storeOwner = $this->scopeConfig->getConfig(static::XPATH_GENERAL_EMAIL_NAME);
        if ($storeOwner === null) {
            $storeOwner = static::DEFAULT_STORE_OWNER;
        }

        return $storeOwner;
    }
}
