<?php

declare(strict_types=1);

namespace Feefo\Reviews\Setup\Patch\Data;

use Exception;
use Feefo\Reviews\Api\Feefo\StorageInterface;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User as UserModel;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * Class InstallFeefoData
 */
class InstallFeefoData implements DataPatchInterface
{
    /**
     * Admin User Data Constants
     */
    public const DATA_USERNAME = 'feefo';

    public const DATA_USER_EMAIL = 'technical@feefo.com';

    public const DATA_USER_FIRST_NAME = 'Feefo';

    public const DATA_USER_LAST_NAME = 'Feefo';

    public const DATA_IS_ACTIVE = 1;

    public const DATA_IS_INACTIVE = 0;

    public const PREFIX_PASSWORD = 'feefo';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokenService;

    /**
     * @var CollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param State $appState
     * @param UserFactory $userFactory
     * @param AdminTokenServiceInterface $adminTokenService
     * @param CollectionFactory $roleCollectionFactory
     * @param LoggerInterface $logger
     * @param Random $mathRandom
     * @param StorageInterface $storage
     * @param UserResource $userResource
     */
    public function __construct(
        State $appState,
        UserFactory $userFactory,
        AdminTokenServiceInterface $adminTokenService,
        CollectionFactory $roleCollectionFactory,
        LoggerInterface $logger,
        Random $mathRandom,
        StorageInterface $storage,
        UserResource $userResource,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->appState = $appState;
        $this->userFactory = $userFactory;
        $this->adminTokenService = $adminTokenService;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->logger = $logger;
        $this->mathRandom = $mathRandom;
        $this->storage = $storage;
        $this->userResource = $userResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Installs data for a module
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            /** @var string $password */
            $password = $this->generatePassword(static::PREFIX_PASSWORD);

            $this->createAdminUser([
                'username' => static::DATA_USERNAME,
                'email' => static::DATA_USER_EMAIL,
                'firstname' => static::DATA_USER_FIRST_NAME,
                'lastname' => static::DATA_USER_LAST_NAME,
                'password' => $password,
                'is_active' => static::DATA_IS_ACTIVE,
            ]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Generate password for the feefo user
     *
     * @param string $prefix
     * @return string
     */
    private function generatePassword(string $prefix): string
    {
        return $this->mathRandom->getUniqueHash($prefix);
    }

    /**
     * Create a admin user for integration with Feefo
     *
     * @param array $data
     *
     * @return void
     */
    private function createAdminUser(array $data): void
    {
        if ($this->isAdminUserAlreadyExists($data['username'])) {
            return;
        }

        /** @var UserModel $userModel */
        $userModel = $this->userFactory->create();
        $userModel->setData($data);
        $roleId = $this->getAdminRoleId();

        if ($roleId) {
            $userModel->setRoleId($roleId);
        }

        $this->userResource->save($userModel);

        if ($userModel->getId()) {
            $token = $this->createAdminToken($userModel->getUserName(), $data['password']);
            $this->makeUserInactive($userModel);
            $this->logger->debug(__('Feefo token( %s ) has been created', $token));

            $this->storage->setAccessKey($token);
            $this->storage->setUserId($userModel->getId());
        } else {
            $this->logger->error(__('User couldn\'t create'));
        }
    }

    /**
     * Check admin user existing
     *
     * @param string $username
     *
     * @return string
     */
    private function isAdminUserAlreadyExists(string $username): string
    {
        /** @var UserModel $userModel */
        $userModel = $this->userFactory->create();
        $userModel->loadByUsername($username);

        return (string) $userModel->getId();
    }

    /**
     * Get any admin group if exists
     *
     * @return string
     */
    private function getAdminRoleId(): string
    {
        try {
            /** @var Collection $roleCollection */
            $roleCollection = $this->roleCollectionFactory->create();
            $roleCollection
                ->setRolesFilter()
                ->setUserFilter(null, UserContextInterface::USER_TYPE_ADMIN);
            $roleCollection->load();
            if ($roleCollection->getSize() > 0) {
                /** @var Role $adminRole */
                $adminRole = $roleCollection->getFirstItem();

                return $adminRole->getId();
            }

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return '';
    }

    /**
     * Create access token for the $username
     *
     * @param string $username
     * @param string $password
     *
     * @return string
     */
    private function createAdminToken(string $username, string $password): string
    {
        return $this->appState->emulateAreaCode(
            FrontNameResolver::AREA_CODE,
            [$this->adminTokenService, 'createAdminAccessToken'],
            [$username, $password]
        );
    }

    /**
     * Inactivate a admin user
     *
     * @param $userModel UserModel
     *
     * @return void
     */
    private function makeUserInactive(UserModel $userModel): void
    {
        $userModel->setIsActive(static::DATA_IS_INACTIVE);

        $this->userResource->save($userModel);
    }

    /**
     * Get alases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
