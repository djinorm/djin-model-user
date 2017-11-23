<?php
/**
 * Created for djin-model-user.
 * Datetime: 22.11.2017 10:57
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace DjinORM\Models\User;


use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{

    const PASSWORD = '0123456789';

    /** @var Password */
    private $password;

    public function setUp()
    {
        $this->password = new Password(self::PASSWORD);
    }

    public function testConstructShortPassword()
    {
        $this->expectException(PasswordException::class);
        new Password('');
    }

    public function testGetPassword()
    {
        $this->assertEquals(self::PASSWORD, $this->password->getPassword());
    }

    public function testGetPasswordHash()
    {
        $this->assertNotEmpty($this->password->getPasswordHash());
    }

    public function testVerify()
    {
        $password = new Password(self::PASSWORD);
        $this->assertTrue($this->password->verify($password->getPasswordHash()));
    }

    public function testRandom()
    {
        $password = Password::random();
        $this->assertInstanceOf(Password::class, $password);
        $this->assertGreaterThan(6, strlen($password->getPassword()));
    }

}