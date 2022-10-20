<?php
namespace VuBib\Indexer;

use VuBib\Db\Table\Agent;
use VuBib\Db\Table\Folder;
use VuBib\Db\Table\Work;

/**
 * Class to delete removed records from the VuFind index.
 */
class VuFindDeleter
{
    use ConsoleWriterTrait;

    protected $adapter;
    protected $solr;
    protected $loader;

    public function __construct($solr, $adapter, $loader)
    {
        $this->solr = $solr;
        $this->adapter = $adapter;
        $this->loader = $loader;
    }

    /**
     * Delete obsolete records from the index. Returns count of records deleted.
     *
     * @param string $type     The type of record being deleted (for display only)
     * @param object $table    Table object containing current data on a record type
     * @param string $idPrefix Prefix to attach to ID number for Solr ID
     *
     * @return int
     */
    protected function deleteThings($type, $table, $idPrefix = '')
    {
        // Retrieve all records from the database:
        $paginator = new \Laminas\Paginator\Paginator(
            new \Laminas\Paginator\Adapter\DbTableGateway($table)
        );
        $cnt = $paginator->getTotalItemCount();
        $paginator->setDefaultItemCountPerPage($cnt);

        $this->writeLine("Total $type records: ----- " . $cnt);

        // Initialize counter:
        $deleteCount = 0;

        // Detect gaps in the ID sequence and remove them from Solr if they exist there:
        $expectedId = 0;
        foreach ($paginator as $row) {
            // Is there a gap between the expected and actual next ID in the sequence?
            // If so, delete any Solr records within this gap!
            for ($i = $expectedId; $i < $row['id']; $i++) {
                $solrId = $idPrefix . $i;
                if ($this->loader->hasRecord($solrId)) {
                    $deleteCount++;
                    $this->writeLine("deleting: {$solrId}");
                    $this->solr->deleteRecords([$solrId]);
                }
            }

            // Now that we have processed the current ID, we expect that the next ID should
            // follow the current one.
            $expectedId = $row['id'] + 1;
        }

        $this->writeLine("Total $type records deleted: ----- " . $deleteCount);

        return $deleteCount;
    }

    /**
     * Remove deleted records from the index
     *
     * @return void
     */
    public function delete()
    {
        $work = new Work($this->adapter);
        $agent = new Agent($this->adapter);
        $folder = new Folder($this->adapter);

        // Initialize counter:
        $deleteCount = $this->deleteThings('Work', $work)
            + $this->deleteThings('Agent', $agent, 'agent-')
            + $this->deleteThings('Folder', $folder, 'folder-');

        $this->writeLine("Total Deletes: ----- {$deleteCount}");
    }
}