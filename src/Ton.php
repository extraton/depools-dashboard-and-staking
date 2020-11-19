<?php namespace App;

use Extraton\TonClient\TonClient;

class Ton
{
    private array $clients = [];

    public function getClient($server): TonClient
    {
        if (!array_key_exists($server, $this->clients)) {
            $this->clients[$server] = new TonClient(
                [
                    'network' => [
                        'server_address' => $server
                    ]
                ]
            );
        }

        return $this->clients[$server];
    }
}
