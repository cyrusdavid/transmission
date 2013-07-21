<?php namespace Vohof;

/**
 * @package  Transmission
 * @author Cyrus David <david@jcyr.us>
 *
 * @link(RPC Protocol Specification, https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt)
 */
class Transmission {
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Vohof\ClientInterface
     */
    private $client;

    public function __construct(array $config, ClientAbstract $client = null)
    {
        $this->config = $config;

        foreach(array('host', 'endpoint') as $requirement)
        {
            if ( ! isset($config[$requirement]))
            {
                throw new \InvalidArgumentException("Missing argument: $requirement");
            }
        }

        if (is_null($client))
        {
            $options = array();

            if (isset($config['username']) and isset($config['password']))
            {
                $options = array(
                    'request.options' => array(
                        'auth' => array($config['username'], $config['password'])
                    )
                );
            }

            $this->client = new GuzzleClient($config['host'], $options);
        }
        else
        {
            $this->client = $client;
        }

        $this->client->setEndpoint($config['endpoint']);
    }

    public function add($url, $isEncoded = false, $options = array())
    {
        $options[$isEncoded ? 'metainfo' : 'filename'] = $url;

        return $this->client->request('torrent-add', $options);
    }

    public function action($action, $ids)
    {
        $options['ids'] = (array) $ids;

        return $this->client->request("torrent-{$action}", $options);
    }

    public function set($ids, $options)
    {
        $options['ids'] = (array) $ids;

        return $this->client->request('torrent-set', $options);
    }

    public function get($ids, $fields)
    {
        $options = array(
            'fields' => $fields
        );

        if ($ids != 'all')
        {
            $options['ids'] = (array) $ids;
        }

        return $this->client->request('torrent-get', $options);
    }

    public function remove($ids, $deleteData = false)
    {
        $options = array(
            'ids' => (array) $ids,
            'delete-local-data' => $deleteData
        );

        return $this->client->request('torrent-remove', $options);
    }

    // TODO: torrent-rename-path, blocklist-update

    public function session($key = null, $value = null)
    {
        if (! is_null($key))
        {
            return $this->client->request('session-set', array($key => $value));
        }

        return $this->client->request('session-get');
    }

    public function getStats()
    {
        return $this->client->request('session-stats');
    }

    public function relocate($ids, $location, $move = false)
    {
        $options = array(
            'ids' => (array) $ids,
            'location' => $location,
            'move' => $move
        );

        return $this->client->request('torrent-set-location', $options);
    }

    public function isPeerPortOpen()
    {
        return $this->client->request('port-test');
    }

    public function close()
    {
        return $this->client->request('session-close');
    }

    public function done()
    {
        return $this->close();
    }

    public function queue($ids, $where)
    {
        if ( ! in_array($where, array('top', 'bottom', 'up', 'down')))
        {
            throw new \InvalidArgumentException("Invalid queue direction: ${where}");
        }

        return $this->client->request(
            "queue-move-${where}", array('ids' => (array) $ids)
        );
    }

    public function getFreeSpace($path = '/')
    {
        return $this->client->request('free-space', array('path' => $path));
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getClient()
    {
        return $this->client;
    }
}
