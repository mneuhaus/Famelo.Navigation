<?php
namespace Famelo\Navigation\Core;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class NavigationItem {
	private $container = array();

	public function __construct($items) {
		$this->container = $items;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->container[] = $value;
		} else {
			$this->container[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->container[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->container[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->container[$offset]) ? $this->container[$offset] : NULL;
	}

	public function addChild($child) {
		if (!isset($this->container['children'])) {
			$this->container['children'] = array();
		}
		$this->container['children'][] = $child;

		uasort($this->container['children'], function($a, $b){
			return $a->offsetGet('sorting') > $b->offsetGet('sotring');
		});
	}

	public function getArray() {
		return $this->container;
	}

	public function getLabel() {
		return $this->container['label'];
	}

	public function getChildren() {
		return isset($this->container['children']) ? $this->container['children'] : array();
	}
}
?>