<?php

namespace Tudock\TodaysMessage\Observer;

class Categorysaveafter implements \Magento\Framework\Event\ObserverInterface {

    private $category = null;
    private $helper;

    public function __construct(
    \Tudock\TodaysMessage\Helper\TodaysMessage $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $_objectManager->get('\Tudock\TodaysMessage\Helper\TodaysMessage');
        if (!$helper->isIndexerScheduled()) {
            $this->category = $observer->getEvent()->getCategory();
            $ids = $this->helper->getProductIds($this->category->getId());
            $customIndexer = $_objectManager->get('Tudock\TodaysMessage\Model\Indexer\CategoryMessage');
            $customIndexer->execute($ids);
        }
    }

}
