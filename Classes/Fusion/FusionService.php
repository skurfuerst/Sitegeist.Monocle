<?php
namespace Sitegeist\Monocle\Fusion;

/**
 * This file is part of the Sitegeist.Monocle package
 *
 * (c) 2016
 * Martin Ficzel <ficzel@sitegeist.de>
 * Wilhelm Behncke <behncke@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use \Neos\Neos\Domain\Service\FusionService as NeosFusionService;



try {
    class_exists("Neos\Neos\Domain\Service\FusionService");


    /**
     * Class FusionService
     * @package Sitegeist\Monocle\Fusion
     */
    class FusionService extends NeosFusionService
    {
        const RENDERPATH_DISCRIMINATOR = 'monoclePrototypeRenderer_';

        /**
         * @Flow\InjectConfiguration(path="fusion.autoInclude", package="Neos.Neos")
         * @var array
         */
        protected $autoIncludeConfiguration = array();

        /**
         * Returns a merged fusion object tree in the context of the given site-package
         *
         * @param string $siteResourcesPackageKey
         * @return array The merged object tree as of the given node
         * @throws \Neos\Neos\Domain\Exception
         * @deprecated
         */
        public function getMergedTypoScriptObjectTreeForSitePackage($siteResourcesPackageKey)
        {
            return $this->getMergedFusionObjectTreeForSitePackage($siteResourcesPackageKey);
        }

        /**
         * Returns a merged fusion object tree in the context of the given site-package
         *
         * @param string $siteResourcesPackageKey
         * @return array The merged object tree as of the given node
         * @throws \Neos\Neos\Domain\Exception
         */
        public function getMergedFusionObjectTreeForSitePackage($siteResourcesPackageKey)
        {
            $siteRootFusionPathAndFilename = sprintf($this->siteRootFusionPattern, $siteResourcesPackageKey);

            $mergedFusionCode = '';
            $mergedFusionCode .= $this->generateNodeTypeDefinitions();
            $mergedFusionCode .= $this->getFusionIncludes($this->prepareAutoIncludeFusion());
            $mergedFusionCode .= $this->getFusionIncludes($this->prependFusionIncludes);
            $mergedFusionCode .= $this->readExternalFusionFile($siteRootFusionPathAndFilename);
            $mergedFusionCode .= $this->getFusionIncludes($this->appendFusionIncludes);

            return $this->fusionParser->parse($mergedFusionCode, $siteRootFusionPathAndFilename);
        }


        /**
         * Get all styleguide objects for the given fusion-ast
         *
         * @param array $fusionAst
         * @return array
         */
        public function getStyleguideObjectsFromFusionAst($fusionAst)
        {
            $styleguideObjects = [];
            if ($fusionAst && $fusionAst['__prototypes']) {
                foreach ($fusionAst['__prototypes'] as $prototypeFullName => $prototypeObject) {
                    if (array_key_exists('__meta', $prototypeObject) && is_array($prototypeObject['__meta']) && array_key_exists('styleguide', $prototypeObject['__meta'])) {
                        list($prototypeVendor, $prototypeName) = explode(':', $prototypeFullName, 2);
                        $styleguideConfiguration = $prototypeObject['__meta']['styleguide'];
                        $styleguideObjects[$prototypeFullName] = [
                            'title' => (isset($styleguideConfiguration['title'])) ? $styleguideConfiguration['title'] : implode(' ', array_reverse(explode('.', $prototypeName))),
                            'path' => (isset($styleguideConfiguration['path'])) ? $styleguideConfiguration['path'] : $prototypeName,
                            'description' => (isset($styleguideConfiguration['description'])) ? $styleguideConfiguration['description'] :  '',
                            'options' => (isset($styleguideConfiguration['options'])) ? $styleguideConfiguration['options'] : null,
                        ];
                    }
                }
            }
            return $styleguideObjects;
        }

        /**
         * Get anatomical prototype tree from fusion AST excerpt
         *
         * @param array $fusionAstExcerpt
         * @return array
         */
        public function getAnatomicalPrototypeTreeFromAstExcerpt($fusionAstExcerpt)
        {
            $result = [];

            if (!is_array($fusionAstExcerpt)) {
                return $result;
            }

            foreach ($fusionAstExcerpt as $key => $value) {
                if (substr($key, 0, 2) === '__') {
                    continue;
                }

                $anatomy = $this->getAnatomicalPrototypeTreeFromAstExcerpt($value);

                if (array_key_exists('prototypeName', $anatomy)) {
                    if ($anatomy['prototypeName'] !== null) {
                        $result[] = $anatomy;
                    }
                } else {
                    $result = array_merge($result, $anatomy);
                }
            }

            if (!array_key_exists('__objectType', $fusionAstExcerpt)) {
                return $result;
            } else {
                return [
                    'prototypeName' => $fusionAstExcerpt['__objectType'],
                    'children' => $result
                ];
            }
        }
    }


} catch (\Exception $e) {
    class FusionService {

    }
}

