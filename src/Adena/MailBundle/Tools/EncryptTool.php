<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 4/17/2017
 * Time: 2:33 PM
 *
 * This tool is used as a service to encrypt and decrypt strings
 */

namespace Adena\MailBundle\Tools;


class EncryptTool
{

    private $key;

    public function __construct( $key = "testKey" )
    {
        $this->key = $key;
    }

    /**
     * @param $data
     *
     * @return string
     */
    function encrypt($data) {

        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($this->key);

        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

        // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param $data
     *
     * @return string
     */
    function decrypt($data) {

        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($this->key);

        // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
}