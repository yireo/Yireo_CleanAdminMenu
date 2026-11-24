<?php declare(strict_types=1);

namespace Yireo\CleanAdminMenu\Test\Unit\Plugin;

use Magento\Backend\Block\Menu as MenuBlock;
use Magento\Backend\Model\Menu as MenuModel;
use Magento\Backend\Model\Menu\Item as MenuItem;
use Magento\Backend\Model\Menu\Item\Factory as MenuItemFactory;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Yireo\CleanAdminMenu\Plugin\CleanMenuModelPlugin;

class CleanMenuModelPluginTest extends TestCase
{
    private const BUCKET = 'Yireo_CleanAdminMenu::thirdParty';

    public function testThirdPartyItemsAreMovedIntoBucket(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem('Magento_Sales::sales', 'Magento_Sales'));
        $menu->add($this->createItem('Foo_One::root', 'Foo_One'));
        $menu->add($this->createItem('Foo_Two::root', 'Foo_Two'));
        $menu->add($this->createItem('Foo_Three::root', 'Foo_Three'));
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $result = $this->createPlugin()->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame(['Magento_Sales::sales', self::BUCKET], $this->getRootIds($result));
        $this->assertSame(
            ['Foo_One::root', 'Foo_Two::root', 'Foo_Three::root'],
            $this->getIds($result->get(self::BUCKET)->getChildren())
        );
    }

    public function testMissingMarketplaceItemDoesNotThrow(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $result = $this->createPlugin()->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame([self::BUCKET], $this->getRootIds($result));
    }

    public function testMarketplaceItemIsMovedIntoBucket(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem('Magento_Marketplace::partners', 'Magento_Marketplace'));
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $result = $this->createPlugin()->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame([self::BUCKET], $this->getRootIds($result));
        $this->assertNotNull($result->get(self::BUCKET)->getChildren()->get('Magento_Marketplace::partners'));
    }

    public function testMissingBucketLeavesMenuUntouched(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem('Magento_Marketplace::partners', 'Magento_Marketplace'));
        $menu->add($this->createItem('Foo_One::root', 'Foo_One'));

        $result = $this->createPlugin()->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame(['Magento_Marketplace::partners', 'Foo_One::root'], $this->getRootIds($result));
    }

    public function testRemovingMarketplaceItemDoesNotThrow(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem('Magento_Marketplace::partners', 'Magento_Marketplace'));
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $plugin = $this->createPlugin(['Magento_Marketplace::partners']);
        $result = $plugin->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame([self::BUCKET], $this->getRootIds($result));
        $this->assertNull($result->get('Magento_Marketplace::partners'));
    }

    public function testModuleNameDecidesInsteadOfIdPrefix(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem('Custom::core_item', 'Magento_Catalog'));
        $menu->add($this->createItem('Magento_Fake::root', 'Foo_Fake'));
        $menu->add($this->createItem('Yireo_CleanAdminMenu::fake', 'Foo_Other'));
        $menu->add($this->createItem('Magento_NoModule::root', ''));
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $result = $this->createPlugin()->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame(['Custom::core_item', 'Magento_NoModule::root', self::BUCKET], $this->getRootIds($result));
        $this->assertSame(
            ['Magento_Fake::root', 'Yireo_CleanAdminMenu::fake'],
            $this->getIds($result->get(self::BUCKET)->getChildren())
        );
    }

    public function testMenuModelIsOnlyProcessedOnce(): void
    {
        $menu = $this->createMenu();
        $menu->add($this->createItem(self::BUCKET, 'Yireo_CleanAdminMenu', $this->createMenu()));

        $plugin = $this->createPlugin();
        $plugin->afterGetMenuModel($this->createMenuBlock(), $menu);

        $menu->add($this->createItem('Foo_Late::root', 'Foo_Late'));
        $result = $plugin->afterGetMenuModel($this->createMenuBlock(), $menu);

        $this->assertSame([self::BUCKET, 'Foo_Late::root'], $this->getRootIds($result));
    }

    /**
     * @param string[] $removeMenuItemIds
     */
    private function createPlugin(array $removeMenuItemIds = []): CleanMenuModelPlugin
    {
        return new CleanMenuModelPlugin(self::BUCKET, $removeMenuItemIds);
    }

    private function createMenuBlock(): MenuBlock
    {
        return $this->createMock(MenuBlock::class);
    }

    private function createMenu(): MenuModel
    {
        return new MenuModel(
            $this->createMock(LoggerInterface::class),
            '',
            $this->createMock(MenuItemFactory::class),
            $this->createMock(SerializerInterface::class)
        );
    }

    private function createItem(string $id, string $moduleName, ?MenuModel $children = null): MenuItem
    {
        $children = $children ?? $this->createMenu();
        $item = $this->createMock(MenuItem::class);
        $item->method('getId')->willReturn($id);
        $item->method('toArray')->willReturn(['id' => $id, 'module' => $moduleName]);
        $item->method('getChildren')->willReturn($children);
        $item->method('hasChildren')->willReturnCallback(fn () => $children->count() > 0);

        return $item;
    }

    /**
     * @return string[]
     */
    private function getRootIds(MenuModel $menu): array
    {
        return $this->getIds($menu);
    }

    /**
     * @return string[]
     */
    private function getIds(MenuModel $menu): array
    {
        $ids = [];
        foreach ($menu as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
