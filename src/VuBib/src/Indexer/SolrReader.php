<?php
namespace VuBib\Indexer;

class SolrReader
{
    protected $solrUrl;

    public function __construct($solrUrl)
    {
        $this->solrUrl = $solrUrl;
    }

    public function hasRecord($id)
    {
        // TODO
        return true;
    }
}