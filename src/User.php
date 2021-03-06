<?php
/**
 * Created for djin-model-user.
 * Datetime: 22.11.2017 10:29
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace DjinORM\Models\User;


use DateTimeImmutable;
use DjinORM\Djin\Id\Id;
use DjinORM\Djin\Model\ModelInterface;
use DjinORM\Djin\Model\ModelTrait;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Recovery\Recovery;
use Respect\Validation\Validator;
use XAKEPEHOK\Password\Password;

abstract class User implements ModelInterface
{

    use ModelTrait;

    /** @var Id */
    protected $id;

    /** @var DateTimeImmutable */
    protected $registeredAt;

    /** @var string */
    protected $email;

    /** @var string{60} */
    protected $password;

    /** @var string */
    protected $twoFactorSecretKey = '';

    /** @var array */
    protected $recoveryCodes = [];

    /**
     * User constructor.
     * @param string $email
     * @param Password $password
     * @throws InvalidEmailException
     * @throws \Exception
     */
    public function __construct(string $email, Password $password)
    {
        $this->registeredAt = new DateTimeImmutable();
        $this->setEmail($email);
        $this->password = $password->getPasswordHash();
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @throws InvalidEmailException
     */
    public function setEmail(string $email)
    {
        if (!Validator::email()->validate($email)) {
            throw new InvalidEmailException('User email is invalid');
        }

        $this->email = $email;
    }

    public function isValidPassword(Password $password): bool
    {
        return $password->verify($this->password);
    }

    public function setPassword(Password $password)
    {
        $this->password = $password->getPasswordHash();
    }

    public function isTwoFactorAuthEnabled(): bool
    {
        return !empty($this->twoFactorSecretKey);
    }

    public function setTwoFactorSecretKey(string $key)
    {
        $this->twoFactorSecretKey = $key;
    }

    public function isTwoFactorCodeValid(string $code, $window = null): bool
    {
        $authenticator = new Google2FA();
        return $authenticator->verify($code, $this->twoFactorSecretKey, $window);
    }

    public function generateRecoveryCodes(int $count = 10): array
    {
        $recovery = new Recovery();
        $recoveryCodes = $recovery->setCount($count)->toArray();
        $this->recoveryCodes = array_map(function($code){
            return password_hash($code, PASSWORD_BCRYPT);
        }, $recoveryCodes);
        return $recoveryCodes;
    }

    /**
     * Ключи восстановления при утере генератора одноразовых кодов
     * @param string $recoveryCode
     * @return bool
     * @throws InvalidRecoveryCode
     */
    public function useUpRecoveryCode(string $recoveryCode): bool
    {
        if (!empty($this->recoveryCodes)) {
            foreach ($this->recoveryCodes as $i => $hash) {
                if (password_verify($recoveryCode, $hash)) {
                    unset($this->recoveryCodes[$i]);
                    return true;
                }
            }
        }
        throw new InvalidRecoveryCode('Invalid recovery code');
    }

}