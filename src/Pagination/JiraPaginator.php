<?php

namespace AtlassianConnectCore\Pagination;

/**
 * Class JiraPaginator
 *
 * @package AtlassianConnectCore\Pagination
 */
class JiraPaginator extends Paginator
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
    protected $perPageKey = 'maxResults';

    /**
     * The name of key representing offset
     *
     * @var string
     */
    protected $offsetKey = 'startAt';

    /**
     * The key of total items value
     *
     * @var string
     */
    protected $totalKey = 'total';

    /**
     * The key of containing items
     *
     * @var string
     */
    protected $itemsKey = 'values';
}
