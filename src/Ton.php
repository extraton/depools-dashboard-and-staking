<?php namespace App;

use Extraton\TonClient\TonClient;

class Ton
{
    private const ROUND_END = 1606141148;
    private const VALIDATORS_ELECTED_FOR = 65536;
    private const SECONDS_IN_YEAR = 31536000;

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

    public function compileRoundGrid(int $roundAmount): array
    {
        $grid = [];
        $lastRoundEnd = $this->getLastRoundEndTs();
        for ($i = 0; $i < $roundAmount; $i++) {
            $grid[] = [
                'start' => $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * ($i + 1)),
                'end' => $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * $i),
//                'start' => \DateTime::createFromFormat('U', $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * ($i + 1)))->format('Y-m-d H:i:s'),
//                'end' => \DateTime::createFromFormat('U', $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * $i))->format('Y-m-d H:i:s'),
            ];
        }

        return $grid;
    }

    private function getLastRoundEndTs(): int
    {
        $time = time();
        $lastRoundEnd = Ton::ROUND_END;
        while ($lastRoundEnd + self::VALIDATORS_ELECTED_FOR < $time) {
            $lastRoundEnd += self::VALIDATORS_ELECTED_FOR;
        }

        return $lastRoundEnd;
    }

    public function getRoundsNumPerYear()
    {
        return bcdiv(self::SECONDS_IN_YEAR, self::VALIDATORS_ELECTED_FOR, 0);
    }

    public static function getDecFormHexOrDec($val)
    {
        return preg_match('/^0x/', $val) === 1 ? hexdec($val) : $val;
    }
}
