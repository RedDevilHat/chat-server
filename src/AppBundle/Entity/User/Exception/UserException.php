<?php
/**
 * Created by PhpStorm.
 * User: BarkhatovaEA
 * Date: 31.05.2016
 * Time: 12:31
 */

namespace CommonBundle\Entity\User\Exception;

/**
 * Class UserException
 * @package CommonBundle\Entity\User\Exception
 */
class UserException extends \Exception
{
    /**
     * @return UserException
     */
    public static function canNotCreateUserException(array $params)
    {
        return new UserException();
    }

    public static function canNotFindUser($searchParams)
    {
        return new UserException();
    }

    public static function canNotUpdateUser(array $params)
    {
        return new UserException();
    }

}