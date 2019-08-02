<?php
namespace Sitegeist\Monocle\Controller;

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
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * NOTE: Technically this is a ModuleController (for Neos); but we do not use any special functionality.
 * Thus, we can use ActionController here; so this also work in plain Flow.
 */
class StyleguideController extends ActionController
{

    /**
     * @return void
     */
    public function indexAction()
    {
    }
}
