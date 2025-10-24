<?php
declare(strict_types=1);

namespace App\Services\Crypto;

use GMP;

final class Keccak
{
    private const KECCAK_ROUNDS = 24;

    /** @var int[] */
    private static array $rotc = [
        1, 3, 6, 10, 15, 21, 28, 36, 45, 55,
        2, 14, 27, 41, 56, 8, 25, 43, 62, 18,
        39, 61, 20, 44,
    ];

    /** @var int[] */
    private static array $piln = [
        10, 7, 11, 17, 18, 3, 5, 16, 8, 21,
        24, 4, 15, 23, 19, 13, 12, 2, 20, 14,
        22, 9, 6, 1,
    ];

    /** @var GMP|null */
    private static ?GMP $mod = null;

    /** @var GMP[] */
    private static array $pow2 = [];

    /** @var GMP[]|null */
    private static ?array $roundConstants = null;

    public static function hash(string $message, int $bits = 256): string
    {
        $rate = 1600 - $bits * 2;
        $rateInBytes = intdiv($rate, 8);
        $blockSize = 0;
        $state = array_fill(0, 25, gmp_init(0));
        $offset = 0;
        $inputLength = strlen($message);

        while ($offset < $inputLength) {
            $blockSize = min($inputLength - $offset, $rateInBytes);
            $chunk = substr($message, $offset, $blockSize);
            self::absorbBlock($state, $chunk);
            $offset += $blockSize;
            if ($blockSize === $rateInBytes) {
                self::keccakf($state);
                $blockSize = 0;
            }
        }

        $pad = str_repeat("\0", $rateInBytes);
        $pad = substr_replace($pad, chr(0x01), $blockSize, 1);
        $lastIndex = $rateInBytes - 1;
        $pad[$lastIndex] = chr(ord($pad[$lastIndex]) ^ 0x80);
        self::absorbBlock($state, $pad);
        self::keccakf($state);

        $output = '';
        $targetBytes = intdiv($bits, 8);
        while (strlen($output) < $targetBytes) {
            for ($i = 0; $i < $rateInBytes && strlen($output) < $targetBytes; $i += 8) {
                $lane = $state[$i >> 3];
                $bytes = self::laneToBytes($lane);
                $needed = $targetBytes - strlen($output);
                $output .= substr($bytes, 0, min(8, $needed));
            }
            if (strlen($output) >= $targetBytes) {
                break;
            }
            self::keccakf($state);
        }

        return bin2hex(substr($output, 0, $targetBytes));
    }

    /**
     * @param GMP[] $state
     */
    private static function absorbBlock(array &$state, string $block): void
    {
        $length = strlen($block);
        for ($i = 0; $i < $length; $i += 8) {
            $laneBytes = substr($block, $i, min(8, $length - $i));
            $laneBytes = str_pad($laneBytes, 8, "\0", STR_PAD_RIGHT);
            $lane = gmp_import($laneBytes, 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
            $index = intdiv($i, 8);
            $state[$index] = gmp_xor($state[$index], $lane);
        }
    }

    private static function mod(): GMP
    {
        if (self::$mod === null) {
            self::$mod = gmp_pow(gmp_init(2), 64);
        }
        return self::$mod;
    }

    private static function pow2(int $shift): GMP
    {
        if (!isset(self::$pow2[$shift])) {
            self::$pow2[$shift] = gmp_pow(gmp_init(2), $shift);
        }
        return self::$pow2[$shift];
    }

    private static function mask64(GMP $value): GMP
    {
        return gmp_mod($value, self::mod());
    }

    private static function rotl(GMP $value, int $shift): GMP
    {
        $shift &= 63;
        if ($shift === 0) {
            return self::mask64($value);
        }
        $value = self::mask64($value);
        $left = gmp_mod(gmp_mul($value, self::pow2($shift)), self::mod());
        $right = gmp_div_q($value, self::pow2(64 - $shift));
        return self::mask64(gmp_add($left, $right));
    }

    /**
     * @param GMP[] $state
     */
    private static function keccakf(array &$state): void
    {
        $roundConstants = self::roundConstants();
        $bc = array_fill(0, 5, gmp_init(0));

        for ($round = 0; $round < self::KECCAK_ROUNDS; $round++) {
            for ($i = 0; $i < 5; $i++) {
                $bc[$i] = self::mask64(gmp_xor(gmp_xor(gmp_xor(gmp_xor(
                    $state[$i],
                    $state[$i + 5]
                ), $state[$i + 10]), $state[$i + 15]), $state[$i + 20]));
            }

            for ($i = 0; $i < 5; $i++) {
                $t = gmp_xor($bc[($i + 4) % 5], self::rotl($bc[($i + 1) % 5], 1));
                for ($j = 0; $j < 25; $j += 5) {
                    $state[$j + $i] = self::mask64(gmp_xor($state[$j + $i], $t));
                }
            }

            $t = $state[1];
            for ($i = 0; $i < 24; $i++) {
                $j = self::$piln[$i];
                $temp = $state[$j];
                $state[$j] = self::rotl($t, self::$rotc[$i]);
                $t = $temp;
            }

            for ($j = 0; $j < 25; $j += 5) {
                for ($i = 0; $i < 5; $i++) {
                    $bc[$i] = $state[$j + $i];
                }
                for ($i = 0; $i < 5; $i++) {
                    $state[$j + $i] = self::mask64(gmp_xor(
                        $bc[$i],
                        gmp_and(
                            self::mask64(gmp_com($bc[($i + 1) % 5])),
                            $bc[($i + 2) % 5]
                        )
                    ));
                }
            }

            $state[0] = self::mask64(gmp_xor($state[0], $roundConstants[$round]));
        }
    }

    /**
     * @return GMP[]
     */
    private static function roundConstants(): array
    {
        if (self::$roundConstants !== null) {
            return self::$roundConstants;
        }

        $hex = [
            '0x0000000000000001', '0x0000000000008082', '0x800000000000808a', '0x8000000080008000',
            '0x000000000000808b', '0x0000000080000001', '0x8000000080008081', '0x8000000000008009',
            '0x000000000000008a', '0x0000000000000088', '0x0000000080008009', '0x000000008000000a',
            '0x000000008000808b', '0x800000000000008b', '0x8000000000008089', '0x8000000000008003',
            '0x8000000000008002', '0x8000000000000080', '0x000000000000800a', '0x800000008000000a',
            '0x8000000080008081', '0x8000000000008080', '0x0000000080000001', '0x8000000080008008',
        ];

        self::$roundConstants = array_map(static fn(string $value) => gmp_init($value, 16), $hex);
        return self::$roundConstants;
    }

    private static function laneToBytes(GMP $lane): string
    {
        $bytes = gmp_export(self::mask64($lane), 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        if ($bytes === false) {
            $bytes = '';
        }
        return str_pad($bytes, 8, "\0", STR_PAD_RIGHT);
    }
}
