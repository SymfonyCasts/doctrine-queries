<?php

namespace App\Factory;

use App\Entity\FortuneCookie;
use App\Repository\FortuneCookieRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<FortuneCookie>
 *
 * @method static FortuneCookie|Proxy createOne(array $attributes = [])
 * @method static FortuneCookie[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static FortuneCookie[]|Proxy[] createSequence(array|callable $sequence)
 * @method static FortuneCookie|Proxy find(object|array|mixed $criteria)
 * @method static FortuneCookie|Proxy findOrCreate(array $attributes)
 * @method static FortuneCookie|Proxy first(string $sortedField = 'id')
 * @method static FortuneCookie|Proxy last(string $sortedField = 'id')
 * @method static FortuneCookie|Proxy random(array $attributes = [])
 * @method static FortuneCookie|Proxy randomOrCreate(array $attributes = [])
 * @method static FortuneCookie[]|Proxy[] all()
 * @method static FortuneCookie[]|Proxy[] findBy(array $attributes)
 * @method static FortuneCookie[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static FortuneCookie[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static FortuneCookieRepository|RepositoryProxy repository()
 * @method FortuneCookie|Proxy create(array|callable $attributes = [])
 */
final class FortuneCookieFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'fortune' => self::faker()->text(),
            'numberPrinted' => self::faker()->randomNumber(),
            'discontinued' => self::faker()->boolean(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(FortuneCookie $fortuneCookie): void {})
        ;
    }

    protected static function getClass(): string
    {
        return FortuneCookie::class;
    }
}
