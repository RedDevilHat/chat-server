<?php
/**
 * Created by PhpStorm.
 * User: KustovVA
 * Date: 23.05.2016
 * Time: 11:01
 */

namespace CommonBundle\Entity\User;


use CommonBundle\Entity\Location\City\City;
use CommonBundle\Entity\Questionnaire\Questionnaire;
use CommonBundle\Entity\Zodiac\Zodiac;
use CommonBundle\Entity\File\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package CommonBundle\Entity\User
 */
class User implements UserInterface
{
    const ROLE_NORMAL = ['ROLE_NORMAL'];
    const ROLE_VIP = ['ROLE_VIP'];
    const ROLE_ADMIN = ['ROLE_ADMIN'];

    const ACCESS_ACTIVE = 'ACTIVE';
    const ACCESS_BLOCKED ='BLOCKED';
    const ACCESS_DELETED = 'DELETED';


    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", nullable=false)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;

    /**
     * @var \DateTime
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var Zodiac
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\Zodiac\Zodiac", fetch="EAGER")
     * @ORM\JoinColumn(name="zodiac_id", nullable=true, onDelete="CASCADE")
     */
    private $zodiac;

    /**
     * @var City
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\Location\City\City", fetch="EAGER")
     * @ORM\JoinColumn(name="city_id", nullable=true, onDelete="CASCADE")
     */
    private $location;

    /**
     * @var bool
     * @ORM\Column(name="is_private", type="boolean", nullable=true)
     */
    private $isPrivate;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", nullable=true)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(name="salt", type="string", nullable=true)
     */
    private $salt;

    /**
     * @var array
     * @ORM\Column(name="roles", type="json_array", nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(name="vkontakte_id", type="string", length=255, nullable=true)
     */
    private $vkontakteId;

    /**
     * @ORM\Column(name="odnoklassniki_id", type="string", length=255, nullable=true)
     */
    private $odnoklassnikiId;

    /**
     * @ORM\Column(name="short_code", type="string", length=4, nullable=true)
     */
    private $shortCode;

    /**
     * @ORM\Column(name="phone_number", type="string", length=20, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(name="accessibility_status", type="string", length=20, nullable=true)
     */
    private $accessibilityStatus;

    /**
     * @var File
     * @ORM\OneToOne(targetEntity="CommonBundle\Entity\File\File", fetch="EAGER")
     * @ORM\JoinColumn(name="avatar", nullable=true, onDelete="SET NULL")
     */
    private $avatar;

    /**
     * @var array
     * @ORM\Column(name="accessuars", nullable=true, type="array")
     */
    private $accessuars;

    /**
     * @var \DateTime
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;



    /**
     * @return UserBuilder
     */
    public static function build(User $user = null)
    {
        $builder = new UserBuilder();
        if ($user instanceof User) {
            $builder->fillFromUser($user);
        }
        return $builder;
    }

    /**
     * User constructor.
     * @param $id
     * @param $username
     * @param $gender
     * @param $birthday
     * @param $zodiac
     * @param $location
     * @param $isPrivate
     * @param $isActive
     * @param $password
     * @param $salt
     * @param $roles
     * @param $facebookId
     * @param $googleId
     * @param $vkontakteId
     * @param $odnoklassnikiId
     * @param $shortCode
     * @param $phoneNumber
     * @param $accessibilityStatus
     * @param $avatar
     * @param $deletedAt
     */
    public function __construct($id, $username, $gender, $birthday, $zodiac, $location, $isPrivate, $isActive, $password, $salt, $roles, $facebookId, $googleId, $vkontakteId, $odnoklassnikiId, $shortCode, $phoneNumber, $accessibilityStatus, $avatar, $deletedAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->gender = $gender;
        $this->birthday = $birthday;
        $this->zodiac = $zodiac;
        $this->location = $location;
        $this->isPrivate = $isPrivate;
        $this->isActive = $isActive;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->facebookId = $facebookId;
        $this->googleId = $googleId;
        $this->vkontakteId = $vkontakteId;
        $this->odnoklassnikiId = $odnoklassnikiId;
        $this->shortCode = $shortCode;
        $this->phoneNumber = $phoneNumber;
        $this->accessibilityStatus = $accessibilityStatus;
        $this->avatar = $avatar;
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the user gender.
     *
     * @return string The gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return Zodiac
     */
    public function getZodiac()
    {
        return $this->zodiac;
    }

    /**
     * @return City
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }


    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {

    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @return mixed
     */
    public function getVkontakteId()
    {
        return $this->vkontakteId;
    }

    /**
     * @return mixed
     */
    public function getOdnoklassnikiId()
    {
        return $this->odnoklassnikiId;
    }

    /**
     * @return mixed
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getAccessibilityStatus()
    {
        return $this->accessibilityStatus;
    }

    /**
     * @return File
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}