<?php
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

namespace FfuenfCategoryUrlSlash\Subscriber;

use Enlight\Event\SubscriberInterface;
use FfuenfCommon\Service\AbstractService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Enlight_Hook_HookArgs;
use Shopware\Models\Category\Category;
use Shopware\Components\Model\ModelManager;
use Smarty;

class CategoryUrlsSubscriber extends AbstractService implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var \Smarty_Data
     */
    protected $data;

    public function __construct(string $pluginName, string $pluginDirectory, string $viewDirectory, ContainerInterface $container)
    {
        parent::__construct($pluginName, $pluginDirectory, $viewDirectory, $container);
        $this->modelManager = $this->container->get('models');
    }

    public static function getSubscribedEvents()
    {
        return [
            'sRewriteTable::sCreateRewriteTableCategories::before' => 'sCreateRewriteTableCategoriesBefore'
        ];
    }

    public function sCreateRewriteTableCategoriesBefore(Enlight_Hook_HookArgs $args)
    {
        try {
            $offset = $args->get('offset');
            $limit = $args->get('limit');
            $subject = $args->getSubject();
            $this->baseSetup($subject);
            $routerCategoryTemplate = $this->config['routerCategoryTemplate'];
            if (empty($routerCategoryTemplate)) {
                return;
            }
            $parentId = Shopware()->Shop()->getCategory()->getId();
            $categories = $this->modelManager
                ->getRepository(Category::class)
                ->getActiveChildrenList($parentId);
            if (isset($offset, $limit)) {
                $categories = array_slice($categories, $offset, $limit);
            }
            // Remove slash
            $routerCategoryTemplateSlash = rtrim($routerCategoryTemplate, '/');
            if ($routerCategoryTemplateSlash !== $routerCategoryTemplate) {
                $template = 'string:' . $routerCategoryTemplateSlash;
                $template = $this->templateManager->createTemplate($template, $this->data);
                foreach ($categories as $category) {
                    if (!empty($category['external'])) {
                        continue;
                    }
                    $template->assign('sCategory', $category);
                    $path = $template->fetch();
                    $path = $subject->sCleanupPath($path);
                    if ($category['blog']) {
                        $orgPath = 'sViewport=blog&sCategory=' . $category['id'];
                    } else {
                        $orgPath = 'sViewport=cat&sCategory=' . $category['id'];
                    }
                    $subject->sInsertUrl($orgPath, $path);
                }
            }
        } catch (\Exception $ex) {
            if ($this->config['debug']) {
                $this->logger->log(100, $ex->getMessage());
            }
        }
    }

    /**
     * Sets up the environment for seo url calculation
     *
     * @param $subject
     *
     * @throws \SmartyException
     */
    public function baseSetup($subject)
    {
        $keys = isset($this->templateManager->registered_plugins['function']) ? array_keys($this->templateManager->registered_plugins['function']) : [];
        if (!in_array('sCategoryPath', $keys)) {
            $this->templateManager->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'sCategoryPath',
                [$subject, 'sSmartyCategoryPath']
            );
        }
        if (!in_array('createSupplierPath', $keys)) {
            $this->templateManager->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'createSupplierPath',
                [$subject, 'createSupplierPath']
            );
        }
        $this->data = $this->templateManager->createData();
        $this->data->assign('sConfig', $this->config);
        $this->data->assign('sRouter', $subject);
        $this->data->assign('sCategoryStart', Shopware()->Shop()->getCategory()->getId());
    }
}
