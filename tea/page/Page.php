<?php

namespace tea\page;

use tea\Request;

/**
 * 分页类
 *
 * <code>
 * $pager = new Page();
 * $pager->total(100);
 * $pager->setSize(21);
 * $pager->setKeyword("page");
 * $pager->setPath("pager.php");
 * $pager->setQuery("a=b&pager=%{PAGE_NO}&d=1");
 * echo $pager;
 * echo "offset:" . $pager->offset();
 * </code>
 *
 */
class Page {
	private $keyword;
	private $_total;
	private $path;
	private $size;
	private $properties;
	private $query;
	private $pageSetSize = 11;
	private $rows = [];
	
	/**
	 * 分页中代码当前页码的常量
	 *
	 */
	const PAGER_VARIABLE_STRING = "%{PAGE_NO}";		
	
	/**
	 * 构造器
	 *
	 * @since 1.0
	 */
	public function __construct() {
		if (isset($_SERVER["REDIRECT_URL"])) {
			$this->path = $_SERVER["REDIRECT_URL"];
		}
		else {
			$this->path = $_SERVER["PHP_SELF"];
		}
	}
	
	/**
	 * 取得当前页码，第一页为1
	 * 
	 * @return integer
	 */
	public function current() {
		$keyword = $this->keyword();
		$pageNo = intval(Request::shared()->param($keyword));
		if ($pageNo <= 0) {
			$pageNo = 1;
		}
		return min($pageNo, $this->length());
	}
	
	/**
	 * 取得下一页页码
	 *
	 * @return integer
	 */
	public function next() {
		$length = $this->length();
		$current = $this->current();
		return $current < $length ? ($current + 1) : $length;
	}
	
	/**
	 * 取得上一页页码
	 *
	 * @return integer
	 */
	public function prev() {
		$current = $this->current();
		return $current > 1 ? ($current - 1) : 1;
	}
	
	/**
	 * 取得记录开始的偏移量
	 *
	 * @return integer
	 */
	public function offset() {
		$offset = $this->size() * ($this->current() - 1);
		if ($offset < 0) {
			$offset = 0;
		}
		if($offset >= $this->total()){
            $offset = max($this->size () * ($this->length () - 1), 0);
        }
		return $offset;
	}
	
	/**
	 * 设置内容总数
	 *
	 * @param integer $total 内容总数
	 * @return Page
	 * @throws \Exception
	 */
	public function setTotal($total) {
		$this->_total =  intval($total);
		if ($this->_total < 0) {
			throw new \Exception("content total '{$total}' can't be small than 0");
		}
		return $this;
	}
	
	/**
	 * 数据总数
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function total() {
		return $this->_total;
	}
	
	/**
	 * 设置分页链接中的关键字
	 *
	 * @param string $keyword 关键字
	 * @return Page
	 */
	public function setKeyword($keyword) {
		$this->keyword = $keyword;
		return $this;
	}
	
	/**
	 * 取得分页用的关键字
	 *
	 * 从1.0开始，如果没有关键字，则默认为page
	 * 
	 * @return string
	 */
	public function keyword() {
		if (!$this->keyword) {
			$this->keyword = "page";
		}
		return $this->keyword;
	}
	
	/**
	 * 设置每页记录数
	 *
	 * @param integer $size 大于0的数字
	 * @return Page
	 * @throws \Exception
	 */
	public function setSize($size) {
		$this->size = intval($size);
		if ($this->size < 1) {
			throw new \Exception("page size '{$size}' can't be small than 1");
		}
		return $this;
	}
	
	/**
	 * 取得每页记录数
	 *
	 * @return integer
	 */
	public function size() {
		if ($this->size < 1) {
			$this->size = 10;
		}
		return $this->size;
	}
	
	/**
	 * 设置链接的路径
	 *
	 * @param string $path 路径
	 * @return Page
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}
	
	/**
	 * 取得程序路径
	 *
	 * @return string
	 * @since 1.0
	 */
	public function path() {
		return $this->path;
	}
	
	/**
	 * 设置属性
	 *
	 * @param array $properties 属性列表
	 * @return Page
	 */
	public function setProperties(array $properties) {
		$this->properties = $properties;
		return $this;
	}
	
	/**
	 * 取得设置的属性
	 *
	 * @return array
	 * @since 1.0
	 */
	public function properties() {
		return $this->properties;
	}
	
	/**
	 * 设置查询
	 *
	 * @param mixed $query string|array
	 * @return Page
	 */
	public function setQuery($query) {
		if (is_array($query)) {
			$_query = [];
			foreach ($query as $key => $value) {
				if ($key == $this->keyword()) {
					continue;
				}
				if (is_array($value)) {
					foreach ($value as $key1=>$value1) {
						$_query[] = "{$key}[]=" . urlencode($value1);
					}
				}
				else {
					$_query[] = "{$key}=" . urlencode($value);
				}
			}
			$query = implode("&", $_query);
		}
		$this->query = $query;
		return $this;
	}
	
	/**
	 * 添加查询条件
	 * 
	 * <code>
	 * $page->addQuery(array(
	 *		 "e" => 5,
	 *		 "f" => 6
	 *	));
	 *	$page->addQuery("g=7");
	 * </code>
	 *
	 * @param mixed $query string|array
	 * @return Page
	 * @since 1.0
	 */
	public function addQuery($query) {
		if (is_array($query)) {
			$_query = [];
			foreach ($query as $key => $value) {
				if ($key == $this->keyword()) {
					continue;
				}
				if (is_array($value)) {
					foreach ($value as $key1=>$value1) {
						$_query[] = "{$key}[]=" . urlencode($value1);
					}
				}
				else {
					$_query[] = "{$key}=" . urlencode($value);
				}
			}
			$query = implode("&", $_query);
		}
		$this->query .= ($this->query ? "&" : "") . $query;
		return $this;
	}
	
	/**
	 * 开启自动构造查询条件功能
	 *
	 * @param boolean $bool 是否开启该功能
	 * @param string|array $except 要去除的参数名
	 * @param string|array $only 限制的参数名
	 * @return Page
	 * @since 1.0
	 */
	public function setAutoQuery($bool = true, $except = "", $only = "") {
		if ($bool) {
			$x = Request::shared()->params();
			foreach ($x as $name => $value) {
				if ($except && \tea\Arrays::in($name, $except)) {
					unset($x[$name]);
				}
				if ($only && !\tea\Arrays::in($name, $only)) {
					unset($x[$name]);
				}
			}
			$this->setQuery($x);
		}
		return $this;
	}
	
	/**
	 * 取得查询
	 *
	 * @return array
	 * @since 1.0
	 */
	public function query() {
		return $this->query;
	}
	
	/**
	 * 取得一个分页好号对应的URL
	 *
	 * @param integer $pageNo 分页号
	 * @return string
	 * @since 1.0
	 */
	public function url($pageNo) {
		$query = $this->query();
		if (strstr($query, self::PAGER_VARIABLE_STRING)) {
			$query = str_replace(self::PAGER_VARIABLE_STRING, $pageNo, $query);
		}
		else {
			if ($query == "") {
				$query = $this->keyword() . "=" . $pageNo;
			}
			else {
				$query .= "&" . $this->keyword() . "=" . $pageNo;
			}
		}
		return $this->path() . "?" . $query;
	}
	
	/**
	 * 取得总分页数
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function length() {
		if ($this->size() == 0) {
			return 0;
		}
		return ceil($this->total()/$this->size());
	}
	
	/**
	 * 添加记录
	 *
	 * @param mixed $row 记录
	 * @return Page
	 */
	public function addRow($row) {
		$this->rows[] = $row;
		return $this;
	}
	
	/**
	 * 添加记录集
	 *
	 * @param array $rows 记录集
	 * @return Page
	 */
	public function addRows(array $rows) {
		foreach ($rows as $row) {
			$this->rows[] = $row;
		}
		return $this;
	}
	
	/**
	 * 取得记录集
	 *
	 * @return array
	 */
	public function rows() {
		return $this->rows;
	}
	
	/**
	 * 设置记录集
	 *
	 * @param array $rows 记录集
	 * @return Page
	 */
	public function setRows($rows) {
		$this->rows = $rows;
		return $this;
	}
	
	/**
	 * 设置分页集尺寸
	 *
	 * @param integer $num 大于1
	 * @return Page
	 * @since 1.0
	 */
	public function setPageSetNum($num){
		$this->pageSetSize = $num;
		return $this;
	}
	
	/**
	 * 取得分页集尺寸
	 *
	 * @return integer
	 * @since 1.0
	 */
	public function pageSetNum(){
		return $this->pageSetSize;
	}

	public function __get($prop) {
		if (method_exists($this, $prop)) {
			return $this->$prop();
		}
	}

	public function info() {
		return [
			"current" => $this->current(),
			"size" => $this->size(),
			"total" => $this->total(),
			"length" => $this->length()
		];
	}

	public function asHtml() {
		$pages = [];
		$pageNum = $this->length();
		$currPageNo = $this->current();
		$size = $this->size();
		$total = $this->total();
		$pageSetNum = $this->pageSetNum();
		$middlePageNum = ceil($pageSetNum/2);
		if ($pageNum > 0) {
			if ($currPageNo <= $middlePageNum) {
				$start = 1;
				$end = min($pageNum, $pageSetNum);
			}
			else if ($currPageNo + $middlePageNum - 1 > $pageNum) {
				$start = max(1, $pageNum - $pageSetNum - 1);
				$end = $pageNum;
			}
			else {
				$start = max(1, $currPageNo - $middlePageNum);
				$end = min($currPageNo + $middlePageNum - 1, $pageNum);
			}

			if ($currPageNo > 1) {
				$pages[] = "<a class=\"item\" href=\"" . $this->url(1)  . "\" title=\"首页\">首页</a>";
				$pages[] = "<a class=\"item prev\" href=\"" . $this->url($currPageNo - 1)  . "\" title=\"前 {$size}\">前页</a>";
			}
			else {
				$pages[] = "<a class=\"item disabled\" href=\"" . $this->url(1)  . "\" title=\"首页\">首页</a>";
				$pages[] = "<a class=\"item prev disabled\" href=\"" . $this->url(1)  . "\" title=\"前 {$size}\">前页</a>";
			}
			for ($i = $start; $i <= $end; $i++) {
				$_start = $size * ($i - 1) + 1;
				$_end = min($size * $i, $total);
				if ($i != $currPageNo) {
					$pages[] = "<a class=\"item\" href=\"" . $this->url($i) . "\" title=\"结果 {$_start} - {$_end}\">{$i}</a>";
				}
				else {
					$pages[] = "<a class=\"item active\" href=\"" . $this->url($i) . "\" title=\"结果 {$_start} - {$_end}\">{$i}</a>";
				}
			}
			if ($currPageNo < $pageNum) {
				$pages[] = "<a class=\"item next\" href=\"" . $this->url($currPageNo + 1) . "\" title=\"后 {$size}\">后页</a>";
				$pages[] = "<a class=\"item\" href=\"" . $this->url($pageNum)  . "\" title=\"尾页\">尾页</a>";
			}
			else {
				$pages[] = "<a class=\"item next disabled\" href=\"" . $this->url($pageNum) . "\" title=\"后 {$size}\">后页</a>";
				$pages[] = "<a class=\"item disabled\" href=\"" . $this->url($pageNum)  . "\" title=\"尾页\">尾页</a>";
			}
		}

		$string = implode("", $pages);

		if ($this->length() == 1) {
			return "";
		}
		return "<div class=\"page\">" . $string . "</div>";
	}

	public function __toString() {
		return $this->asHtml();
	}
}


?>