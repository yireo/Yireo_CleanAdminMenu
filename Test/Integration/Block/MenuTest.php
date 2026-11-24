<?php declare(strict_types=1);

namespace Yireo\CleanAdminMenu\Test\Integration\Block;

use Magento\Backend\Block\Menu as MenuBlock;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\CleanAdminMenu\Plugin\CleanMenuModelPlugin;

/**
 * @magentoAppArea adminhtml
 */
class MenuTest extends TestCase
{
    private const BUCKET = 'Yireo_CleanAdminMenu::thirdParty';

    public function testPluginIsRegistered(): void
    {
        $pluginList = Bootstrap::getObjectManager()->get(PluginListInterface::class);
        $next = $pluginList->getNext(MenuBlock::class, 'getMenuModel');

        $this->assertIsArray($next);
        $this->assertContains(
            'Yireo_CleanAdminMenu::cleanMenuModel',
            (array)($next[DefinitionInterface::LISTENER_AFTER] ?? [])
        );

        $plugin = $pluginList->getPlugin(MenuBlock::class, 'Yireo_CleanAdminMenu::cleanMenuModel');
        $this->assertInstanceOf(CleanMenuModelPlugin::class, $plugin);
    }

    public function testMenuModelOnlyContainsCoreItemsAndBucketAtRoot(): void
    {
        $menuBlock = Bootstrap::getObjectManager()->create(MenuBlock::class);
        $menu = $menuBlock->getMenuModel();

        $this->assertNotNull($menu->get(self::BUCKET));

        foreach ($menu as $menuItem) {
            $moduleName = (string)($menuItem->toArray()['module'] ?? '');
            $this->assertTrue(
                str_starts_with($moduleName, 'Magento_') || $moduleName === 'Yireo_CleanAdminMenu',
                sprintf('Menu item "%s" of module "%s" is still at root level', $menuItem->getId(), $moduleName)
            );
        }

        $this->assertSame($menu, $menuBlock->getMenuModel());
    }

    public function testMarketplaceItemIsMovedIntoBucket(): void
    {
        $moduleManager = Bootstrap::getObjectManager()->get(ModuleManager::class);
        if (false === $moduleManager->isEnabled('Magento_Marketplace')) {
            $this->markTestSkipped('Module Magento_Marketplace is not enabled');
        }

        $menu = Bootstrap::getObjectManager()->create(MenuBlock::class)->getMenuModel();
        $bucket = $menu->get(self::BUCKET);

        $this->assertNotNull($bucket);
        $this->assertNotNull($bucket->getChildren()->get('Magento_Marketplace::partners'));
    }

    public function testCacheLifetimeIsNotDisabled(): void
    {
        $menuBlock = Bootstrap::getObjectManager()->create(MenuBlock::class);
        $this->assertGreaterThan(0, (int)$menuBlock->getCacheLifetime());
    }
}
