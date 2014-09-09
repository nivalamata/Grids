<?php
namespace Nayjest\Grids;

use Illuminate\Database\Eloquent\Builder;
use Event;

class EloquentDataProvider extends DataProvider
{


    protected $collection;

    protected $paginator;

    /** @var  $iterator \ArrayIterator */
    protected $iterator;

    public function __construct(Builder $src)
    {
        $this->src = $src;
    }

    public function reset()
    {
        $this->getIterator()->rewind();
        return $this;
    }

    /**
     * @return \Illuminate\Pagination\Paginator
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->getPaginator()->getCollection();
        }
        return $this->collection;
    }

    public function getPaginator()
    {
        if (!$this->paginator) {
            $this->paginator = $this->src->paginate($this->page_size);
        }
        return $this->paginator;
    }

    /**
     * @return \Illuminate\Pagination\Factory
     */
    public function getPaginationFactory()
    {
        return $this->src->getQuery()->getConnection()->getPaginator();
    }

    protected function getIterator()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getCollection()->getIterator();
        }
        return $this->iterator;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->src;
    }

    public function getRow()
    {
        if ($this->index < $this->count()) {
            $this->index++;
            $item = $this->iterator->current();
            $this->iterator->next();
            $row = new EloquentDataRow($item, $this->getRowId());
            Event::fire(self::EVENT_FETCH_ROW, [$row, $this]);
            return $row;
        } else {
            return null;
        }
    }

    public function count()
    {
        return $this->getCollection()->count();
    }

    public function orderBy($field_name, $direction)
    {
        $this->src->orderBy($field_name, $direction);
    }

    public function filter($field_name, $operator, $value)
    {
        $this->src->where($field_name, $operator, $value);
    }

} 