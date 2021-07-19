<?php

namespace Tudock\TodaysMessage\Model\Indexer;

class CategoryMessage implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface {

    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'tudock_todaysmessage_indexer';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Tudock\TodaysMessage\Helper\TodaysMessage
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
    \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry, \Magento\Framework\App\ResourceConnection $resource, \Tudock\TodaysMessage\Helper\TodaysMessage $helper
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->connection = $resource->getConnection();
        $this->helper = $helper;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */

    public function execute($ids) {

        //code here!
        $this->executeAction($ids);
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */

    public function executeFull() {
        //code here!
        $this->helper->reindexFull();
    }

    /*
     * Works with a set of entity changed (may be massaction)
     */

    public function executeList(array $ids) {
        //code here!
        $this->executeAction($ids);
    }

    /*
     * Works in runtime for a single entity using plugins
     */

    public function executeRow($id) {
        //code here!
        $this->executeAction([$id]);
    }

    /**
     * Execute action for single entity or list of entities
     *
     * @param int[] $ids
     * @return $this
     */
    protected function executeAction($ids) {
        $ids = array_unique($ids);
        $this->helper->reindexRows($ids);
        return $this;
    }

}
