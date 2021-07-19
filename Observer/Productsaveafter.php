<?php

namespace Tudock\TodaysMessage\Observer;

use Magento\Framework\Event\ObserverInterface;


class Productsaveafter implements ObserverInterface {

   

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $_objectManager->get('\Tudock\TodaysMessage\Helper\TodaysMessage');
        if (!$helper->isIndexerScheduled()) {
            $_product = $observer->getProduct();  // you will get product object
            $_id = $_product->getId(); // for sku
            $customIndexer = $_objectManager->get('Tudock\TodaysMessage\Model\Indexer\CategoryMessage');
            $customIndexer->executeRow($_id);
        }
    }

}
