<?php
namespace Famelo\Navigation\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3.Expose package.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 *
 * @api
 */
class NavigationViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * The AccessDecisionVoterManager
	 *
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionVoterManager
	 * @Flow\Inject
	 */
	protected $accessDecisionVoterManager;

	/**
	 * The policyService
	 *
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

	/**
	 * @param string $path
	 * @param string $as
	 * @param boolean $nested
	 * @return string
	 */
	public function render($path = NULL, $as = 'items', $nested = TRUE) {
		$routes = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_ROUTES);

		$items = $this->parseRoutes($routes);

		if ($nested || TRUE) {
			$items = $this->nest($items);
		}

		$this->templateVariableContainer->add($as, $items);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		return $output;
	}

	public function parseRoutes($routes) {
		$items = array();
		$classTemplate = '{package}\Controller\{controller}Controller';
		foreach ($routes as $route) {
			if (isset($route['navigation'])) {

				$item = array();
				$item['label'] = $route['navigation'];
				$item['uriPattern'] = $route['uriPattern'];
				$searchAndReplace = array();
				foreach ($route['defaults'] as $key => $value) {
					$item[ltrim($key, '@')] = $value;
					$searchAndReplace['{' . ltrim($key, '@') . '}'] = str_replace('.', '\\', $value);
				}

				$className = str_replace(array_keys($searchAndReplace), array_values($searchAndReplace), $classTemplate);
				try {
					$joinPoint = new \Famelo\Navigation\Aop\VirtualJoinPoint();
					$joinPoint->setClassName($className);
					$joinPoint->setMethodName($route['defaults']['@action'] . 'Action');
					$vote = $this->accessDecisionVoterManager->decideOnJoinPoint($joinPoint);
				} catch (\TYPO3\Flow\Security\Exception\AccessDeniedException $e) {
					continue;
				}

				$items[] = $item;
			}
		}
		return $items;
	}

	public function nest($items) {
		$nested = array();

		$originalOrder = array();
		foreach ($items as $key => $item) {
			$items[$key]['sorting'] = $key;
		}

		usort($items, function($a, $b){
			return substr_count($a['uriPattern'], '/') > substr_count($b['uriPattern'], '/');
		});

		$map = array();
		foreach ($items as $item) {
			$parts = explode('/', $item['uriPattern']);
			$fullPath = implode('.', $parts);
			$item = new \Famelo\Navigation\Core\NavigationItem($item);
			while (!empty($parts)) {
				$path = implode('.children.', $parts);
				if (isset($map[$path])) {
					$map[$path]->addChild($item);
				} elseif (count($parts) == 1) {
					$nested[$path] = $item;
					$map[$fullPath] = $item;
				}
				$lastPart = array_pop($parts);
			}
		}

		uasort($nested, function($a, $b){
			return $a->offsetGet('sorting') > $b->offsetGet('sorting');
		});

		return $nested;
	}
}

?>