<?php
/**
 * Script for exporting data to VuFind's solr index
 */
declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use VuBib\Indexer\VuFindIndexer;
use VuBib\Indexer\SolrWriter;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    die("Please provide Solr update URL on command line, e.g. 'http://localhost:8983/solr/biblio/update'");
}
$solrUpdateUrl = $argv[1];

$container = include __DIR__ . '/../config/container.php';

$adapter = $container->get(Adapter::class);
$writer = new SolrWriter($solrUpdateUrl);
$indexer = new VuFindIndexer($writer, $adapter);
$indexer->indexAll();

exit(0);
