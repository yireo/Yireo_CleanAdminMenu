<?php declare(strict_types=1);

namespace Yireo\CleanAdminMenu\Plugin;

use InvalidArgumentException;
use Magento\Backend\Block\Menu as MenuBlock;
use Magento\Backend\Model\Menu as MenuModel;
use Magento\Backend\Model\Menu\Item as MenuItem;
use WeakMap;

class CleanMenuModelPlugin
{
    private const OWN_MODULE = 'Yireo_CleanAdminMenu';

    private WeakMap $processedMenuModels;

    /**
     * @param array<int|string, mixed> $removeMenuItemIds
     */
    public function __construct(
        private string $thirdPartyBucket = 'Yireo_CleanAdminMenu::thirdParty',
        private array $removeMenuItemIds = []
    ) {
        $this->processedMenuModels = new WeakMap();
    }

    public function afterGetMenuModel(MenuBlock $subject, MenuModel $result): MenuModel
    {
        if (isset($this->processedMenuModels[$result])) {
            return $result;
        }

        $this->processedMenuModels[$result] = true;

        if ($result->get($this->thirdPartyBucket) === null) {
            return $result;
        }

        foreach ($this->removeMenuItemIds as $menuItemId) {
            if (is_string($menuItemId) && $menuItemId !== '') {
                $result->remove($menuItemId);
            }
        }

        $this->moveToThirdPartyBucket($result, 'Magento_Marketplace::partners');

        $menuItemIds = [];
        foreach ($result as $menuItem) {
            if ($this->isThirdPartyMenuItem($menuItem)) {
                $menuItemIds[] = (string)$menuItem->getId();
            }
        }

        foreach ($menuItemIds as $menuItemId) {
            $this->moveToThirdPartyBucket($result, $menuItemId);
        }

        return $result;
    }

    private function isThirdPartyMenuItem(MenuItem $menuItem): bool
    {
        $menuItemId = (string)$menuItem->getId();
        if ($menuItemId === $this->thirdPartyBucket) {
            return false;
        }

        $moduleName = (string)($menuItem->toArray()['module'] ?? '');
        if ($moduleName === '') {
            $moduleName = $menuItemId;
        }

        if (str_starts_with($moduleName, 'Magento_')) {
            return false;
        }

        if (str_starts_with($moduleName, self::OWN_MODULE)) {
            return false;
        }

        return true;
    }

    private function moveToThirdPartyBucket(MenuModel $menuModel, string $menuItemId): void
    {
        if ($menuModel->get($menuItemId) === null) {
            return;
        }

        try {
            $menuModel->move($menuItemId, $this->thirdPartyBucket);
        } catch (InvalidArgumentException) {
            return;
        }
    }
}
