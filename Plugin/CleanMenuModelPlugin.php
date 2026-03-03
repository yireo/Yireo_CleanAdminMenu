<?php declare(strict_types=1);

namespace Yireo\CleanAdminMenu\Plugin;

use Magento\Backend\Block\Menu as MenuBlock;
use Magento\Backend\Model\Menu as MenuModel;

class CleanMenuModelPlugin
{
    public function __construct(
        private string $thirdPartyBucket = 'Yireo_CleanAdminMenu::thirdParty',
        private array $removeMenuItemIds = []
    ) {
    }

    public function afterGetCacheLifetime(): int
    {
        return 0;
    }

    private function moveToThirdPartyBucket(MenuModel $menuModel, string $menuItemId)
    {
        $menuModel->move($menuItemId, $this->thirdPartyBucket);
    }

    public function afterGetMenuModel(MenuBlock $subject, MenuModel $result): MenuModel
    {
        foreach ($this->removeMenuItemIds as $menuItemId) {
            $result->remove($menuItemId);
        }

        $this->moveToThirdPartyBucket($result, 'Magento_Marketplace::partners');

        foreach ($result as $menuItem) {
            if (str_starts_with($menuItem->getId(), 'Magento_')) {
                continue;
            }

            if (str_starts_with($menuItem->getId(), 'Yireo_CleanAdminMenu')) {
                continue;
            }

            $this->moveToThirdPartyBucket($result, $menuItem->getId());
        }

        return $result;
    }
}
