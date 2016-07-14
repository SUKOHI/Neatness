# Neatness
A Laravel package to automatically add sorting system for DB query and provide URLs to switch between ASC and DESC.  
(This is for Laravel 5+. [For Laravel 4.2](https://github.com/SUKOHI/Neatness/tree/3.0))

[Demo](http://demo-laravel52.capilano-fw.com/neatness)

# Installation

Execute composer command.

    composer require sukohi/neatness:4.*

# Preparation

At first, set `NeatnessTrait` in your Model.

    use Sukohi\Neatness\NeatnessTrait;
    
    class Item extends Eloquent {
    
        use NeatnessTrait;

    }

Secondary, add configuration values also in your Model.

**default:** Default key and direction. (Required)  
**columns:** Keys and column names you want to sort. (Required)  
**symbols:** Labels you will be able to use in your View. (Optional)  
**symbols:** Symbols you will be able to use in your View. (Optional)  
**appends:** Keys you want to append to URLs. (Optional)  

    protected $neatness = [
        'default' => ['sort_id', 'desc'],
        'columns' => [
            'sort_id' => 'id',
            'sort_title' => 'title',
            'sort_date' => 'created_at'
        ],
        'labels' => [
            'sort_id' => 'ID',
            'sort_title' => 'Title',
            'sort_date' => 'Date'
        ],
        'symbols' => [
            'asc' => '<i class="fa fa-sort-asc"></i>',
            'desc' => '<i class="fa fa-sort-desc"></i>',
            'default' => '<i class="fa fa-sort"></i>'
        ],
        'appends' => ['name']
    ];

**Multiple columns:** If you want to sort by multiple columns, you can use delimiter `|` like so.

    'columns' => [
        'id_n_title' => 'id|title'
    ],

**Query Scope:** You also can utilize `Query Scopes` instead of column name.  

    'columns' => [
        'scope_title' => 'scope::sortTitle'
    ],

in this case, you need to prepare a scope method in your model. ([About Query Scopes](https://laravel.com/docs/4.2/eloquent#query-scopes))
    
    public function scopeSortTitle($query, $direction) {

        return $query->orderBy('title', $direction);

    }

**Label:** You can use `label::` prefix to call a specific method.

    'labels' => [
        'title' => 'label::SortTitle'
    ],
    
in this case, you need to prepare a method in your model.  

    public function labelSortTitle() {

        return 'Your Title'.

    }

# Usage

Now you can use a method called `neatness`.

(in Controller)

    $items = Item::neatness()->get();

After call `neatness()`, you can access to sort data through `$neatness`.
    
(in View)

**key:** The key name sorting now.

    Column: {{ $neatness->key }}
    
**column:** The column name sorting now.

    Column: {{ $neatness->column }}
    
**direction:** The Direction sorting now. `asc` or `desc`

    Column: {{ $neatness->direction }}
    
**urls:** URLs to switch sort. 
    
    @foreach($neatness->urls as $key => $url)
        {{ $key }} => {{ $url }}
    @endforeach

    or 
    
    $neatness->urls->get('title');

**labels:** Labels you set in your Model.

    @foreach($neatness->labels as $key => $label)
        {{ $key }} => {{ $label }}
    @endforeach
    
    or 
    
    $neatness->labels->get('title');

**symbols:** Symbols plucked with sort state.

    @foreach($neatness->symbols as $key => $symbol)
        {{ $key }} => {{ $symbol }}
    @endforeach
    
    or 
    
    $neatness->symbols->get('title');

**texts:** Texts mainly for link.

    @foreach($neatness->urls as $key => $url)
        <a href="{{ $url }}">{{ $neatness->texts->get($key) }}</a>
    @endforeach
    
    or 
    
    $neatness->texts->get('title');

**appends:** Array values for pagination
  
    {{ $items->appends($neatness->appends)->links() }}

# Change default column and direction
By this way, you can change default column and direction.

    Item::neatness('title', 'desc')->get();

# Relationship

You can use this package with relationship using join().

(in Controller)

    $items = Item::join('item_details', 'item_details.item_id', '=', 'items.id')
                ->neatness()
                ->paginate(5);

(in Model)

	protected $neatness = [
		'default' => ['items.id', 'desc'],
		'columns' => [
			'id' => 'items.id',
			'title' => 'items.title',
			'date' => 'items.created_at',
			'address' => 'item_details.address'
		]
	];

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh