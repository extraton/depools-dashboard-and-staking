<?php namespace App\Crud\Unit\Depool;

use App\Entity\Depool;
use App\Entity\Net;
use Doctrine\ORM\EntityManagerInterface;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Date;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;

class DepoolCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    public const NAME = 'depool';

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Depool::class;
    }

    public function getAccessConditions(string $action): array
    {
        return [];
    }

    public function getReadOneFields(): array
    {
        return [];
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'name',
            'address',
            'link' => function (Depool $depool) {
                return sprintf(
                    "https://%s/accounts?section=details&id=%s",
                    $depool->getNet()->getExplorer(),
                    $depool->getAddress()
                );
            },
            'dateCreate' => new Date('createdTs', Date::FORMAT_DATE_TIME),
            'params' => function (Depool $depool) {
                return [
                    'minStake' => hexdec($depool->getInfo()['minStake']),
                    'validatorAssurance' => hexdec($depool->getInfo()['validatorAssurance']),
                    'participantRewardFraction' => hexdec($depool->getInfo()['participantRewardFraction']),
                    'validatorRewardFraction' => hexdec($depool->getInfo()['validatorRewardFraction']),
                    'balanceThreshold' => hexdec($depool->getInfo()['balanceThreshold']),
                    'validatorWallet' => $depool->getInfo()['validatorWallet'],
                ];
            },
            'stakes' => function (Depool $depool) {
                $stakes = $depool->getStakes();
                $total = '0';
                $items = [];
                foreach ($stakes as $stake) {
                    $total = bcadd($total, hexdec($stake['info']['total']));
                    $items[] = [
                        'address' => $stake['address'],
                        'info' => [
                            'total' => hexdec($stake['info']['total']),
                            'withdrawValue' => hexdec($stake['info']['withdrawValue']),
                            'reinvest' => $stake['info']['reinvest'],
                            'reward' => hexdec($stake['info']['reward']),
                        ],
                    ];
                }
                return [
                    'participantsNum' => count($stakes),
                    'total' => $total,
                    'items' => $items
                ];
            },
        ];
    }
}
