<?php

namespace App\Support;

class AES
{
    /**
     * AES/PKCS5Padding Encrypter
     *
     * @param $str
     * @param $key
     * @param $algo
     * @param $encoding
     * @return string
     */
    public static function encrypt($str, $key, $algo = 'AES-128-ECB', $encoding = 'base64')
    {
        $encryptedBin = openssl_encrypt($str, $algo, $key, OPENSSL_RAW_DATA);

        if (!$encryptedBin) {
            \Log::error('aes:encrypt:str:' . $str);
            \Log::error('aes:encrypt:key:' . $key);
            \Log::error('aes:encrypt:algo:' . $algo);
            \Log::error('aes:encrypt:error:' . openssl_error_string());
            return $encryptedBin;
        }

        switch ($encoding) {
            case 'bin2hex':
                return bin2hex($encryptedBin);
            case 'base64':
            default:
                return base64_encode($encryptedBin);
        }
    }

    /**
     * AES/PKCS5Padding Decrypter
     *
     * @param $encryptedStr
     * @param $key
     * @param $algo
     * @param $encoding
     * @return string
     */
    public static function decrypt($encryptedStr, $key, $algo = 'AES-128-ECB', $encoding = 'base64')
    {
        switch ($encoding) {
            case 'hex2bin':
                $encryptedStr = hex2bin($encryptedStr);
                break;
            case 'base64':
            default:
                $encryptedStr = base64_decode($encryptedStr);
        }

        $decryptedStr = openssl_decrypt($encryptedStr, $algo, $key, OPENSSL_RAW_DATA);
        if (!$decryptedStr) {
            \Log::error('aes:decrypt:str:' . $encryptedStr);
            \Log::error('aes:decrypt:key:' . $key);
            \Log::error('aes:decrypt:algo:' . $algo);
            \Log::error('aes:decrypt:encoding:' . $encoding);
            \Log::error('aes:decrypt:error:' . openssl_error_string());
        }

        return $decryptedStr;
    }
}
