<?php

namespace tea\upload;

use tea\Arrays;

/**
 * 上传服务
 */
class Upload {
	private $_items = [];

	/**
	 * 取得服务实例
	 *
	 * @return self 服务实例
	 */
	public static function new() {
		return new self;
	}

	/**
	 * 添加新的条目
	 *
	 * - add($field)
	 * - add([ $field, $index ])
	 * - add([ $field, $index1, $index2, ... ])
	 * - add(Item $item)
	 *
	 * @param string|array|Item $item 条目
	 * @param array $validator 校验规则
	 * @return null|Item
	 */
	public function add($item, array $validator = []) {
		if (is_string($item)) {
			$item = new Item($item);
			$item->setValidator($validator);
			$this->_items[] = $item;
		}
		else if (is_array($item) && count($item) >= 2) {
			$item = new Item($item[0], array_slice($item, 1));
			$item->setValidator($validator);
			$this->_items[] = $item;
		}
		else if (is_object($item) && !($item instanceof Item)) {
			/* @var Item $item  */
			$item->setValidator($validator);
			$this->_items[] = $item;
		}
		else {
			$item = null;
		}
		return $item;
	}

	/**
	 * 批量添加新的条目
	 *
	 * @param array $fields 条目字段名称集合
	 * @param array $validator 校验规则
	 * @return item[]
	 */
	public function addAll(array $fields, array $validator = []) {
		$items = [];
		foreach ($fields as $field) {
			$items[] = $this->add($field, $validator);
		}
		return $items;
	}

	/**
	 * 开始接收
	 */
	public function receive() {
		foreach ($this->_items as $item) {
			/** @var Item $item */
			if ($item->index() === false) {
				if (isset($_FILES[$item->field()])) {
					$this->_setItemInfo($item, $_FILES[$item->field()]);
					$item->validate();
				}
			}
			else {
				if (isset($_FILES[$item->field()]) && is_array($_FILES[$item->field()])) {
					$infos = $_FILES[$item->field()];
					$index = $item->index();

					$item->setName(Arrays::get($infos["name"], $index));
					$item->setType(Arrays::get($infos["type"], $index));
					$item->setTmp(Arrays::get($infos["tmp_name"], $index));
					$item->setError(Arrays::get($infos["error"], $index));
					$item->setSize(Arrays::get($infos["size"], $index));
					$item->validate();
				}
			}
		}
	}

	/**
	 * 接收图片
	 *
	 * @param string $field 表单字段名
	 * @param string $path 目标路径
	 * @param null|string $dir 目标路径的父级目录
	 * @return Item
	 */
	public function receiveImage($field, $path, $dir = null) {
		$item = $this->add($field, [
			"ext" => [ "jpg", "jpeg", "png", "gif", "bmp" ]
		]);
		$this->receive();
		if ($item->success()) {
			$item->put($path, $dir);
		}
		return $item;
	}

	/**
	 * 设置条目信息
	 *
	 * @param Item $item 条目对象
	 * @param array $info 信息
	 */
	private function _setItemInfo($item, array $info) {
		if (isset($info["name"])) {
			$item->setName($info["name"]);
		}
		if (isset($info["type"])) {
			$item->setType($info["type"]);
		}
		if (isset($info["tmp_name"])) {
			$item->setTmp($info["tmp_name"]);
		}
		if (isset($info["error"])) {
			$item->setError($info["error"]);
		}
		if (isset($info["size"])) {
			$item->setSize($info["size"]);
		}
	}

	/**
	 * 取得所有条目
	 *
	 * @return Item[]
	 */
	public function items() {
		return $this->_items;
	}

	/**
	 * 获取单个条目对象
	 *
	 * @param string $field 条目字段名
	 * @param integer|boolean $index 索引，用在多个同名文件选择框批量上传
	 * @return Item
	 */
	public function item($field, $index = false) {
		foreach ($this->_items as $item) {
			/* @var Item $item */
			if ($item->field() === $field && $item->index() === $index) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * 判断是否成功
	 *
	 * @return boolean true|false
	 */
	public function success() {
		foreach ($this->_items as $item) {
			/* @var Item $item */
			if (!$item->success()) {
				return false;
			}
		}
		return true;
	}
}

?>