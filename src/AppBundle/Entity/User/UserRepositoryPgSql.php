<?php
/**
 * Created by PhpStorm.
 * User: KustovVA
 * Date: 23.05.2016
 * Time: 11:16
 */

namespace CommonBundle\Entity\User;

use CommonBundle\Entity\ApiToken\ApiToken;
use CommonBundle\Entity\File\Exception\FileException;
use CommonBundle\Entity\Gallery\GalleryRepositoryPgSql;
use CommonBundle\Entity\Location\City\City;
use CommonBundle\Entity\Location\City\CityPgSql;
use CommonBundle\Entity\Location\Country\Country;
use CommonBundle\Entity\Location\Country\CountryPgSql;
use CommonBundle\Entity\Location\Region\Region;
use CommonBundle\Entity\Location\Region\RegionPgSql;
use CommonBundle\Entity\Questionnaire\QuestionnairePgSql;
use CommonBundle\Entity\User\Exception\UserException;
use CommonBundle\Entity\Zodiac\Zodiac;
use CommonBundle\Entity\Zodiac\ZodiacPgSql;
use CommonBundle\Entity\File\File;
use CommonBundle\Entity\File\FileRepositoryPgSql;
use CommonBundle\Service\SocialNetworks\Exception\SocialNetworksException;
use CommonBundle\Service\SocialNetworks\SocialNetworks;
use Doctrine\DBAL\Connection;
use FOS\RestBundle\Request\ParamFetcherInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use SSPSoftware\ApiTokenBundle\Service\Util\StringUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class UserProviderPgSql
 * @package CommonBundle\Entity\User
 */
class UserRepositoryPgSql implements UserRepository, OAuthAwareUserProviderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dateTimeFormatDB;

    /**
     * @var string
     */
    private $dateFormatDB;


    /** @var  ZodiacPgSql */
    private $zodiacRepository;

    /** @var  QuestionnairePgSql */
    private $questionnaireRepository;

    /** @var  CountryPgSql */
    private $countryRepository;

    /** @var  RegionPgSql */
    private $regionRepository;

    /** @var  CityPgSql */
    private $cityRepository;

    /**
     * @var FileRepositoryPgSql
     */
    private $fileRepository;

    /**
     * @var GalleryRepositoryPgSql
     */
    private $galleryRepository;

    /**
     * UserRepositoryPgSql constructor.
     * @param Connection $connection
     * @param string $dateTimeFormatDB
     * @param string $dateFormatDB
     * @param ZodiacPgSql $zodiacRepository
     * @param QuestionnairePgSql $questionnaireRepository
     * @param CountryPgSql $countryRepository
     * @param RegionPgSql $regionRepository
     * @param CityPgSql $cityRepository
     * @param FileRepositoryPgSql $fileRepository
     * @param GalleryRepositoryPgSql $galleryRepository
     */
    public function __construct(
        Connection $connection,
        string $dateTimeFormatDB,
        string $dateFormatDB,
        ZodiacPgSql $zodiacRepository,
        QuestionnairePgSql $questionnaireRepository,
        CountryPgSql $countryRepository,
        RegionPgSql $regionRepository,
        CityPgSql $cityRepository,
        FileRepositoryPgSql $fileRepository,
        GalleryRepositoryPgSql $galleryRepository)
    {
        $this->connection = $connection;
        $this->dateTimeFormatDB = $dateTimeFormatDB;
        $this->dateFormatDB = $dateFormatDB;
        $this->zodiacRepository = $zodiacRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
        $this->cityRepository = $cityRepository;
        $this->fileRepository = $fileRepository;
        $this->galleryRepository = $galleryRepository;
    }


    /**
     * @param $apiKey
     * @return UserInterface
     */
    public function loadUserByApiKey($apiKey)
    {
        // todo
    }

    /**
     * @param $email
     * @return UserInterface
     */
    public function loadUserByEmail($email)
    {
        // todo
    }

    public function loadUserByUsername($username)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT *
             FROM users
             WHERE username=:userName',[
            'userName' =>$username
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new UsernameNotFoundException(sprintf(
                'User with username: %s not found', $username
            ));
        }

        return User::build()
            ->fillFromArray($row)
            ->create();
    }

    public function loadUserByPhone($phone)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT *
             FROM users
             WHERE phone_number=:phone',[
            'phone' =>$phone
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            throw new UsernameNotFoundException(sprintf(
                'User with phone number: %s not found', $phone
            ));
        }

        return User::build()
            ->fillFromArray($row)
            ->create();
    }

    /**
     * @param UserInterface $user
     * @return string|UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     * @throws UserException if the user is not add to the DB
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $providerName = $response->getResourceOwner()->getName();

        switch ($providerName) {
            case SocialNetworks::ODNOKLASSNIKI:
                if (array_key_exists('error_code', $response->getResponse())) {
                    throw SocialNetworksException::badOdnoklassnikiResponse($response->getResponse()['error_msg']);
                }
                break;
            case SocialNetworks::VKONTAKTE:
                if (array_key_exists('error', $response->getResponse())) {
                    throw SocialNetworksException::badVkontakteResponse($response->getResponse()['error']['error_msg']);
                }
                break;
            case SocialNetworks::GOOGLE:
                if (array_key_exists('error', $response->getResponse())) {
                    throw SocialNetworksException::badGoogleResponse($response->getResponse()['error']['message']);
                }
                break;
            case SocialNetworks::FACEBOOK:
                if (array_key_exists('error', $response->getResponse())) {
                    throw SocialNetworksException::badFacebookResponse($response->getResponse()['error']['message']);
                }
                break;
        }


        $socialNetworkId = $response->getUsername();

        /** @var User $user */
        $user = $this->findBySNId($providerName, $socialNetworkId);

        if (!$user) {
            return $this->createFromOAuthResponse($response);
        } else {
            return $user;
        }
    }

    private function findBySNId($providerName, $socialNetworkId)
    {
        $where = '';
        switch ($providerName) {
            case SocialNetworks::FACEBOOK:
                $where = 'facebook_id=:socialNetworkId';
                break;
            case SocialNetworks::GOOGLE:
                $where = 'google_id=:socialNetworkId';
                break;
            case SocialNetworks::VKONTAKTE:
                $where = 'vkontakte_id=:socialNetworkId';
                break;
            case SocialNetworks::ODNOKLASSNIKI:
                $where = 'odnoklassniki_id=:socialNetworkId';
                break;
        }
        $stmt = $this->connection->executeQuery("
            select *
            from users
            WHERE " . $where . "
            LIMIT 1;
        ", ['socialNetworkId' => $socialNetworkId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return User::build()
                ->fillFromArray($row)
                ->create();
        } else {
            return null;
        }

    }

    /**
     * @param UserResponseInterface $response
     * @return User
     * @throws UserException
     */
    private function createFromOAuthResponse(UserResponseInterface $response)
    {

        $socialNetworkId = $response->getUsername();
        $providerName = $response->getResourceOwner()->getName();
        $id = StringUtils::generateGUID();
        $insertParams = [
            'id' => $id,
            'username' => $response->getRealName(),
            'gender' => null,
            'birthday' => null,
            'zodiac_id' => null,
            'is_private' => null,
            'is_active' => null,
            'city_id'   => null,
            'password' => null,
            'salt' => null,
            'roles' => json_encode(User::ROLE_NORMAL),
            'facebook_id' => null,
            'google_id' => null,
            'vkontakte_id' => null,
            'odnoklassniki_id' => null,
            'short_code' => null,
            'phone_number' => null,
            'accessibility_status' => null,
            'deleted_at' => null
        ];

        switch ($providerName) {
            case SocialNetworks::FACEBOOK:
                $insertParams['facebook_id'] = $socialNetworkId;
                break;
            case SocialNetworks::GOOGLE:
                $insertParams['google_id'] = $socialNetworkId;
                break;
            case SocialNetworks::VKONTAKTE:
                $insertParams['vkontakte_id'] = $socialNetworkId;
                break;
            case SocialNetworks::ODNOKLASSNIKI:
                $insertParams['odnoklassniki_id'] = $socialNetworkId;
                break;

        }
        if ($this->connection->insert('users', $insertParams)) {
            $this->galleryRepository->createEmptyGallery($id);
            return User::build()
                ->fillFromArray($insertParams)
                ->create();
        }

        throw UserException::canNotCreateUserException($insertParams);
    }


    public function createFromPhoneAndCode($phone, $code)
    {
        $id = StringUtils::generateGUID();
        $insertParams = [
            'id' => $id,
            'username' => $phone,
            'gender' => null,
            'birthday' => null,
            'zodiac_id' => null,
            'is_private' => null,
            'is_active' =>null,
            'city_id' => null,
            'password' => null,
            'salt' => null,
            'roles' => json_encode([]),
            'facebook_id' => null,
            'google_id' => null,
            'vkontakte_id' => null,
            'odnoklassniki_id' => null,
            'phone_number' => $phone,
            'short_code' => $code,
            'accessibility_status' => null,
            'avatar' => null,
            'deleted_at' => null
        ];

        if ($this->connection->insert('users', $insertParams)) {
            $this->galleryRepository->createEmptyGallery($id);
            return User::build()
                ->fillFromArray($insertParams)
                ->create();
        }

        throw UserException::canNotCreateUserException($insertParams);
    }

    public function findByPhoneAndCode($phone, $code)
    {

        $stmt = $this->connection->prepare("select * from users where phone_number = :phone and short_code = :code LIMIT 1");
        $stmt->execute([':phone' => $phone, ':code' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($row)
        {
            return User::build()
                ->fillFromArray($row)
                ->create();
        } else {
            return null;
        }
    }

    public function findById($id)
    {
        $stmt = $this->connection->prepare("select * from users where id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return User::build()
                ->fillFromArray($row)
                ->create();
        }
        throw UserException::canNotFindUser($id);
    }


    public function updateSecretCode(User $user, $code)
    {
        $updateParams = $this->generateUpdateParams($user, ['short_code' => $code]);
        return $this->updateUserByParams($user, $updateParams);
    }

    public function updateUser(ParamFetcherInterface $fetcher, User $user)
    {
        $zodiac = null;
        $birthday = null;
        if($fetcher->get('birthday')){
            $zodiac = $this->getZodiac($fetcher->get('birthday'))->getId();
            $birthday = (new \DateTime($fetcher->get('birthday')))->format($this->dateFormatDB);
        }

        $updateParams = $this->generateUpdateParams($user, [
            'username' => (!$fetcher->get('userName')) ? $user->getUsername() : $fetcher->get('userName'),
            'gender' => (!$fetcher->get('gender')) ? $user->getGender() : $fetcher->get('gender'),
            'birthday' => $birthday,
            'zodiac_id' => $zodiac,
        ]);
        return $this->updateUserByParams($user, $updateParams);
    }

    private function getZodiac($bday){
        if((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::ARIES_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::ARIES_TO))){
            return $this->zodiacRepository->getByName(Zodiac::ARIES);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::TAURUS_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::TAURUS_TO))){
            return $this->zodiacRepository->getByName(Zodiac::TAURUS);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::GEMINI_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::GEMINI_TO))){
            return $this->zodiacRepository->getByName(Zodiac::GEMINI);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::CANCER_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::CANCER_TO))){
            return $this->zodiacRepository->getByName(Zodiac::CANCER);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::LEO_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::LEO_TO))){
            return $this->zodiacRepository->getByName(Zodiac::LEO);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::VIRGO_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::VIRGO_TO))){
            return $this->zodiacRepository->getByName(Zodiac::VIRGO);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::LIBRA_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::LIBRA_TO))){
            return $this->zodiacRepository->getByName(Zodiac::LIBRA);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::SCORPIO_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::SCORPIO_TO))){
            return $this->zodiacRepository->getByName(Zodiac::SCORPIO);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::SAGITTARIUS_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::SAGITTARIUS_TO))){
            return $this->zodiacRepository->getByName(Zodiac::SAGITTARIUS);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::CAPRICORN_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::CAPRICORN_TO))){
            return $this->zodiacRepository->getByName(Zodiac::CAPRICORN);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::AQUARIUS_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::AQUARIUS_TO))){
            return $this->zodiacRepository->getByName(Zodiac::AQUARIUS);
        }elseif((new \DateTime($bday)) >= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::PISCES_FROM)) && (new \DateTime($bday)) <= (new \DateTime(explode('-', $bday)[0] . '-' . Zodiac::PISCES_TO))){
            return $this->zodiacRepository->getByName(Zodiac::PISCES);
        }

    }

    /**
     * @param $id
     * @param bool $isFullList
     * @return array
     * @throws UserException
     * @throws \Exception
     */
    public function getUser($id, $isFullList = false)
    {
        if($id == '{id}'){
            throw new \Exception('Request parameter {id} not found.',400);
        }
        /** @var User $user */
        $user = $this->findById($id);

        try {
            $avatar = $this->fileRepository->getPhotoById($user->getAvatar());
        } catch(\Exception $e) {
            $avatar = null;
        }

        if($isFullList == false && $user->getIsPrivate() == true){

            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'gender' => $user->getGender(),
                'age' => $this->calculateAge($user->getBirthday()),
                'questionnaire' => $this->questionnaireRepository->getShortByUserId($id),
                'roles' => $user->getRoles(),
                'accessibility_status' => $user->getAccessibilityStatus(),
                'avatar' => $avatar
            ];
        }else{
            $zodiac = null;
            if($user->getZodiac()){
                $zodiac = $this->zodiacRepository->getById($user->getZodiac())->getName();
            }

            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'gender' => $user->getGender(),
                'birthday' => $user->getBirthday(),
                'age' => $this->calculateAge($user->getBirthday()),
                'zodiac' => $zodiac,
                'location' => $this->getUserLocation($user->getLocation()),
                'questionnaire' => $this->questionnaireRepository->getByUserId($id),
                'password' => $user->getPassword(),
                'salt' => $user->getSalt(),
                'roles' => $user->getRoles(),
                'facebook_id' => $user->getFacebookId(),
                'google_id' => $user->getGoogleId(),
                'vkontakte_id' => $user->getVkontakteId(),
                'odnoklassniki_id' => $user->getOdnoklassnikiId(),
                'short_code' => $user->getShortCode(),
                'phone_number' => $user->getPhoneNumber(),
                'accessibility_status' => $user->getAccessibilityStatus(),
                'avatar' => $avatar,
                'deleted_at' => $user->getDeletedAt()
            ];
        }
    }

    private function getUserLocation($cityId){
        if($cityId) {
            /** @var City $city */
            $city = $this->cityRepository->getById($cityId);
            /** @var Region $region */
            $region = $this->regionRepository->getById($city->getRegionId());
            /** @var Country $country */
            $country = $this->countryRepository->getById($city->getCountryId());
            return [
                'country' => $country->getName(),
                'region' => $region->getName(),
                'city' => $city->getName()
            ];
        }
        return null;
    }

    private function calculateAge($bday){
      $now = new \DateTime();
      return $now->diff(new \DateTime($bday))->y;
    }

    /**
     * @param $id
     * @param $password
     * @param $confirmPassword
     * @return User
     * @throws UserException
     * @throws \Exception
     */
    public function createPassword($id, $password, $confirmPassword)
    {
        /** @var User $user */
        $user = $this->findById($id);
        if(!$password){
            throw new \Exception('Passwords should not be empty.',400);
        }
        if($password != $confirmPassword){
            throw new \Exception('Passwords does not match.',400);
        }

        $salt = password_hash($password, PASSWORD_BCRYPT);
        $updateParams = $this->generateUpdateParams($user, ['password'=>md5(md5($password)+$salt), 'salt'=>$salt]);

        return $this->updateUserByParams($user, $updateParams);

    }

    /**
     * @param $id
     * @param ParamFetcherInterface $fetcher
     * @return User
     * @throws UserException
     * @throws \Exception
     */
    public function updatePassword($id, ParamFetcherInterface $fetcher)
    {
        /** @var User $user */
        $user = $this->findById($id);
        $oldPassword = $fetcher->get('oldPassword');
        $newPassword = $fetcher->get('newPassword');
        $confirmNewPassword = $fetcher->get('confirmNewPassword');

        if(md5(md5($oldPassword)+$user->getSalt()) != $user->getPassword()){
            throw new \Exception('Old password is incorrect.',400);
        }

        if($newPassword == $oldPassword){
            throw new \Exception('Old pssword and new password does match.',400);
        }

        if($newPassword != $confirmNewPassword){
            throw new \Exception('Passwords does not match.',400);
        }

        $salt = password_hash($newPassword, PASSWORD_BCRYPT);

        $updateParams = $this->generateUpdateParams($user, ['password'=>md5(md5($newPassword)+$salt), 'salt'=>$salt]);
        return $this->updateUserByParams($user, $updateParams);
    }

    /**
     * @param User $user
     * @param $paramsToUpdate
     * @return array
     */
    private function generateUpdateParams(User $user, $paramsToUpdate){
        $updateParams = [
            'username' => $user->getUsername(),
            'gender' => $user->getGender(),
            'birthday' => $user->getBirthday(),
            'zodiac_id' => $user->getZodiac(),
            'is_private' => $user->getIsPrivate() ? 'true' : 'false',
            'is_active' => $user->getIsActive() ? 'true' : 'false',
            'city_id' =>$user->getLocation(),
            'password' => $user->getPassword(),
            'salt' => $user->getSalt(),
            'roles' => $user->getRoles(),
            'facebook_id' => $user->getFacebookId(),
            'google_id' => $user->getGoogleId(),
            'vkontakte_id' => $user->getVkontakteId(),
            'odnoklassniki_id' => $user->getOdnoklassnikiId(),
            'short_code' => $user->getShortCode(),
            'phone_number' => $user->getPhoneNumber(),
            'accessibility_status' => $user->getAccessibilityStatus(),
            'avatar' => $user->getAvatar(),
            'deleted_at' => $user->getDeletedAt()
        ];
        foreach($paramsToUpdate as $key=>$value){
            if(array_key_exists($key, $updateParams)){
                $updateParams[$key] = $value;
            }
        }
        return $updateParams;
    }

    /**
     * @param User $user
     * @param $updateParams
     * @return User
     * @throws UserException
     */
    private function updateUserByParams(User $user, $updateParams)
    {
        if ($this->connection->update('users', $updateParams, ['id' => $user->getId()])) {
            $updateParams += ['id' => $user->getId()];
            return User::build()
                ->fillFromArray($updateParams)
                ->create();
        }
        throw UserException::canNotUpdateUser($updateParams);
    }

    public function linkSN(ApiToken $token, UserResponseInterface $response)
    {
        $providerName = $response->getResourceOwner()->getName();
        $socialNetworkId = $response->getUsername();

        /** @var User $user */
        $user = $this->findById($token->getUser());
        if ($socialNetworkId == null){
            throw new \Exception('Token is empty.',400);
        }

        if ($user->getFacebookId() == $socialNetworkId ||
            $user->getGoogleId() == $socialNetworkId ||
            $user->getVkontakteId() == $socialNetworkId ||
            $user->getOdnoklassnikiId() == $socialNetworkId){
            throw new \Exception('Social Network already linked.',400);
        }

        $insertParams = [];
        switch ($providerName) {
            case SocialNetworks::FACEBOOK:
                $insertParams['facebook_id'] = $socialNetworkId;
                break;
            case SocialNetworks::GOOGLE:
                $insertParams['google_id'] = $socialNetworkId;
                break;
            case SocialNetworks::VKONTAKTE:
                $insertParams['vkontakte_id'] = $socialNetworkId;
                break;
            case SocialNetworks::ODNOKLASSNIKI:
                $insertParams['odnoklassniki_id'] = $socialNetworkId;
                break;
        }


        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);
    }


    /**
     * @param $id
     * @param $role
     * @return User
     * @throws UserException
     */
    public function changeRole($id, $role)
    {
        /** @var User $user */
        $user = $this->findById($id);
        $insertParams = [];
        switch ($role) {
            case 0:
                $insertParams['roles'] = json_encode(User::ROLE_NORMAL);
                break;
            case 1:
                $insertParams['roles'] = json_encode(User::ROLE_VIP);
                break;
            case 2:
                $insertParams['roles'] = json_encode(User::ROLE_ADMIN);
                break;
        }

        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);

    }

    /**
     * @param $id
     * @param $accessibilityStatus
     * @return User
     * @throws UserException
     */
    public function changeAccessibilityStatus($id, $accessibilityStatus)
    {
        /** @var User $user */
        $user = $this->findById($id);
        $insertParams = [];
        switch ($accessibilityStatus) {
            case 0:
                $insertParams['accessibility_status'] = json_encode(User::ACCESS_ACTIVE);
                $insertParams['deleted_at'] = null;
                break;
            case 1:
                $insertParams['accessibility_status'] = json_encode(User::ACCESS_BLOCKED);
                $insertParams['deleted_at'] = null;
                break;
            case 2:
                $insertParams['accessibility_status'] = json_encode(User::ACCESS_DELETED);
                $insertParams['deleted_at'] = (new \DateTime())->format($this->dateTimeFormatDB);
                break;
        }

        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);
    }

    /**
     * @param ApiToken $token
     * @param $cityId
     * @return User
     * @throws UserException
     */
    public function changeLocation(ApiToken $token, $cityId)
    {
        /** @var User $user */
        $user = $this->findById($token->getUser());
        $insertParams['city_id'] = $cityId;

        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);
    }

    /**
     * @param ApiToken $token
     * @param $privateStatus
     * @return User
     * @throws UserException
     */
    public function changePrivateStatus(ApiToken $token, $privateStatus)
    {
        /** @var User $user */
        $user = $this->findById($token->getUser());
        $insertParams = [];
        switch ($privateStatus) {
            case 0:
                $insertParams['is_private'] = 'false';
                break;
            case 1:
                $insertParams['is_private'] = 'true';
                break;
        }

        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);
    }

    /**
     * @param ApiToken $token
     * @param $activityStatus
     * @return User
     * @throws UserException
     */
    public function changeActivityStatus(ApiToken $token, $activityStatus)
    {
        /** @var User $user */
        $user = $this->findById($token->getUser());
        $insertParams = [];
        switch ($activityStatus) {
            case 0:
                $insertParams['is_active'] = 'false';
                break;
            case 1:
                $insertParams['is_active'] = 'true';
                break;
        }

        $updateParams = $this->generateUpdateParams($user, $insertParams);
        return $this->updateUserByParams($user, $updateParams);
    }


    /**
     * POST a new avatar. The old one will be replaced
     *
     * @param Request $request
     * @param int $id
     * @return User
     * @throws UserException
     */
    public function addUserAvatar($request, $id)
    {
        $file = $request->files->get('file', null);

        if ($file instanceof UploadedFile) {
            try {
                $fileId = $this->fileRepository->save($request, $id);
                $user = $this->findById($id);
                if($user->getAvatar() !== null) {
                    $this->fileRepository->delete($user->getAvatar());
                }
                $insertParams = ['avatar' => $fileId];
                $updateParams = $this->generateUpdateParams($user, $insertParams);
                $this->updateUserByParams($user, $updateParams);
            } catch (FileException $e) {
                throw new UserException($e->getMessage(), $e->getCode(), $e);
            }
        }

    }
}