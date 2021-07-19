<?php

namespace Tudock\TodaysMessage\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Active
 *
 * @package VendorName\SysConfigTable\Block\System\Config\Form\Field
 */
class Fields extends AbstractFieldArray {

    /**
     * @var bool
     */
    protected $_addAfter = TRUE;

    /**
     * @var categoriesRenderer
     */
    protected $categoriesRenderer;

    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     * Construct
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {
        $this->addColumn('category', [
            'label' => __('Category List'),
            'renderer' => $this->getCategoryRenderer()
        ]);
        $this->addColumn('message', ['label' => __('Message'), 'style' => 'width:200px' ]);
        $this->_addAfter = FALSE;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws LocalizedException
     */
    protected function getCategoryRenderer() {
        if (!$this->categoriesRenderer) {
            $this->categoriesRenderer = $this->getLayout()->createBlock(
                    \Tudock\TodaysMessage\Block\System\Config\Form\Field\Categories::class, '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->categoriesRenderer->setClass('required-entry');
            $this->categoriesRenderer->setExtraParams('style="width:200px"');
        }
        return $this->categoriesRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void {
        $options = [];
        $category = $row->getCategory();
        if ($category != '') {
            $options['option_' . $this->getCategoryRenderer()->calcOptionHash($category)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

}
