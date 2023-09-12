<?php


namespace Remarkety\Mgconnector\Helper;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Inspection\Exception;
use \Remarkety\Mgconnector\Model\Install as InstallModel;
use Remarkety\Mgconnector\Serializer\AddressSerializer;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $storeManager;
    protected $galleryManagement;
    protected $_catalogProductTypeConfigurable;
    private $categoryMapCache = [];
    protected $categoryFactory;
    protected $configHelper;
    protected $addressSerializer;
    protected $integration;
    protected $moduleResource;
    protected $session;
    protected $installModel;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \Magento\Integration\Model\Integration $integration,
        \Magento\Customer\Model\Session $session,
        InstallModel $installModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Gallery\GalleryManagement $galleryManagement,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        ConfigHelper $configHelper,
        AddressSerializer $addressSerializer
    ) {
        $this->integration = $integration;
        $this->moduleResource = $moduleResource;
        $this->session = $session;
        $this->installModel = $installModel;
        $this->storeManager = $storeManager;
        $this->galleryManagement = $galleryManagement;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->categoryFactory = $categoryFactory;
        $this->configHelper = $configHelper;
        $this->addressSerializer = $addressSerializer;
        parent::__construct($context);
    }

    public function getInstalledVersion()
    {
        return $this->moduleResource->getDataVersion('Remarkety_Mgconnector');
    }


    public function getMode()
    {
        $mode = InstallModel::MODE_INSTALL_CREATE;

        $webServiceUser = $this->integration->load(InstallModel::WEB_SERVICE_USERNAME, 'name');
        if ($webServiceUser->getData()) {
            $mode = InstallModel::MODE_UPGRADE;
        }

        $response = $this->session->getRemarketyLastResponseStatus();
        if ($response == 1) {
            $mode = InstallModel::MODE_COMPLETE;
        } elseif ($response == 0) {
            $mode = InstallModel::MODE_INSTALL_CREATE;
        }
        $configuredStores = $this->installModel->getConfiguredStores();
        if ($configuredStores) {
            $mode = InstallModel::MODE_WELCOME;
        }

        $forceMode = $this->_request->getParam('mode', false);
        if (!empty($forceMode)) {
            $mode = $forceMode;
        }

        if (!in_array($mode, [
            InstallModel::MODE_INSTALL_CREATE,
            InstallModel::MODE_INSTALL_LOGIN,
            InstallModel::MODE_UPGRADE,
            InstallModel::MODE_COMPLETE,
            InstallModel::MODE_WELCOME,
        ])) {
            throw new \Exception('Installation mode can not be handled.');
        }
        return $mode;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    public function getMediaGalleryImages(ProductInterface $product)
    {
        /**
         * @var $images ProductAttributeMediaGalleryEntryInterface[]
         */
        $images = $this->galleryManagement->getList($product->getSku());
        $ret = [];
        $imagesData = [];
        if ($images) {
            foreach ($images as $imageAttr) {
                if ($imageAttr->getMediaType() == "image") {
                    $types = $imageAttr->getTypes();
                    if (empty($types)) {
                        $imagesData['id'] = $imageAttr->getId();
                        $imagesData['product_id'] = $imageAttr->getEntityId();
                        $imagesData['src'] = $this->getMediaUrl() . 'catalog/product' . $imageAttr->getFile();
                        $ret[] = $imagesData;
                    } else {
                        foreach ($types as $type) {
                            $imagesData['id'] = $imageAttr->getId();
                            $imagesData['type'] = $type;
                            $imagesData['product_id'] = $imageAttr->getEntityId();
                            $imagesData['src'] = $this->getMediaUrl() . 'catalog/product' . $imageAttr->getFile();
                            $ret[] = $imagesData;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    public function getImage($product)
    {
        $images = $this->galleryManagement->getList($product->getSku());
        $imageDet = [];
        $imagesData = [];
        if ($images) {
            foreach ($images as $imageAttr) {
                if ($imageAttr['types']) {
                    foreach ($imageAttr['types'] as $type) {
                        if ($type == 'image') {
                            $imagesData['id'] = $imageAttr['id'];
                            $imagesData['product_id'] = $imageAttr['entity_id'];
                            $imagesData['src'] = $this->getMediaUrl() . 'catalog/product' . $imageAttr['file'];
                            $imageDet = $imagesData;
                        }

                    }
                }
            }
            return $imageDet;
        }
    }

    /**
     * @param $category_id
     * @param null $storeId
     * @return array|bool
     */
    public function getCategory($category_id, $storeId = null)
    {
        if (!isset($this->categoryMapCache[$category_id])) {
            $fullPath = $this->configHelper->useCategoriesFullPath();
            /**
             * @var Category $category
             */
            $category = $this->categoryFactory->create()->load($category_id);
            if (!$fullPath) {
                $name = $category->getName();
            } else {
                $parents = $category->getParentCategories();
                if (count($parents) > 1) {
                    $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
                    $nameParts = [];
                    foreach ($parents as $parentCategory) {
                        if ($parentCategory->getId() == $rootCategoryId) {
                            continue;
                        }
                        $nameParts[] = $parentCategory->getName();
                    }
                    $name = implode(" / ", $nameParts);
                } else {
                    $name = $category->getName();
                }
            }
            $this->categoryMapCache[$category_id] = $name;
        }
        if (!isset($this->categoryMapCache[$category_id])) {
            return false;
        }

        return ['code' => $category_id, 'name' => $this->categoryMapCache[$category_id]];
    }

    public function getParentId($id)
    {
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $id = $parentByChild[0];
            return $id;
        }
        return false;
    }

    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public static function toCamelCase($string, $capitalizeFirstCharacter = false)
    {
        if (strlen($string) == 0) {
            return "";
        }
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = empty($str[0]) ? '' : $str[0];
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    public function getCustomerAddresses($customer)
    {
        $addresses = $customer->getAddresses();
        $address = null;
        $toUse = $this->configHelper->getCustomerAddressType();
        if (!empty($addresses)) {
            $addressId = null;
            if ($toUse === ConfigHelper::CUSTOMER_ADDRESS_BILLING) {
                $addressId = $customer->getDefaultBilling();
            } else {
                $addressId = $customer->getDefaultShipping();
            }
            if ($addressId) {
                $address = $this->findAddressById($addresses, $addressId);
            }
        }

        return $address ? $this->addressSerializer->serialize($address) : null;
    }

    private function findAddressById($addresses, $id)
    {
        foreach ($addresses as $address) {
            if ($address->getId() === $id) {
                return $address;
            }
        }
        return null;
    }
}
