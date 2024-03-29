<?php
namespace VuBib\Indexer;

class SolrWriter
{
    protected $solrUrl;

    public function __construct($solrUrl)
    {
        $this->solrUrl = $solrUrl;
    }

    public function deleteRecords(array $ids)
    {
        $formatId = function ($id) {
            return '<id>' . htmlspecialchars($id) . '</id>';
        };
        $deleteDoc = '<delete>'
            . implode('', array_map($formatId, $ids)) . '</delete>';
        $this->save($deleteDoc);
    }

    public function save($xml)
    {
        // use key 'http' even if you send the request to https://...
        $options = [
            'http' => [
                'header'  => "Content-type: text/xml\r\n",
                'method'  => 'POST',
                'content' => $xml,
            ]
        ];
        $context  = stream_context_create($options);
        if (!file_get_contents($this->solrUrl, false, $context)) {
            throw new \Exception("Problem writing document.");
        }
    }
}