<?php
/**
 * Script for exporting data to VuFind's solr index
 */
declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use VuBib\Indexer\VuFindDeleter;
use VuBib\Indexer\SolrReader;
use VuBib\Indexer\SolrWriter;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    die("Please provide Solr update URL on command line, e.g. 'http://localhost:8983/solr/biblio/update'");
}
$solrUpdateUrl = $argv[1];
$solrQueryUrl = str_replace('/update', '/select', $solrUpdateUrl);

$container = include __DIR__ . '/../config/container.php';

$adapter = $container->get(Adapter::class);
$writer = new SolrWriter($solrUpdateUrl);
$reader = new SolrReader($solrQueryUrl);
$deleter = new VuFindDeleter($writer, $adapter, $reader);
$deleter->delete();

exit(0);
