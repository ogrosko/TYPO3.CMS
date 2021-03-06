<?php
namespace TYPO3\CMS\Fluid\View;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request as WebRequest;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * A standalone template view.
 * Should be used as view if you want to use Fluid without Extbase extensions
 *
 * @api
 */
class StandaloneView extends AbstractTemplateView
{
    /**
     * @var ObjectManager|null
     */
    protected $objectManager = null;

    /**
     * Constructor
     *
     * @param ContentObjectRenderer $contentObject The current cObject. If NULL a new instance will be created
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function __construct(ContentObjectRenderer $contentObject = null)
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        if ($contentObject === null) {
            /** @var ContentObjectRenderer $contentObject */
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }
        $configurationManager->setContentObject($contentObject);

        /** @var WebRequest $request */
        $request = $this->objectManager->get(WebRequest::class);
        $request->setRequestUri(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $request->setBaseUri(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($request);
        /** @var ControllerContext $controllerContext */
        $controllerContext = $this->objectManager->get(ControllerContext::class);
        $controllerContext->setRequest($request);
        $controllerContext->setUriBuilder($uriBuilder);
        $renderingContext = $this->objectManager->get(RenderingContext::class, $this);
        $renderingContext->setControllerContext($controllerContext);
        parent::__construct($renderingContext);
    }

    /**
     * @param string $templateName
     */
    public function setTemplate($templateName)
    {
        $this->baseRenderingContext->setControllerAction($templateName);
    }

    /**
     * Sets the format of the current request (default format is "html")
     *
     * @param string $format
     * @throws \RuntimeException
     * @api
     */
    public function setFormat($format)
    {
        if ($this->baseRenderingContext instanceof RenderingContext) {
            $this->baseRenderingContext->getControllerContext()->getRequest()->setFormat($format);
            $this->baseRenderingContext->getTemplatePaths()->setFormat($format);
        } else {
            throw new \RuntimeException('The rendering context must be of type ' . RenderingContext::class, 1482251886);
        }
    }

    /**
     * Returns the format of the current request (defaults is "html")
     *
     * @return string $format
     * @throws \RuntimeException
     * @api
     */
    public function getFormat()
    {
        if ($this->baseRenderingContext instanceof RenderingContext) {
            return $this->baseRenderingContext->getControllerContext()->getRequest()->getFormat();
        }
        throw new \RuntimeException('The rendering context must be of type ' . RenderingContext::class, 1482251887);
    }

    /**
     * Returns the current request object
     *
     * @return WebRequest
     * @throws \RuntimeException
     */
    public function getRequest()
    {
        if ($this->baseRenderingContext instanceof RenderingContext) {
            return $this->baseRenderingContext->getControllerContext()->getRequest();
        }
        throw new \RuntimeException('The rendering context must be of type ' . RenderingContext::class, 1482251888);
    }

    /**
     * Sets the absolute path to a Fluid template file
     *
     * @param string $templatePathAndFilename Fluid template path
     * @api
     */
    public function setTemplatePathAndFilename($templatePathAndFilename)
    {
        $this->baseRenderingContext->getTemplatePaths()->setTemplatePathAndFilename($templatePathAndFilename);
    }

    /**
     * Returns the absolute path to a Fluid template file if it was specified with setTemplatePathAndFilename() before.
     * If the template filename was never specified, Fluid attempts to resolve the file based on controller and action.
     *
     * NB: If TemplatePaths was previously told to use the specific template path and filename it will short-circuit
     * and return that template path and filename directly, instead of attempting to resolve it.
     *
     * @return string Fluid template path
     * @api
     */
    public function getTemplatePathAndFilename()
    {
        $templatePaths = $this->baseRenderingContext->getTemplatePaths();
        return $templatePaths->resolveTemplateFileForControllerAndActionAndFormat(
            $this->baseRenderingContext->getControllerName(),
            $this->baseRenderingContext->getControllerAction(),
            $templatePaths->getFormat()
        );
    }

    /**
     * Sets the Fluid template source
     * You can use setTemplatePathAndFilename() alternatively if you only want to specify the template path
     *
     * @param string $templateSource Fluid template source code
     * @api
     */
    public function setTemplateSource($templateSource)
    {
        $this->baseRenderingContext->getTemplatePaths()->setTemplateSource($templateSource);
    }

    /**
     * Set the root path(s) to the templates.
     *
     * @param string[] $templateRootPaths Root paths to the templates.
     * @api
     */
    public function setTemplateRootPaths(array $templateRootPaths)
    {
        $this->baseRenderingContext->getTemplatePaths()->setTemplateRootPaths($templateRootPaths);
    }

    /**
     * Set the root path(s) to the layouts.
     *
     * @param string[] $layoutRootPaths Root path to the layouts
     * @api
     */
    public function setLayoutRootPaths(array $layoutRootPaths)
    {
        $this->baseRenderingContext->getTemplatePaths()->setLayoutRootPaths($layoutRootPaths);
    }

    /**
     * Resolves the layout root to be used inside other paths.
     *
     * @return array Fluid layout root paths
     * @throws InvalidTemplateResourceException
     * @api
     */
    public function getLayoutRootPaths()
    {
        return $this->baseRenderingContext->getTemplatePaths()->getLayoutRootPaths();
    }

    /**
     * Set the root path(s) to the partials.
     * If set, overrides the one determined from $this->partialRootPathPattern
     *
     * @param string[] $partialRootPaths Root paths to the partials. If set, overrides the one determined from $this->partialRootPathPattern
     * @api
     */
    public function setPartialRootPaths(array $partialRootPaths)
    {
        $this->baseRenderingContext->getTemplatePaths()->setPartialRootPaths($partialRootPaths);
    }

    /**
     * Returns the absolute path to the folder that contains Fluid partial files
     *
     * @return array Fluid partial root paths
     * @throws InvalidTemplateResourceException
     * @api
     */
    public function getPartialRootPaths()
    {
        return $this->baseRenderingContext->getTemplatePaths()->getPartialRootPaths();
    }

    /**
     * Checks whether a template can be resolved for the current request
     *
     * @return bool
     * @api
     */
    public function hasTemplate()
    {
        try {
            $this->baseRenderingContext->getTemplatePaths()->getTemplateSource(
                $this->baseRenderingContext->getControllerName(),
                $this->baseRenderingContext->getControllerAction()
            );
            return true;
        } catch (InvalidTemplateResourceException $e) {
            return false;
        }
    }
}
