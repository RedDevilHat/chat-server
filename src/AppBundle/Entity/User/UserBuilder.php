<?php
/**
 * Created by PhpStorm.
 * User: KustovVA
 * Date: 23.05.2016
 * Time: 11:05
 */

namespace CommonBundle\Entity\User;


class UserBuilder
{
    private $id;

    private $username;

    private $gender;

    private $birthday;

    private $zodiac;

    private $location;

    private $isPrivate;

    private $isActive;

    private $password, $salt;

    private $roles;

    private $facebookId;

    private $googleId;

    private $vkontakteId;

    private $odnoklassnikiId;

    private $shortCode;

    private $phoneNumber;

    private $accessibilityStatus;

    private $avatar;

    private $deletedAt;


    /**
     * @return User
     */
    public function create()
    {
        return new User(
            $this->id,
            $this->username,
            $this->gender,
            $this->birthday,
            $this->zodiac,
            $this->location,
            $this->isPrivate,
            $this->isActive,
            $this->password,
            $this->salt,
            $this->roles,
            $this->facebookId,
            $this->googleId,
            $this->vkontakteId,
            $this->odnoklassnikiId,
            $this->shortCode,
            $this->phoneNumber,
            $this->accessibilityStatus,
            $this->avatar,
            $this->deletedAt
        );
    }

    /**
     * @param mixed $id
     * @return UserBuilder
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param mixed $username
     * @return UserBuilder
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param mixed $gender
     * @return UserBuilder
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }


    /**
     * @param mixed $birthday
     * @return UserBuilder
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @param mixed $zodiac
     * @return UserBuilder
     */
    public function setZodiac($zodiac)
    {
        $this->zodiac = $zodiac;

        return $this;
    }

    /**
     * @param $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param $isPrivate
     * @return $this
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    /**
     * @param $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }


    /**
     * @param mixed $password
     * @return UserBuilder
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param mixed $salt
     * @return UserBuilder
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param mixed $roles
     * @return UserBuilder
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param $facebookId
     * @return UserBuilder
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * @param $googleId
     * @return UserBuilder
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @param $vkontakteId
     * @return UserBuilder
     */
    public function setVkontakteId($vkontakteId)
    {
        $this->vkontakteId = $vkontakteId;

        return $this;
    }

    /**
     * @param $odnoklassnikiId
     * @return UserBuilder
     */
    public function setOdnoklassnikiId($odnoklassnikiId)
    {
        $this->odnoklassnikiId = $odnoklassnikiId;
        return $this;
    }

    /**
     * @param int $shortCode
     * @return $this
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    /**
     * @param mixed $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @param mixed $accessibilityStatus
     * @return $this
     */
    public function setAccessibilityStatus($accessibilityStatus)
    {
        $this->accessibilityStatus = $accessibilityStatus;

        return $this;
    }

    /**
     * @param $deletedAt
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @param User $user
     * @return UserBuilder
     */
    public function fillFromUser(User $user)
    {
        $this->setRoles($user->getRoles())
            ->setPassword($user->getPassword())
            ->setId($user->getId())
            ->setSalt($user->getSalt())
            ->setUsername($user->getUsername())
            ->setGender($user->getGender())
            ->setBirthday($user->getBirthday())
            ->setZodiac($user->getZodiac())
            ->setLocation($user->getLocation())
            ->setIsPrivate($user->getIsPrivate())
            ->setIsActive($user->getIsActive())
            ->setFacebookId($user->getFacebookId())
            ->setGoogleId($user->getGoogleId())
            ->setVkontakteId($user->getVkontakteId())
            ->setOdnoklassnikiId($user->getOdnoklassnikiId())
            ->setShortCode($user->getShortCode())
            ->setPhoneNumber($user->getPhoneNumber())
            ->setAccessibilityStatus($user->getAccessibilityStatus())
            ->setAvatar($user->getAvatar())
            ->setDeletedAt($user->getDeletedAt());

    }


    /**
     * @param array $user
     * @return UserBuilder
     */
    public function fillFromArray(array $user){
        $this->setRoles($user['roles'])
            ->setPassword($user['password'])
            ->setId($user['id'])
            ->setSalt($user['salt'])
            ->setUsername($user['username'])
            ->setGender($user['gender'])
            ->setBirthday($user['birthday'])
            ->setZodiac($user['zodiac_id'])
            ->setLocation($user['city_id'])
            ->setIsPrivate($user['is_private'])
            ->setIsActive($user['is_active'])
            ->setFacebookId($user['facebook_id'])
            ->setGoogleId($user['google_id'])
            ->setVkontakteId($user['vkontakte_id'])
            ->setOdnoklassnikiId($user['odnoklassniki_id'])
            ->setShortCode($user['short_code'])
            ->setPhoneNumber($user['phone_number'])
            ->setAvatar($user['avatar'])
            ->setAccessibilityStatus($user['accessibility_status'])
            ->setDeletedAt($user['deleted_at']);

        return $this;
    }
}