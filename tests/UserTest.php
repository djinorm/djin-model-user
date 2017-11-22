<?php
/**
 * Created for djin-model-user.
 * Datetime: 22.11.2017 10:57
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace DjinORM\Models;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FA\Google2FA;

class UserTest extends TestCase
{

    /** @var Password */
    private $password;

    /** @var User */
    private $user;

    public function setUp()
    {
        $this->password = new Password('123456789');
        $this->user = new User('user@example.com', $this->password);
    }

    public function invalidEmailProvider()
    {
        return [
            [''],
            ['not-email@'],
            ['@not-email.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     * @param string $email
     */
    public function testConstructInvalidEmail($email)
    {
        $this->expectException(InvalidEmailException::class);
        new User($email, $this->password);
    }

    public function testGetRegisteredAt()
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $this->user->getRegisteredAt());
        $expected = (new DateTimeImmutable())->format('Y-m-d H:i');
        $actual = $this->user->getRegisteredAt()->format('Y-m-d H:i');
        $this->assertEquals($expected, $actual);
    }

    public function testGetEmail()
    {
        $this->assertEquals('user@example.com', $this->user->getEmail());
    }

    /**
     * @dataProvider invalidEmailProvider
     * @param string $email
     */
    public function testSetInvalidEmail($email)
    {
        $this->expectException(InvalidEmailException::class);
        $this->user->setEmail($email);
    }

    public function testSetEmail()
    {
        $this->user->setEmail('user@gmail.com');
        $this->assertEquals('user@gmail.com', $this->user->getEmail());
    }

    public function testIsValidPassword()
    {
        $password = $this->password;
        $this->assertTrue($this->user->isValidPassword($password));
    }

    public function testIsInvalidPassword()
    {
        $password = new Password('00000000');
        $this->assertFalse($this->user->isValidPassword($password));
    }

    public function testSetPassword()
    {
        $password = new Password('00000000');
        $this->user->setPassword($password);
        $this->assertTrue($this->user->isValidPassword($password));
    }

    public function testIsTwoFactorAuthEnabled()
    {
        $this->assertFalse($this->user->isTwoFactorAuthEnabled());
    }

    public function testSetTwoFactorKey()
    {
        $authenticator = new Google2FA();
        $key = $authenticator->generateSecretKey();
        $this->user->setTwoFactorSecretKey($key);

        $this->assertTrue($this->user->isTwoFactorAuthEnabled());
        $this->assertTrue($this->user->isTwoFactorCodeValid($authenticator->getCurrentOtp($key)));
        $this->assertFalse($this->user->isTwoFactorCodeValid('000000'));

        return $key;
    }

    public function testGenerateRecoveryCodes()
    {
        $codes = $this->user->generateRecoveryCodes(10);
        $this->assertCount(10, $codes);
    }

    public function testUseUpRecoveryCode()
    {
        $codes = $this->user->generateRecoveryCodes(10);
        $this->assertTrue($this->user->useUpRecoveryCode($codes[0]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[1]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[2]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[3]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[4]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[5]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[6]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[7]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[8]));
        $this->assertTrue($this->user->useUpRecoveryCode($codes[9]));
    }

    public function testUseUpInvalidRecoveryCode()
    {
        $this->user->generateRecoveryCodes(10);
        $this->expectException(InvalidRecoveryCode::class);
        $this->user->useUpRecoveryCode('222');
    }

}
