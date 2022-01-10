<?php

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener
{
    /** @var Utils  */
    private $utils;

    /** @var array  */
    private $bundleConfig;

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

            if (!$layout->addEncore) {
                return;
            }

            $template->encoreStylesheets = '[[HUH_ENCORE_CSS]]';
            $template->encoreHeadScripts = '[[HUH_ENCORE_HEAD_JS]]';
            $template->encoreScripts = '[[HUH_ENCORE_JS]]';
        }
    }

    protected function getLayout(Template $template): ?LayoutModel
    {
        if (isset($template->layout) && $template->layout instanceof LayoutModel) {
            return $template->layout;
        }

        $page = $this->utils->request()->getCurrentPageModel();
        if (!$page) {
            return null;
        }

        $layout = LayoutModel::findByPk($page->layout);
        return $layout;
    }
}