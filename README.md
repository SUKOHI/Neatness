# Neatness
A Laravel package to automatically add sorting system for DB query and provide URLs to switch between ASC and DESC.  
(This is for Laravel 4.2. [For Laravel 5](https://github.com/SUKOHI/Neatness))

[Demo](http://demo-laravel52.capilano-fw.com/neatness)

# Installation

Execute composer command.

    composer require sukohi/neatness:1.*

# Preparation

At first, set `NeatnessTrait` in your Model.

    use Sukohi\Neatness\NeatnessTrait;
    
    class Item extends Eloquent {
    
        use NeatnessTrait;

    }

Secondary, add configuration values also in your Model.

**default:** Default column and direction. (Required)  
**columns:** Columns and Labels you want to sort. (Required)  
**symbols:** Symbols you will be able to use in your View. (Optional)  

    protected $neatness = [
        'default' => ['id', 'desc'],
        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
            'created_at' => 'Date'
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
        'id|title' => 'LABEL'
    ],

**Query Scope:** You also can utilize `Query Scopes` instead of column name.  

    'columns' => [
        'scope::sortTitle' => 'LABEL'
    ],

in this case, you need to prepare a scope method in your model. ([About Query Scopes](https://laravel.com/docs/4.2/eloquent#query-scopes))
    
    public function scopeSortTitle($query, $direction) {

        return $query->orderBy('title', $direction);

    }

# Usage

Now you can use a method called `neatness`.

(in Controller)

    $items = Item::neatness()->get();

After call `neatness()`, you can access to sort data through `$neatness`.
    
(in View)

**column:** The column name sorting now.

    Column: {{ $neatness->column }}
    
**direction:** The Direction sorting now. `asc` or `desc`

    Column: {{ $neatness->direction }}
    
**urls:** URLs to switch sort. 
    
    @foreach($neatness->urls as $column => $url)
        {{ $column }} => {{ $url }}
    @endforeach

**labels:** Labels you set in your Model.

    @foreach($neatness->labels as $column => $label)
        {{ $column }} => {{ $label }}
    @endforeach

**symbols:** Symbols plucked with sort state.

    @foreach($neatness->symbols as $column => $symbol)
        {{ $column }} => {{ $symbol }}
    @endforeach

**texts:** Texts mainly for link.

    @foreach($neatness->urls as $column => $url)
        <a href="{{ $url }}">{{ $neatness->texts->$column }}</a>
    @endforeach

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
			'items.id' => 'ID',
			'items.title' => 'Title',
			'items.created_at' => 'Date',
			'item_details.address' => 'Address'
		],
		'symbols' => [
			'asc' => '<i class="fa fa-sort-asc"></i>',
			'desc' => '<i class="fa fa-sort-desc"></i>',
			'default' => '<i class="fa fa-sort"></i>'
		]
	];

# Before filter (optional)

You can set closure as the 3rd argument.  
And it will be called before this package set ORDER BY clause.

    $items = Item::neatness('id', 'asc', function($neatness){
                                         
                    // Get column & direction
    
                    $current_column = $neatness->getSortColumn();
                    $current_direction = $neatness->getSortDirection();
    
                    // Set column & direction
    
                    $neatness->setSortColumn('title');
                    $neatness->setSortDirection('desc');
    
    
                    // Get DB query
    
                    $query = $neatness->getQuery();
    
                })
                ->paginate(5);

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh