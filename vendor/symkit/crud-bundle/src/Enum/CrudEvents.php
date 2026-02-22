<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Enum;

enum CrudEvents: string
{
    /**
     * Dispatched before a new entity is persisted.
     */
    case PRE_PERSIST = 'crud.pre_persist';

    /**
     * Dispatched after a new entity is persisted.
     */
    case POST_PERSIST = 'crud.post_persist';

    /**
     * Dispatched before an existing entity is updated.
     */
    case PRE_UPDATE = 'crud.pre_update';

    /**
     * Dispatched after an existing entity is updated.
     */
    case POST_UPDATE = 'crud.post_update';

    /**
     * Dispatched before an entity is deleted.
     */
    case PRE_DELETE = 'crud.pre_delete';

    /**
     * Dispatched after an entity is deleted.
     */
    case POST_DELETE = 'crud.post_delete';

    /**
     * Dispatched when the list query is being built.
     */
    case LIST_QUERY = 'crud.list_query';
}
