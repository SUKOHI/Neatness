<?php namespace Sukohi\Neatness;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

trait NeatnessTrait {

	private $_neatness_order_by = 'orderby',
			$_neatness_direction = 'direction',
			$_neatness_direction_values = [
				'asc', 'desc'
			],
			$_neatness_db_query = null;

	public function scopeNeatness($query, $default_column = '', $default_direction = '', $before_filter = null) {

		$this->_neatness_db_query = $query;

		if(!empty($default_column) && !empty($default_direction)) {

			$this->neatness['default'][0] = $default_column;
			$this->neatness['default'][1] = $default_direction;

		}

		if(is_callable($before_filter)) {

			$before_filter($this);

		}

		$column = $this->getSortColumn();
		$direction = $this->getSortDirection();

		if(empty($column) || empty($direction)) {

			$column = $this->getDefaultColumn();
			$direction = $this->getDefaultDirection();

		}

		$sort_columns = explode('|', $column);

		foreach ($sort_columns as $index => $sort_column) {

			$this->_neatness_db_query->orderBy($sort_column, $direction);

		}

		$results = new \stdClass();
		$results->column = $column;
		$results->direction = $direction;
		$results->appends = $this->getAppends($column, $direction);
		$results->urls = $this->getUrls($column, $direction);
		$results->labels = $this->getLabels();
		$results->symbols = $this->getSymbols($column, $direction);
		$results->texts = $this->getText($results);
		View::Share('neatness', $results);

	}

	public function getSortColumn() {

		$column = '';
		$request_column = strtolower(Request::get($this->_neatness_order_by));

		if(in_array($request_column, $this->getColumns())) {

			$column = $request_column;

		}

		return $column;

	}

	public function setSortColumn($column) {

		Request::merge([
			$this->_neatness_order_by => $column
		]);

	}

	public function getSortDirection() {

		$direction = '';
		$request_direction = strtolower(Request::get($this->_neatness_direction));

		if(in_array($request_direction, $this->_neatness_direction_values)) {

			$direction = $request_direction;

		}

		return $direction;

	}

	public function setSortDirection($direction) {

		Request::merge([
			$this->_neatness_direction => $direction
		]);

	}

	public function getQuery() {

		return $this->_neatness_db_query;

	}

	private function getDefaultColumn() {

		return $this->neatness['default'][0];

	}

	private function getDefaultDirection() {

		return $this->neatness['default'][1];

	}

	private function getReverseDirection($direction) {

		$values = $this->_neatness_direction_values;

		if($values[0] == $direction) {

			return $values[1];

		}

		return $values[0];

	}

	private function getAppends($column, $direction) {

		$original_params = [];

		if(isset($this->neatness['appends'])) {

			$original_params = Request::only($this->neatness['appends']);

		} else {

			$original_params = Request::except([
				$this->_neatness_order_by,
				$this->_neatness_direction
			]);

		}

		return $original_params + [
			$this->_neatness_order_by => $column,
			$this->_neatness_direction => $direction
		];

	}

	private function getUrls($current_column, $current_direction) {

		$original_params = [];

		if(isset($this->neatness['appends'])) {

			$original_params = Request::only($this->neatness['appends']);

		} else {

			$original_params = Request::except([
				$this->_neatness_order_by,
				$this->_neatness_direction
			]);

		}

		$urls = new \stdClass();

		foreach ($this->getColumns() as $column) {

			$params = $original_params + [
					$this->_neatness_order_by => $column,
					$this->_neatness_direction => ($column == $current_column) ? $this->getReverseDirection($current_direction) : $this->getDefaultDirection()
				];
			$urls->$column = Request::url() .'?'. http_build_query($params);

		}

		return $urls;

	}

	private function getSymbols($current_column, $current_direction) {

		$symbols = new \stdClass();

		if(!isset($this->neatness['symbols'])) {

			return $symbols;

		}

		$original_symbols = $this->neatness['symbols'];

		foreach ($this->getColumns() as $column) {

			$symbol = $original_symbols['default'];

			if(Request::has($this->_neatness_order_by) &&
				Request::has($this->_neatness_direction) &&
				$column == $current_column) {

				$symbol = $original_symbols[$current_direction];

			}

			$symbols->$column = $symbol;

		}

		return $symbols;

	}

	private function getColumns() {

		return array_keys($this->neatness['columns']);

	}

	private function getLabels() {

		$labels = new \stdClass();

		foreach ($this->neatness['columns'] as $column => $label) {

			$labels->$column = $label;

		}

		return $labels;

	}

	private function getText($results) {

		$texts = new \stdClass();

		foreach ($results->labels as $column => $label) {

			$symbol = (isset($results->symbols->$column)) ? ' '. $results->symbols->$column : '';
			$texts->$column = $label . $symbol;

		}

		return $texts;

	}

}