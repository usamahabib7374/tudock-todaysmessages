<?php

declare(strict_types = 1);

namespace Tudock\TodaysMessage\Block\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Categories extends Select {

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value) {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value) {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    protected function getSourceOptions(): array {
        $categories = $this->_toArray();
        $options = [];
        
        foreach ($categories as $key => $value) {
            $options[$key] = ['label' => $value, 'value' => $key];
        }
        return $options;
    }

    private function _toArray() {
        $categories = $this->getCategoryCollection(true, false, false, false);

        $catagoryList = array();
        foreach ($categories as $category) {
            $catagoryList[$category->getEntityId()] = __($this->_getParentName($category->getPath()) . $category->getName());
        }

        return $catagoryList;
    }

    private function _getParentName($path = '') {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $parentName = '';
        $rootCats = array(1, 2);

        $catTree = explode("/", $path);
        // Deleting category itself
        array_pop($catTree);

        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }

    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $collection = $collectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

}
