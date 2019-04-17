<?php
/**
 * Created for djin-model-user.
 * Datetime: 07.08.2018 15:08
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace DjinORM\Models\User;



class UserClass extends User
{
    public static function getModelName(): string
    {
        return 'user';
    }
}