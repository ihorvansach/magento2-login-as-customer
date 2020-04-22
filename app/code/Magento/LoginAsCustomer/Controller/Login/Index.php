<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Login;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;

/**
 * Login As Customer storefront login action
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    private $loginModel;


    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login $loginModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel;
    }
    /**
     * Login As Customer storefront login
     *
     * @return ResultInterface
     */
    public function execute():ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $login = $this->_initLogin();

            /* Log in */
            $login->authenticateCustomer();
            $this->messageManager->addSuccessMessage(
                __('You are logged in as customer: %1', $login->getCustomer()->getName())
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            $resultRedirect->setPath('/');
            return $resultRedirect;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect->setPath('*/*/proceed');
        return $resultRedirect;
    }

    /**
     * Init login info
     * @return \Magento\LoginAsCustomer\Model\Login
     */
    private function _initLogin(): \Magento\LoginAsCustomer\Model\Login
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            throw LocalizedException(__('Cannot login to account. No secret key provided.'));
        }

        $login = $this->loginModel->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            throw LocalizedException(__('Cannot login to account. Secret key is not valid'));
        }
    }
}
