<?php
/**
 * Created for djin-model-user.
 * Datetime: 22.11.2017 10:37
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace DjinORM\Models;


class Password
{

    private $password;
    private $hash;

    public function __construct(string $password)
    {
        $this->guardShort($password);
        $this->password = $password;
        $this->hash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPasswordHash()
    {
        return $this->hash;
    }

    public function verify(string $hash)
    {
        return password_verify($this->password, $hash);
    }

    public static function random(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    private function guardShort($password)
    {
        if (strlen($password) < 6) {
            throw new PasswordException('Password length should be great than 6 chars');
        }
    }

}