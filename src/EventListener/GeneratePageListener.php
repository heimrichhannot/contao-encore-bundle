<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\EventListener;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class GeneratePageListener
{
    /**
     * @var array
     */
    private $bundleConfig;
    /**
     * @var TemplateAsset
     */
    private $templateAsset;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Contao\CoreBundle\Framework\ContaoFramework|object|null
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param array $bundleConfig
     * @param ContaoFrameworkInterface $framework
     * @param ContainerInterface $container
     * @param Environment $twig
     * @param TemplateAsset $templateAsset
     */
    public function __construct(array $bundleConfig, ContaoFrameworkInterface $framework, ContainerInterface $container, Environment $twig, TemplateAsset $templateAsset)
    {
        $this->framework = $framework;
        $this->twig = $twig;
        $this->container = $container;
        $this->templateAsset = $templateAsset;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @Hook("generatePage")
     */
    public function onGeneratePage(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$layout->addEncore) {
            return;
        }
        $this->addEncore($pageModel, $layout, $pageRegular);
        $this->cleanGlobalArrays($layout);
    }

    /**
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, $encoreField);

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $templateAssets->linkTags();


        if ($includeInline) {
            $pageRegular->Template->encoreStylesheetsInline = $templateAssets->inlineCssLinkTag();
        }

        // render js alone (should be used in footer region)
        $pageRegular->Template->encoreScripts = $templateAssets->scriptTags();

        $pageRegular->Template->encoreHeadScripts = $templateAssets->headScriptTags();
    }

    /**
     * Clean up contao global asset arrays
     */
    public function cleanGlobalArrays(LayoutModel $layout)
    {
        if (!$this->container->get('huh.utils.container')->isFrontend()) {
            return;
        }

        // js
        if (isset($this->bundleConfig['unset_global_keys']['js']) && \is_array($this->bundleConfig['unset_global_keys']['js'])) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (\is_array($jsFiles)) {
                foreach ($this->bundleConfig['unset_global_keys']['js'] as $jsFile) {
                    if (isset($jsFiles[$jsFile])) {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }
        // jquery
        if (isset($this->bundleConfig['unset_global_keys']['jquery']) && \is_array($this->bundleConfig['unset_global_keys']['jquery'])) {
            $jqueryFiles = &$GLOBALS['TL_JQUERY'];

            if (\is_array($jqueryFiles)) {
                foreach ($this->bundleConfig['unset_global_keys']['jquery'] as $legacyFile) {
                    if (isset($jqueryFiles[$legacyFile])) {
                        unset($jqueryFiles[$legacyFile]);
                    }
                }
            }
        }

        // css
        if (isset($this->bundleConfig['unset_global_keys']['css']) && \is_array($this->bundleConfig['unset_global_keys']['css'])) {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey) {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (\is_array($cssFiles)) {
                    foreach ($this->bundleConfig['unset_global_keys']['css'] as $cssFile) {
                        if (isset($cssFiles[$cssFile])) {
                            unset($cssFiles[$cssFile]);
                        }
                    }
                }
            }
        }
        if (isset($this->bundleConfig['unset_jquery']) && true === $this->bundleConfig['unset_jquery']) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];
            if (($key = array_search('assets/jquery/js/jquery.min.js|static', $jsFiles)) !== false) {
                unset($jsFiles[$key]);
            }
        }
    }
}