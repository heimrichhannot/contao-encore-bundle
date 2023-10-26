<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Exception\NoEntrypointsException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class LayoutContainer
{
    protected array             $bundleConfig;
    protected ContaoFramework   $contaoFramework;
    private RequestStack        $requestStack;
    private ScopeMatcher        $scopeMatcher;
    private EntryCollection     $entryCollection;
    private TranslatorInterface $translator;

    /**
     * LayoutContainer constructor.
     */
    public function __construct(array $bundleConfig, ContaoFramework $contaoFramework, RequestStack $requestStack, ScopeMatcher $scopeMatcher, EntryCollection $entryCollection, TranslatorInterface $translator)
    {
        $this->bundleConfig = $bundleConfig;
        $this->contaoFramework = $contaoFramework;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
        $this->entryCollection = $entryCollection;
        $this->translator = $translator;
    }

    /**
     * @Callback(table="tl_layout", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$dc || !$this->scopeMatcher->isBackendRequest($request) || !($layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk($dc->id))) {
            return;
        }

        $messageAdapter = $this->contaoFramework->getAdapter(Message::class);
        if ($layout->addEncore) {
            if ($messageAdapter->hasMessages('huh.encore.error.noEntryPoints')) {
                $messageAdapter->addError($messageAdapter->generateUnwrapped('huh.encore.error.noEntryPoints', true));
            } else {
                try {
                    $this->entryCollection->getEntries();
                } catch (NoEntrypointsException $e) {
                    $messageAdapter->addError('[Encore Bundle] '.$this->translator->trans('huh.encore.errors.noEntrypoints').' '.$e->getMessage());
                }
            }
        }

        if ($layout->addEncore && $layout->addJQuery && (!isset($this->bundleConfig['unset_jquery']) || true !== $this->bundleConfig['unset_jquery'])) {
            $messageAdapter->addInfo(($GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] ?: ''));
        }
    }

    /**
     * @Callback(table="tl_layout", target="fields.encoreStylesheetsImportsTemplate.options")
     * @Callback(table="tl_layout", target="fields.encoreScriptsImportsTemplate.options")
     */
    public function onImportTemplateOptionsCallback(): array
    {
        $options = [];

        if (!isset($this->bundleConfig['templates']['imports'])) {
            return $options;
        }

        foreach ($this->bundleConfig['templates']['imports'] as $template) {
            $options[$template['name']] = $template['template'];
        }

        asort($options);

        return $options;
    }
}
