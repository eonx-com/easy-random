<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * @see testUuidGeneratorInstance
     */
    public static function provideConfigsForUuidGenerator(): iterable
    {
        yield 'UUID v4' => [
            'config' => [
                'easy-random' => [
                    'uuid_version' => 4,
                ],
            ],
            'expectedUuidGeneratorClass' => RamseyUuidV4Generator::class,
        ];

        yield 'UUID v6' => [
            'config' => [
                'easy-random' => [
                    'uuid_version' => 6,
                ],
            ],
            'expectedUuidGeneratorClass' => RamseyUuidV6Generator::class,
        ];
    }

    public function testRandomGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGenerator::class, $result);
        self::assertInstanceOf(
            RandomStringGenerator::class,
            self::getPrivatePropertyValue($result, 'randomStringGenerator')
        );
        self::assertInstanceOf(
            RandomIntegerGenerator::class,
            self::getPrivatePropertyValue($result, 'randomIntegerGenerator')
        );
        self::assertInstanceOf(RamseyUuidV6Generator::class, self::getPrivatePropertyValue($result, 'uuidGenerator'));
    }

    public function testRandomIntegerGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomIntegerGeneratorInterface::class);

        self::assertInstanceOf(RandomIntegerGenerator::class, $result);
    }

    public function testRandomStringGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomStringGeneratorInterface::class);

        self::assertInstanceOf(RandomStringGenerator::class, $result);
    }

    /**
     * @param string[] $config
     *
     * @psalm-param class-string $expectedUuidGeneratorClass
     */
    #[DataProvider('provideConfigsForUuidGenerator')]
    public function testUuidGeneratorInstance(array $config, string $expectedUuidGeneratorClass): void
    {
        $sut = $this->getApp($config);

        $result = $sut->get(UuidGeneratorInterface::class);

        self::assertInstanceOf($expectedUuidGeneratorClass, $result);
    }
}
