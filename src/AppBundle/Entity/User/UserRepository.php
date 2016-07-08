<?php
/**
 * Created by PhpStorm.
 * User: KustovVA
 * Date: 23.05.2016
 * Time: 11:13
 */

namespace CommonBundle\Entity\User;


use SSPSoftware\ApiTokenBundle\Service\User\ApiKeyUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface UserProvider
 * @package CommonBundle\Entity\User
 */
interface UserRepository extends UserProviderInterface, ApiKeyUserProviderInterface
{
}