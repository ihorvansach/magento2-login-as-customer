<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Block\Adminhtml;

use Zend\Json\Json;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\LoginAsCustomer\Model\Config;

/**
 * Admin blog post
 */
class ConfirmationPopup extends Template
{
    /**
     * System store
     *
     * @var SystemStore
     */
    private $systemStore;

    /**
     * Config
     *
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        SystemStore $systemStore,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemStore = $systemStore;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $layout = Json::decode(parent::getJsLayout(), Json::TYPE_ARRAY);
        $showStoreViewOptions = $this->config->isManualChoiceEnabled();

        $layout['components']['lac-confirmation-popup']['title'] = $showStoreViewOptions
            ? __('Login as Customer: Select Store View')
            : __('You are about to Login as Customer');
        $layout['components']['lac-confirmation-popup']['content'] = __('Actions taken while in "Login as Customer" will affect actual customer data.');

        $layout['components']['lac-confirmation-popup']['showStoreViewOptions'] = $showStoreViewOptions;
        $layout['components']['lac-confirmation-popup']['storeViewOptions'] = $showStoreViewOptions ? $this->getStoreViewOptions() : [];

        return Json::encode($layout);
    }

    /**
     * Retrieve store views options
     *
     * @return array
     */
    private function getStoreViewOptions():array
    {
        $options = [];
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();
        /** @var \Magento\Store\Model\Website $website */
        foreach ($websiteCollection as $website) {
            $groups = [];
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $stores = [];
                    /** @var  \Magento\Store\Model\Store $store */
                    foreach ($storeCollection as $store) {
                        if ($store->getGroupId() == $group->getId() && $store->isActive()) {
                            $name = $this->sanitizeName($store->getName());
                            $stores[$store->getId()]['label'] = str_repeat(' &nbsp;', 4) . $name;
                            $stores[$store->getId()]['value'] = $store->getId();
                        }
                    }
                    if (!empty($stores)) {
                        $name = $this->sanitizeName($group->getName());
                        $groups[$group->getId()]['label'] = str_repeat(' &nbsp;', 2) . $name;
                        $groups[$group->getId()]['value'] = $stores;
                    }
                }
            }
            if (!empty($groups)) {
                $name = $this->sanitizeName($website->getName());

                $options[$website->getId()]['label'] = $name;
                $options[$website->getId()]['value'] = $groups;
            }
        }

        return $options;
    }

    /**
     * Sanitize website/store option name
     *
     * @param string $name
     *
     * @return string
     */
    private function sanitizeName(string $name):string
    {
        $matches = [];
        preg_match('/\$[:]*{(.)*}/', $name, $matches);
        if (count($matches) > 0) {
            $name = $this->_escaper->escapeHtml($this->_escaper->escapeJs($name));
        } else {
            $name = $this->_escaper->escapeHtml($name);
        }

        return $name;
    }
}
