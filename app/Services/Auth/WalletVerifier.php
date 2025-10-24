<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Crypto\Keccak;
use GMP;

final class WalletVerifier
{
    private const CURVE_P = '0xfffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f';
    private const CURVE_N = '0xfffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
    private const G_X = '0x79be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
    private const G_Y = '0x483ada7726a3c4655da4fbfc0e1108a8fd17b448a68554199c47d08ffb10d4b8';

    public function verifyEvmSignature(string $address, string $message, string $signature): bool
    {
        $address = strtolower($address);
        if (!preg_match('/^0x[0-9a-f]{40}$/', $address)) {
            return false;
        }

        $signature = strtolower($signature);
        if (str_starts_with($signature, '0x')) {
            $signature = substr($signature, 2);
        }

        if (strlen($signature) !== 130) {
            return false;
        }

        $r = gmp_init(substr($signature, 0, 64), 16);
        $s = gmp_init(substr($signature, 64, 64), 16);
        $v = hexdec(substr($signature, 128, 2));
        if ($v >= 27) {
            $v -= 27;
        }

        if ($v < 0 || $v > 3) {
            return false;
        }

        $prefix = "\x19Ethereum Signed Message:\n" . strlen($message) . $message;
        $hashHex = Keccak::hash($prefix, 256);
        $public = $this->recoverPublicKey($r, $s, $hashHex, $v);
        if ($public === null) {
            return false;
        }

        $derived = $this->publicKeyToAddress($public);
        return $derived === $address;
    }

    /**
     * @return array{0:GMP,1:GMP}|null
     */
    private function recoverPublicKey(GMP $r, GMP $s, string $hashHex, int $recId): ?array
    {
        $n = $this->n();
        $p = $this->p();

        if (gmp_cmp($r, gmp_init(0)) <= 0 || gmp_cmp($r, $n) >= 0) {
            return null;
        }
        if (gmp_cmp($s, gmp_init(0)) <= 0 || gmp_cmp($s, $n) >= 0) {
            return null;
        }

        $x = gmp_add($r, gmp_mul(gmp_init(intdiv($recId, 2)), $n));
        if (gmp_cmp($x, $p) >= 0) {
            return null;
        }

        $R = $this->decompressPoint($x, $recId % 2);
        if ($R === null) {
            return null;
        }

        if (!$this->isOnCurve($R)) {
            return null;
        }

        $rInv = gmp_invert($r, $n);
        if ($rInv === false) {
            return null;
        }

        $e = gmp_init($hashHex, 16);
        $negE = gmp_mod(gmp_neg($e), $n);

        $sr = $this->scalarMultiply($s, $R);
        $eG = $this->scalarMultiply($negE, $this->generator());
        $Q = $this->pointAdd($sr, $eG);
        if ($Q === null) {
            return null;
        }

        $final = $this->scalarMultiply($rInv, $Q);
        return $final;
    }

    private function publicKeyToAddress(array $point): string
    {
        $xHex = str_pad(gmp_strval($point[0], 16), 64, '0', STR_PAD_LEFT);
        $yHex = str_pad(gmp_strval($point[1], 16), 64, '0', STR_PAD_LEFT);
        $binary = hex2bin($xHex . $yHex) ?: '';
        $hash = Keccak::hash($binary, 256);
        return '0x' . substr($hash, -40);
    }

    private function scalarMultiply(GMP $k, ?array $point): ?array
    {
        $n = $this->n();
        $k = gmp_mod($k, $n);
        $result = null;
        $addend = $point;

        while (gmp_cmp($k, gmp_init(0)) > 0) {
            if (gmp_testbit($k, 0)) {
                $result = $this->pointAdd($result, $addend);
            }
            $addend = $this->pointDouble($addend);
            $k = gmp_div_q($k, gmp_init(2));
        }

        return $result;
    }

    private function pointAdd(?array $p, ?array $q): ?array
    {
        if ($p === null) {
            return $q;
        }
        if ($q === null) {
            return $p;
        }

        $pCurve = $this->p();

        if (gmp_cmp($p[0], $q[0]) === 0) {
            if (gmp_cmp(gmp_mod(gmp_add($p[1], $q[1]), $pCurve), gmp_init(0)) === 0) {
                return null;
            }
            return $this->pointDouble($p);
        }

        $den = gmp_sub($q[0], $p[0]);
        $denInv = gmp_invert($den, $pCurve);
        if ($denInv === false) {
            return null;
        }

        $lambda = gmp_mod(gmp_mul(gmp_sub($q[1], $p[1]), $denInv), $pCurve);
        $x = gmp_mod(gmp_sub(gmp_sub(gmp_pow($lambda, 2), $p[0]), $q[0]), $pCurve);
        $y = gmp_mod(gmp_sub(gmp_mul($lambda, gmp_sub($p[0], $x)), $p[1]), $pCurve);

        return [$this->normalize($x), $this->normalize($y)];
    }

    private function pointDouble(?array $p): ?array
    {
        if ($p === null) {
            return null;
        }

        if (gmp_cmp($p[1], gmp_init(0)) === 0) {
            return null;
        }

        $pCurve = $this->p();
        $denInv = gmp_invert(gmp_mul(gmp_init(2), $p[1]), $pCurve);
        if ($denInv === false) {
            return null;
        }

        $lambda = gmp_mod(gmp_mul(gmp_mul(gmp_init(3), gmp_pow($p[0], 2)), $denInv), $pCurve);
        $x = gmp_mod(gmp_sub(gmp_pow($lambda, 2), gmp_mul(gmp_init(2), $p[0])), $pCurve);
        $y = gmp_mod(gmp_sub(gmp_mul($lambda, gmp_sub($p[0], $x)), $p[1]), $pCurve);

        return [$this->normalize($x), $this->normalize($y)];
    }

    private function decompressPoint(GMP $x, int $yBit): ?array
    {
        $p = $this->p();
        $alpha = gmp_mod(gmp_add(gmp_pow($x, 3), gmp_init(7)), $p);
        $beta = gmp_powm($alpha, gmp_div_q(gmp_add($p, gmp_init(1)), gmp_init(4)), $p);

        if (gmp_cmp(gmp_mod($beta, $p), gmp_init(0)) === 0 && gmp_cmp($alpha, gmp_init(0)) !== 0) {
            return null;
        }

        if ((int)gmp_intval(gmp_mod($beta, gmp_init(2))) !== $yBit) {
            $beta = gmp_sub($p, $beta);
        }

        return [$this->normalize($x), $this->normalize($beta)];
    }

    private function isOnCurve(array $point): bool
    {
        $p = $this->p();
        $left = gmp_mod(gmp_pow($point[1], 2), $p);
        $right = gmp_mod(gmp_add(gmp_pow($point[0], 3), gmp_init(7)), $p);
        return gmp_cmp($left, $right) === 0;
    }

    private function normalize(GMP $value): GMP
    {
        $p = $this->p();
        $value = gmp_mod($value, $p);
        if (gmp_cmp($value, gmp_init(0)) < 0) {
            $value = gmp_add($value, $p);
        }
        return $value;
    }

    private function p(): GMP
    {
        return gmp_init(self::CURVE_P, 16);
    }

    private function n(): GMP
    {
        return gmp_init(self::CURVE_N, 16);
    }

    /**
     * @return array{0:GMP,1:GMP}
     */
    private function generator(): array
    {
        return [
            gmp_init(self::G_X, 16),
            gmp_init(self::G_Y, 16),
        ];
    }
}
