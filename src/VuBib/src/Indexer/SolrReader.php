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
        $params = [
            'q' => 'id:"' . addcslashes($id, '"') . '"',
            'wt' => 'json',
        ];
        $url = $this->solrUrl . '?' . http_build_query($params);
        $result = json_decode(file_get_contents($url));
        return ($result->response->numFound ?? 0) === 1;
    }
}