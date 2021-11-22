<?php

declare(strict_types=1);

namespace Lhp\JigsawPuzzleVerify;


use HyperfExt\Jwt\Jwt;
use HyperfExt\Jwt\Manager;

/**
 * 验证
 * @package Lhp\JigsawPuzzleVerify
 */
class Verify
{
    public static $key = 'acb04b7e103a0cd8b54763051cef18bc55abe029fdebae5e1d417e2ffb2a00a2';

    public Image $image;

    public function __construct()
    {
        $this->image = new Image();
    }

    /**
     * @param string $ip
     * @return array|bool
     */
    public function getNewImage(string $ip): array|bool
    {
        $image = $this->image->createImage();
        $key = uniqid('v').time().mt_rand(1,1000000);
        $key = $key.'_'.$image['concavePosition'][0].'_'.time();

        $key = $this->encrypt($key);
        if ( ! $key) {
            return false;
        }

        $image['concaveHeight'] = $image['concavePosition'][1];
        $image['key'] = $image['concavePosition'][1];
        unset($image['concavePosition']);
        return $image;
    }

    /**
     * 验证
     * @param string $key   key
     * @param float $x  选择的宽度
     * @param int $expire key的失效时间
     * @param int $diff 正确的差值范围
     * @return bool
     */
    public function verify(string $key, float $x, int $expire = 120, $diff = 2): bool
    {
       $key = $this->decrypt($key);
       if ( ! $key) {
          return false;
       }

       $tA = explode('_', $key);
       if ( ! isset($tA[2]) ||  ! isset($tA[3])) {
           return false;
       }

       if (time() - $tA[2] > $expire) {
           new \Exception('已过期', 502);
       }

       $_diff = abs($x - $tA[1]);
       if ($_diff > $diff) {
           return false;
       }

       return true;
    }

    /**
     * 加密
     * @param string $data
     * @return bool|string
     */
    public function encrypt(string $data): bool|string
    {
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        return openssl_encrypt($data, $cipher, static::$key, $options=0, $iv);

    }

    /**
     * 加密
     * @param string $data
     * @return bool|string
     */
    public function decrypt(string $data): bool|string
    {
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        return openssl_decrypt($data, $cipher, static::$key, $options=0, $iv);
    }
}