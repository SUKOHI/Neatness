<?php namespace Sukohi\Neatness;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

trait NeatnessTrait {

	private $_neatness_order_by = 'orderby',
		$_neatness_direction = 'direction',
		$_neatness_direction_values = ['asc', 'desc'],
		$_neatness_db_query = null;

	public function scopeNeatness($query, $default_key = '', $default_direction = '') {

		$this->_neatness_db_query = $query;

		if(!empty($default_key) && !empty($default_direction)) {

			$this->neatness['default'][0] = $default_key;
			$this->neatness['default'][1] = $default_direction;

		}

		$key = $this->getNeatnessKey();
		$column = $this->getNeatnessColumn($key);
		$direction = $this->getNeatnessDirection();

		if(strpos($column, 'scope::') === 0) {

			$method = camel_case(str_replace('::', '_', $column));

			if(method_exists($this, $method)) {

				$this->$method($query, $direction);

			} else {

				throw new \Exception('Method '. $method .'() Not Found.');

			}

		} else {

			$sort_columns = explode('|', $column);

			foreach ($sort_columns as $index => $sort_column) {

				$this->_neatness_db_query->orderBy($sort_column, $direction);

			}

		}

		$results = new \stdClass();
		$results->key = $key;
		$results->column = $column;
		$results->direction = $direction;
		$results->appends = $this->getAppends($key, $direction);
		$results->urls = $this->getNeatnessUrls($key, $direction);
		$results->labels = $this->getNeatnessLabels();
		$results->symbols = $this->getNeatnessSymbols($key, $direction);
		$results->texts = $this->getNeatnessTexts($results);
		View::Share('neatness', $results);

	}

	private function getNeatnessKey() {

		$name = $this->_neatness_order_by;

		if(Request::has($name)) {

			return Request::get($name);

		}

		return $this->neatness['default'][0];

	}

	private function getNeatnessColumn($key) {

		if(isset($this->neatness['columns'][$key])) {

			return $this->neatness['columns'][$key];

		}

		$key = $this->neatness['default'][0];
		return $this->neatness['columns'][$key];

	}

	private function getNeatnessDirection() {

		$direction = $this->neatness['default'][1];
		$request_direction = Request::get($this->_neatness_direction);

		if(in_array($request_direction, $this->_neatness_direction_values)) {

			$direction = $request_direction;

		}

		return $direction;

	}

	private function getNeatnessReverseDirection($direction) {

		$values = $this->_neatness_direction_values;

		if($values[0] == $direction) {

			return $values[1];

		}

		return $values[0];

	}

	private function getAppends($key, $direction) {

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
			$this->_neatness_order_by => $key,
			$this->_neatness_direction => $direction
		];

	}

	private function getNeatnessUrls($current_key, $current_direction) {

		$original_params = [];

		if(isset($this->neatness['appends'])) {

			$original_params = Request::only($this->neatness['appends']);

		} else {

			$original_params = Request::except([
				$this->_neatness_order_by,
				$this->_neatness_direction
			]);

		}

		$urls = [];

		foreach ($this->neatness['columns'] as $key => $column) {

			$direction = ($key == $current_key) ? $this->getNeatnessReverseDirection($current_direction) : $this->neatness['default'][1];
			$params = $original_params + [
					$this->_neatness_order_by => $key,
					$this->_neatness_direction => $direction
				];
			$urls[$key] = Request::url() .'?'. http_build_query($params);

		}

		return Collection::make($urls);

	}

	private function getNeatnessSymbols($current_key, $current_direction) {

		$symbols = [];

		if(!isset($this->neatness['symbols'])) {

			return $symbols;

		}

		$original_symbols = $this->neatness['symbols'];

		foreach ($this->neatness['columns'] as $key => $column) {

			$symbol = $original_symbols['default'];

			if(Request::has($this->_neatness_order_by) &&
				Request::has($this->_neatness_direction) &&
				$key == $current_key) {

				$symbol = $original_symbols[$current_direction];

			}

			$symbols[$key] = $symbol;

		}

		return Collection::make($symbols);

	}

	private function getNeatnessTexts($results) {

		$texts = [];

		foreach ($results->labels as $key => $label) {

			$symbol = $results->symbols->get($key);
			$texts[$key] = $label .' '. $symbol;

		}

		return Collection::make($texts);

	}

	private function getNeatnessLabels() {

		$labels = array_get($this->neatness, 'labels', []);

		foreach ($labels as $key => $label) {

			if(starts_with($label, 'label::')) {

				$method = camel_case(str_replace('::', '_', $label));

				if(method_exists($this, $method)) {

					$labels[$key] = $this->$method();

				} else {

					throw new \Exception('Method '. $method .'() Not Found.');

				}

			}

		}

		return Collection::make($labels);

	}

}