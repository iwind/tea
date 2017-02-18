<?php

namespace tea\upload;

use tea\Arrays;

/**
 * 条目集合
 */
class Items {
	private $_upload = null;

	/**
	 * @var Item[]
	 */
	private $_successItems = [];

	/**
	 * 创建供参数使用的对象
	 *
	 * @param string $param 参数
	 * @return Items
	 */
	public static function newForParam($param) {
		return new self($param);
	}

	/**
	 * 构造器
	 *
	 * @param string $field 字段名
	 */
	public function __construct($field) {
		$this->_upload = Upload::new();

		$names = Arrays::get($_FILES, $field . ".name");
		if (is_array($names)) {
			foreach (array_keys($names) as $name) {
				$this->_upload->add([ $field, $name ]);
			}
		}
		$this->_upload->receive();

		foreach ($this->_upload->items() as $item) {
			if ($item->success()) {
				$this->_successItems[] = $item;
			}
		}
	}

	/**
	 * 所有条目是否都上传成功
	 *
	 * @return bool
	 */
	public function success() {
		return $this->_upload->success();
	}

	/**
	 * 执行校验
	 *
	 * @param array $rules 校验规则
	 * @return bool
	 */
	public function validate(array $rules) {
		$result = true;
		$items = [];
		foreach ($this->_successItems as $item) {
			$item->setValidator($rules);
			if (!$item->validate()) {
				$result = false;
			}
			else {
				$items[] = $item;
			}
		}
		$this->_successItems = $items;
		return $result;
	}

	/**
	 * 获取上传成功的条目
	 *
	 * @return Item[]
	 */
	public function array() {
		return $this->_successItems;
	}

	/**
	 * 获取所有条目
	 *
	 * 包括成功和失败的
	 *
	 * @return Item[]
	 */
	public function all() {
		return $this->_upload->items();
	}

	/**
	 * 获取某个位置上的条目
	 *
	 * @param int $index 位置
	 * @return null|Item
	 */
	public function at($index) {
		return $this->_successItems[$index] ?? null;
	}

	/**
	 * 使用索引（可能是数字）来获取条目
	 *
	 * @param string|int $index 索引
	 * @return Item
	 */
	public function get($index) {
		foreach ($this->_successItems as $item) {
			$itemIndex = $item->index();
			if ((is_array($itemIndex) && $itemIndex == explode(".", $index)) || $itemIndex == $index) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * 条目数量
	 *
	 * @return int
	 */
	public function count() {
		return count($this->_successItems);
	}

	/**
	 * 获取第一个条目
	 *
	 * @return null|Item
	 */
	public function first() {
		if (count($this->_successItems) > 0) {
			return $this->_successItems[0];
		}
		return null;
	}

	/**
	 * 获取最后一个目录
	 *
	 * @return null|Item
	 */
	public function last() {
		$count = count($this->_successItems);
		if ($count > 0) {
			return $this->_successItems[$count - 1];
		}
		return null;
	}

	/**
	 * 判断是否为空
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->_successItems);
	}
}

?>