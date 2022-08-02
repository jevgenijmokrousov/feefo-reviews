<?php

namespace Feefo\Reviews\Controller\Adminhtml\Options;

use Feefo\Reviews\Api\Feefo\Data\StoreUrlGroupDataInterface;
use Feefo\Reviews\Api\Feefo\StorageInterface;
use Feefo\Reviews\Api\Feefo\StoreUrlGroupInterface;
use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Url\DecoderInterface as UrlDecoder;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends BackendAction
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var StoreUrlGroupInterface
     */
    protected $storeUrlGroup;

    /**
     * @var UrlDecoder
     */
    protected $urlDecoder;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * Index constructor
     *
     * @param ActionContext $context
     * @param StorageInterface $storage
     * @param StoreUrlGroupInterface $storeUrlGroup
     * @param UrlDecoder $urlDecoder
     */
    public function __construct(
        ActionContext $context,
        StorageInterface $storage,
        StoreUrlGroupInterface $storeUrlGroup,
        UrlDecoder $urlDecoder,
        PageFactory $pageFactory
    ) {
        $this->storage = $storage;
        $this->storeUrlGroup = $storeUrlGroup;
        $this->urlDecoder = $urlDecoder;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Show choose website, registration flow, configuration pages.
     *
     * @return $this
     */
    public function execute()
    {
        $websiteUrl = (string) $this->getRequest()->getParam('website_url', '');
        $websiteUrl = $this->urlDecoder->decode($websiteUrl);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($websiteUrl) {
            /** @var StoreUrlGroupDataInterface $urlGroup */
            $urlGroup = $this->storeUrlGroup->getGroupByUrl($websiteUrl);

            if (!$urlGroup) {
                $this->messageManager->addErrorMessage(__('The "%1" store can not be set.', $websiteUrl));
            } else {
                $storeIds = $urlGroup->getStoreIds();
                $this->storage->setWebsiteUrl($websiteUrl, $storeIds);
                $this->storage->setStoreIds($storeIds);
            }
            return $resultRedirect->setPath('feefo/*/');
        }

        /** @var Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Feefo Ratings & Reviews Configurations'));

        return $resultPage;
    }
}
