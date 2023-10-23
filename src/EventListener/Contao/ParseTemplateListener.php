<?php

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\Model;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("parseTemplate")
 *
 * @deprecated Will be removed in version 2.0.0.
 */
class ParseTemplateListener
{
    private Utils $utils;
    private array $bundleConfig;

    public function __construct(Utils $utils, array $bundleConfig)
    {
        $this->utils = $utils;
        $this->bundleConfig = $bundleConfig;
    }


    public function __invoke(Template $template): void
    {
        if ('fe_page' === $template->getName() || 0 === strpos($template->getName(), 'fe_page_')) {
            if (isset($this->bundleConfig['use_contao_template_variables']) && true === $this->bundleConfig['use_contao_template_variables']) {
                return;
            }

            $layout = $this->getLayout($template);

            if (!$layout || !$layout->addEncore) {
                return;
            }

            $template->encoreStylesheets = '[[HUH_ENCORE_CSS]]';
            $template->encoreHeadScripts = '[[HUH_ENCORE_HEAD_JS]]';
            $template->encoreScripts = '[[HUH_ENCORE_JS]]';
        }
    }

    protected function getLayout(Template $template): ?Model
    {
        if (isset($template->layout) && $template->layout instanceof LayoutModel) {
            return $template->layout;
        }

        $page = $this->utils->request()->getCurrentPageModel();
        if (!$page) {
            return null;
        }

        return $this->utils->model()->findModelInstanceByPk(LayoutModel::getTable(), $page->layout);
    }
}