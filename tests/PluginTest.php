<?php declare(strict_types=1);
/**
 *
 * class FfuenfCategoryUrlSlash
 *
 * @category   Shopware
 * @package    Shopware\Plugins\FfuenfCategoryUrlSlash
 * @author     Achim Rosenhagen / ffuenf - Pra & Rosenhagen GbR
 * @copyright  Copyright (c) 2019, Achim Rosenhagen / ffuenf - Pra & Rosenhagen GbR (https://www.ffuenf.de)
 *
 */

namespace FfuenfCategoryUrlSlash\Tests;

use FfuenfCategoryUrlSlash\FfuenfCategoryUrlSlash as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'FfuenfCategoryUrlSlash' => [],
    ];
    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['FfuenfCategoryUrlSlash'];
        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
