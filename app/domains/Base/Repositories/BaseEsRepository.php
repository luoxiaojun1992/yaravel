<?php

namespace App\Domains\Base\Repositories;

use Elasticsearch;

class BaseEsRepository
{
    //Elasticsearch Index
    protected $index;

    //Elasticsearch Type
    protected $type;

    //Elasticsearch Connection Name
    protected $connection;

    public function __construct()
    {
        \Registry::set(static::class, $this);
    }

    /**
     * @param mixed $body
     * @param int $mid
     * @return array
     */
    public function search($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        return $esClient->search($params);
    }

    /**
     * @param mixed $body
     * @param int $mid
     * @return array
     */
    public function count($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        return $esClient->count($params);
    }

    /**
     * @param $body
     * @param int $mid
     * @return array
     */
    public function getScrollId($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body,
            'scroll' => '10m'
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        $esResults =  $esClient->search($params);
        if(isset($esResults["_scroll_id"])){
            return [
                'result' => $esResults['hits']['hits'],
                'scroll_id'=> $esResults["_scroll_id"],
                'count'=> $esResults['hits']['total']
            ];
        }

        return [];
    }

    /**
     * Get es index and client
     *
     * @return array
     */
    protected function getEsInfo()
    {
        //Default es info
        if (is_null($this->connection)) {
            $connection = config('elasticsearch.defaultConnection') ?: 'default';
        } else {
            $connection = $this->connection;
        }
        $configTpl = config('elasticsearch.connections.' . $connection);
        //replace index with default index if repo index not be set
        if (is_null($index = $this->index)) {
            $index = $configTpl['index'] ?? null;
        }

        $esClient = Elasticsearch::connection($connection);

        return compact('index', 'esClient');
    }
}
