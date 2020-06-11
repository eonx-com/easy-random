<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\Exceptions\InvalidAlphabetException;
use EonX\EasyRandom\Exceptions\InvalidAlphabetNameException;
use EonX\EasyRandom\Exceptions\InvalidRandomStringException;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\Tests\Stubs\AlwaysInvalidRandomStringConstraintStub;
use EonX\EasyRandom\Tests\Stubs\AlwaysValidRandomStringConstraintStub;

final class RandomStringTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestRandomString(): iterable
    {
        yield 'Default configs' => [];

        foreach (RandomStringInterface::ALPHABET_NAMES as $name) {
            yield \sprintf('Exclude %s', $name) => [
                null,
                static function (RandomStringInterface $randomString) use ($name): void {
                    $randomString->{\sprintf('exclude%s', \ucfirst($name))}();
                },
                static function (string $randomString) use ($name): void {
                    self::assertAlphabetExcluded($name, $randomString);
                },
            ];

            yield \sprintf('Include only %s', $name) => [
                null,
                static function (RandomStringInterface $randomString) use ($name): void {
                    $randomString
                        ->clear()
                        ->{\sprintf('include%s', \ucfirst($name))}();
                },
                static function (string $randomString) use ($name): void {
                    self::assertIncludesOnly(RandomStringInterface::ALPHABETS[$name], $randomString);
                },
            ];
        }

        yield 'Override alphabet' => [
            null,
            static function (RandomStringInterface $randomString): void {
                $randomString->alphabet('EONX');
            },
            static function (string $randomString): void {
                self::assertIncludesOnly('EONX', $randomString);
            },
        ];

        yield 'User friendly' => [
            null,
            static function (RandomStringInterface $randomString): void {
                $randomString
                    ->constraints([new AlwaysValidRandomStringConstraintStub()])
                    ->maxAttempts(10)
                    ->userFriendly();
            },
            static function (string $randomString): void {
                self::assertAlphabetExcluded(RandomStringInterface::AMBIGUOUS, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::LOWERCASE, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SYMBOL, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SIMILAR, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::VOWEL, $randomString);
            },
        ];
    }

    public function testInvalidAlphabetExceptionThrown(): void
    {
        $this->expectException(InvalidAlphabetException::class);

        (new RandomGenerator())
            ->randomString(8)
            ->alphabet('')
            ->__toString();
    }

    public function testInvalidAlphabetNameExceptionThrown(): void
    {
        $this->expectException(InvalidAlphabetNameException::class);

        (new RandomGenerator())
            ->randomString(8)
            ->exclude('invalid')
            ->__toString();
    }

    public function testInvalidRandomStringExceptionThrown(): void
    {
        $this->expectException(InvalidRandomStringException::class);

        (new RandomGenerator())
            ->randomString(8)
            ->constraints([new AlwaysInvalidRandomStringConstraintStub()])
            ->__toString();
    }

    /**
     * @dataProvider providerTestRandomString
     */
    public function testRandomString(?int $length = null, ?callable $configure = null, ?callable $assert = null): void
    {
        $iterations = $iterations ?? 100;
        $length = $length ?? 100;
        $generator = new RandomGenerator();
        $generated = [];

        for ($i = 0; $i < $iterations; $i++) {
            $randomString = $generator->randomString($length);

            if ($configure !== null) {
                $configure($randomString);
            }

            $randomStringToString = (string)$randomString;

            self::assertEquals($randomStringToString, $randomString->__toString());
            self::assertEquals($length, \strlen($randomStringToString));
            self::assertArrayNotHasKey($randomStringToString, $generated);

            if ($assert !== null) {
                $assert($randomStringToString);
            }

            $generated[$randomStringToString] = true;
        }
    }

    private static function assertAlphabetExcluded(string $alphabetName, string $randomString): void
    {
        foreach (\str_split(RandomStringInterface::ALPHABETS[$alphabetName]) as $char) {
            self::assertEquals(0, \strpos($randomString, $char));
        }
    }

    private static function assertIncludesOnly(string $alphabet, string $randomString): void
    {
        $alphabet = \preg_quote($alphabet, '#');

        self::assertEquals(0, \preg_match(\sprintf('#[^%s]#', $alphabet), $randomString));
    }
}
