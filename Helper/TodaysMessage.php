<?php

namespace Tudock\TodaysMessage\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\DB\Adapter\AdapterInterface;

class TodaysMessage extends AbstractHelper {

    const TABLE_INDEXER = 'catalog_product_index_todaysmessage';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $json;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    protected $_productloader;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * fields permission config path
     */
    const XML_PATH_FIELDS_MESSAGES_CONFIG = 'tudock_general_section/general/active';

    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Serialize\Serializer\Json $json, \Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry, \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->indexerRegistry = $indexerRegistry;
        $this->_productloader = $_productloader;
    }

    public function deleteAll() {
        $this->connection->delete($this->resource->getTableName(self::TABLE_INDEXER));
    }

    public function deleteRows($ids = [], $is_null = false) {

        $where = ['entity_id IN (?)' => $ids];
        if ($is_null) {
            $where = ['message IS NULL'];
        }
        $this->connection->delete(
                self::TABLE_INDEXER, $where
        );
    }

    public function getTodaysMessageForProduct($productId, $categoryId) {
        $select = $this->connection->select()
                        ->distinct()->where('category_id IN (' . implode(',',$categoryId).')')->where('entity_id = ' . $productId)->from(self::TABLE_INDEXER);
        $data = $this->connection->fetchAll($select);
        return $data;
    }

    public function reindexRows($ids) {
        $this->deleteRows($ids);
        $indexData = [];
        $linkedCategories = $this->getLinkedCategories($ids);
        $messages = $this->getMessages();
        if (count($linkedCategories) > 0) {
            foreach ($linkedCategories as $categoryId => $productId) {
                if (isset($messages[$categoryId])) {
                    $indexRow = [];
                    $indexRow['entity_id'] = $productId;
                    $indexRow['category_id'] = $categoryId;
                    $indexRow['message'] = $messages[$categoryId][rand(1, count($messages[$categoryId])) - 1];
                    $indexData[] = $indexRow;
                }
            }
            try {
                $tableName = $this->resource->getTableName(self::TABLE_INDEXER);
                $this->connection->insertMultiple($tableName, $indexData);
            } catch (\Exception $e) {
                if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
                ) {
                    throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('Row already exists.')
                    );
                }
                throw $e;
            }
        }
    }

    public function getProductIds($categoryId) {
        $select = $this->connection->select()
                        ->distinct()->where('category_id = ' . $categoryId)->from('catalog_category_product', 'product_id');
        $productIds = $this->connection->fetchAll($select);
        $return = [];
        if (count($productIds) > 0) {
            foreach ($productIds as $productId) {
                $return[] = $productId['product_id'];
            }
        }
        return $return;
    }

    public function reindexFull() {
        $this->deleteAll();
        $select = $this->connection->select()
                        ->distinct()->from('catalog_category_product', ['entity_id' => 'product_id', 'category_id' => 'category_id', 'message' => '']);
        $this->connection->query(
                $this->connection->insertFromSelect(
                        $select, self::TABLE_INDEXER, ['entity_id', 'category_id'], AdapterInterface::INSERT_ON_DUPLICATE
                )
        );
        $messages = $this->getMessages();
        foreach ($messages as $category_id => $message) {
            $this->connection->update(
                    self::TABLE_INDEXER, ['message' => $message[rand(1, count($message)) - 1]], ['category_id = ?' => $category_id]
            );
        }
        $this->deleteRows([], true);
    }

    private function getLinkedCategories($ids) {
        $linkedCategories = [];
        foreach ($ids as $id) {
            $product = $this->getLoadProduct($id);
            $categories = $product->getCategoryIds();
            foreach ($categories as $categoryId) {
                $linkedCategories[$categoryId] = $id;
            }
        }
        return $linkedCategories;
    }

    public function getLoadProduct($id) {
        return $this->_productloader->create()->load($id);
    }

    public function getMessages() {
        $fields = $this->scopeConfig->getValue(
                self::XML_PATH_FIELDS_MESSAGES_CONFIG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $convertedArray = $this->json->unserialize($fields);
        $groupByCategoryId = [];
        foreach ($convertedArray as $_config) {
            $groupByCategoryId[$_config['category']][] = $_config['message'];
        }
        return $groupByCategoryId;
    }

    /**
     * @return bool
     */
    public function isIndexerScheduled() {
        $isScheduled = false;
        $indexerId = 'tudock_todaysmessage_indexer';
        try {
            $indexer = $this->indexerRegistry->get($indexerId);
            $isScheduled = $indexer->isScheduled();
        } catch (\InvalidArgumentException $e) {
            
        }
        return $isScheduled;
    }

}
