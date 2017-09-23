<?php

namespace AtlassianConnectCore\Pagination;

/**
 * Class ConfluencePaginator
 *
 * @package AtlassianConnectCore\Pagination
 */
class ConfluencePaginator extends Paginator
{
    /**
     * Type of pagination
     *
     * @var int
     */
    protected $type = self::TYPE_OFFSET;

    /**
     * The name of key representing limitation of results
     *
     * @var string
     */
    protected $perPageKey = 'limit';

    /**
     * The name of key representing offset
     *
     * @var string
     */
    protected $offsetKey = 'start';

    /**
     * The key of total items value
     *
     * @var string
     */
    protected $totalKey = 'size';

    /**
     * The key of containing items
     *
     * @var string
     */
    protected $itemsKey = 'results';
}